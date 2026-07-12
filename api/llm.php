<?php
/**
 * LLM API Endpoint for eMedic
 * 
 * Handles: patient_summary, polish_note
 * All responses are JSON.
 */
session_start();
header('Content-Type: application/json');

require_once(__DIR__ . '/../Connections/Conn.php');
require_once(__DIR__ . '/../inc/LlmGatewayService.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$gateway = new LlmGatewayService($db);

if (!$gateway->isEnabled()) {
    die(json_encode(['success' => false, 'message' => 'AI features are not enabled. Please configure in Admin Control Panel.']));
}

try {
    switch ($action) {

        // ─── Patient Summary ─────────────────────────────────────────
        case 'patient_summary':
            $hospital_no = isset($_POST['hospital_no']) ? trim($_POST['hospital_no']) : '';
            if (empty($hospital_no)) {
                die(json_encode(['success' => false, 'message' => 'Missing hospital_no']));
            }

            $context = buildPatientContext($db, $hospital_no);
            $config = $gateway->getConfig();
            $systemPrompt = isset($config['system_prompts']['patient_summary']) && !empty($config['system_prompts']['patient_summary'])
                ? $config['system_prompts']['patient_summary']
                : getDefaultSummaryPrompt();

            $response = $gateway->complete($systemPrompt, $context, [
                'max_tokens' => 4096,
                'temperature' => 0.2,
            ]);

            echo json_encode([
                'success' => true,
                'summary_text' => $response['content'],
                'model_used' => $response['model'],
                'provider' => $response['provider'],
            ]);
            break;

        // ─── Polish Note ─────────────────────────────────────────────
        case 'polish_note':
            $noteContent = isset($_POST['note_content']) ? trim($_POST['note_content']) : '';
            $hospital_no = isset($_POST['hospital_no']) ? trim($_POST['hospital_no']) : '';

            if (empty($noteContent)) {
                die(json_encode(['success' => false, 'message' => 'No note content provided']));
            }

            $config = $gateway->getConfig();
            $systemPrompt = isset($config['system_prompts']['polish_note']) && !empty($config['system_prompts']['polish_note'])
                ? $config['system_prompts']['polish_note']
                : getDefaultPolishPrompt();

            $userMessage = "NOTE TO POLISH:\n" . $noteContent;

            // If we have a patient, add context
            if (!empty($hospital_no)) {
                $context = buildPatientContext($db, $hospital_no);
                $userMessage = "PATIENT CONTEXT (Do not include this in your output, just use it for understanding abbreviations/history):\n" .
                    $context . "\n\n---\n" . $userMessage;
            }

            $response = $gateway->complete($systemPrompt, $userMessage, [
                'max_tokens' => 2000,
                'temperature' => 0.3,
            ]);

            echo json_encode([
                'success' => true,
                'polished_content' => $response['content'],
                'model_used' => $response['model'],
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action: ' . $action]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'LLM Error: ' . $e->getMessage()]);
}


// ─── Helper Functions ────────────────────────────────────────────────

function buildPatientContext($db, $hospital_no)
{
    $sections = [];

    // 1. Demographics
    $stmt = $db->prepare("SELECT surname, fname, oname, gender, dob, phone, blood_g, geno_type, marital_status FROM enrollee WHERE hospital_no = ?");
    $stmt->execute([$hospital_no]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($patient) {
        $age = '';
        if (!empty($patient['dob'])) {
            $dob = new DateTime($patient['dob']);
            $age = $dob->diff(new DateTime())->y . ' years old';
        }
        $sections[] = "## PATIENT DEMOGRAPHICS\n" .
            "Name: {$patient['fname']} {$patient['oname']} {$patient['surname']}\n" .
            "Sex: {$patient['gender']}\n" .
            "Age: {$age}\n" .
            "Blood Group: {$patient['blood_g']}\n" .
            "Genotype: {$patient['geno_type']}\n" .
            "Marital Status: {$patient['marital_status']}";
    }

    // 2. Allergies
    $stmt = $db->prepare("SELECT complain FROM c_d_remarks WHERE hospital_no = ? AND cat_type='DH' ORDER BY sn DESC LIMIT 1");
    $stmt->execute([$hospital_no]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && !empty($row['complain'])) {
        $sections[] = "## KNOWN ALLERGIES\n" . $row['complain'];
    }

    // 3. Latest Vitals
    $stmt = $db->prepare("SELECT * FROM vital_sign WHERE hospital_no = ? ORDER BY sn DESC LIMIT 1");
    $stmt->execute([$hospital_no]);
    $vitals = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($vitals) {
        $vitalText = "## LATEST VITALS\n";
        $vitalFields = ['temp' => 'Temperature', 'bp' => 'BP', 'pulse_read' => 'Pulse', 'resp_rate' => 'Resp Rate', 'weight' => 'Weight', 'height' => 'Height', 'spo2' => 'SpO2'];
        foreach ($vitalFields as $field => $label) {
            if (isset($vitals[$field]) && !empty($vitals[$field])) {
                $vitalText .= "{$label}: {$vitals[$field]}\n";
            }
        }
        $sections[] = $vitalText;
    }

    // 4. Recent Clinical Notes (last 10)
    $stmt = $db->prepare("SELECT notes, notes_type, prepared_by, date_entry FROM notes WHERE hospital_no = ? ORDER BY sn DESC LIMIT 10");
    $stmt->execute([$hospital_no]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($notes)) {
        $noteText = "## RECENT CLINICAL NOTES\n";
        foreach ($notes as $note) {
            $date = isset($note['date_entry']) ? date('d M Y', strtotime($note['date_entry'])) : 'N/A';
            $type = isset($note['notes_type']) ? $note['notes_type'] : '';
            $cleanNote = strip_tags($note['notes']);
            $noteText .= "- [{$type}] ({$date}) by {$note['prepared_by']}: " . mb_substr($cleanNote, 0, 500) . "\n";
        }
        $sections[] = $noteText;
    }

    // 5. Recent Diagnoses
    $stmt = $db->prepare("SELECT diagnosis, created_at as date_entry FROM diagnosis_tracking WHERE hospital_no = ? ORDER BY id DESC LIMIT 10");
    $stmt->execute([$hospital_no]);
    $diagnoses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($diagnoses)) {
        $dxText = "## DIAGNOSES\n";
        foreach ($diagnoses as $dx) {
            $date = isset($dx['date_entry']) ? date('d M Y', strtotime($dx['date_entry'])) : '';
            $dxText .= "- {$dx['diagnosis']} ({$date})\n";
        }
        $sections[] = $dxText;
    }

    // 6. Active Medications (recent prescriptions)
    $stmt = $db->prepare("SELECT item_services as drug_name, med_dosage as dosage, med_frequency as frequency, med_duration as duration FROM patient_ap_services WHERE hospital_no = ? AND serv_group IN ('Drugs', 'Pharmacy', 'Dispensary') ORDER BY sn DESC LIMIT 15");
    $stmt->execute([$hospital_no]);
    $meds = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($meds)) {
        $medText = "## RECENT MEDICATIONS\n";
        foreach ($meds as $med) {
            $medText .= "- {$med['drug_name']} {$med['dosage']} {$med['frequency']} x {$med['duration']}\n";
        }
        $sections[] = $medText;
    }

    // 7. Recent Lab Results
    $stmt = $db->prepare("SELECT r.test_name, r.field_name, r.field_value, r.field_ref, r.result_date FROM lab_result r JOIN lab_manage m ON r.lab_no = m.labrequest_no WHERE m.patient = ? ORDER BY r.sn DESC LIMIT 10");
    $stmt->execute([$hospital_no]);
    $labs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($labs)) {
        $labText = "## RECENT LAB RESULTS\n";
        foreach ($labs as $lab) {
            $date = isset($lab['result_date']) ? date('d M Y', strtotime($lab['result_date'])) : '';
            $subfield = !empty($lab['field_name']) ? " (" . $lab['field_name'] . ")" : "";
            $labText .= "- {$lab['test_name']}{$subfield}: {$lab['field_value']} {$lab['field_ref']} ({$date})\n";
        }
        $sections[] = $labText;
    }

    if (empty($sections)) {
        return "No clinical data found for patient {$hospital_no}.";
    }

    return implode("\n\n", $sections);
}

function getDefaultSummaryPrompt()
{
    return "You are a clinical summarizer for a hospital Electronic Medical Record system. Given the patient's clinical data, generate a concise, professional clinical summary suitable for a doctor's quick review. Include:
1. Patient overview (demographics, key identifiers)
2. Active problems and diagnoses
3. Recent clinical findings and vitals
4. Current medications
5. Recent lab results (if notable)
6. Key clinical considerations

Use clear, professional medical language. Use markdown headings and bullet points for readability. Keep it concise but comprehensive. Do NOT fabricate any data - only summarize what is provided.";
}

function getDefaultPolishPrompt()
{
    return "You are a medical note editor. Your task is to polish and clean up clinical notes written by healthcare professionals. Rules:
1. Fix spelling, grammar, and punctuation errors
2. Expand common medical abbreviations where appropriate (e.g., 'htn' → 'hypertension', 'sob' → 'shortness of breath')
3. Improve sentence structure while preserving the clinical meaning exactly
4. Format into clear paragraphs with SOAP-style sections if applicable
5. Do NOT add any clinical information that was not in the original note
6. Do NOT change medical facts, dosages, or clinical observations
7. Return ONLY the polished note text, no commentary or explanations";
}
