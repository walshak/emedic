<?php session_start();
include("../../Connections/Conn.php");
header('Content-Type: application/json');
include('../objects.php');
include('../helpers.php');






$request = json_decode(json_encode(
    [
        "appointment_number" => $_POST['appointment_number'],
        "hospital_no" => $_POST['hospital_no'],
        "drug" => $_POST['drug'],
        'action' => $_POST['action']
    ]
));


if (isset($request->drug) && isset($request->appointment_number)) {

    if ($request->action == 'add') {
        // $appointment_info = $Appointment->get(["appt_no" => $request->appointment_number, "hospital_no" => $request->hospital_no]);
        $save = false;

        $patient_info = $Patient->getByHospitalNo($request->hospital_no);
        $ap_type = 0;
        if (!empty($patient_info)) {
            $insurance_name = $patient_info->insurance_name;
            $interest = $patient_info->interest;
            $insurance_type = $patient_info->insurance_type;
            $services_access = $patient_info->services_access;
            $insurance_no = $patient_info->insurance_no;
            $payment_mode = $patient_info->payment_mode;
			$add_minus = $patient_info->add_minus;

            $payst = null;
            $insurance = $insurance_type;
            $access = $services_access;
            $insurance = $insurance_type;
            


        }else{
            echo json_encode(["status" => 404, "message" => "Invalid Patient No."]);
            exit;
        }

        $drug = $request->drug;
        //// Medication 

        $serv_group = "Pharmacy";
        $cat_type = "Pharmacy";
        $dept_id = 7;   //// use the id of the drug to get the department
        $drug_sn = $drug->sn;
        $item_services = $drug->product_name;
        $hosp_price = $drug->hosp_price;
        $ext_price = $drug->cash_price;

        $dept_stmt = $db->prepare("SELECT sn FROM  department WHERE department = 'Pharmacy' LIMIT 1");
        $dept_stmt->execute();
        if($dept_stmt->rowCount() > 0){
            $department_info =  $dept_stmt->fetch();
            $dept_id = $department_info['sn'];
        }

        $med_dosage_ = intval($drug->med_dosage) < 1 ? 1 : intval($drug->med_dosage);
        $med_duration_ = intval($drug->med_duration) < 1 ? 1 : intval($drug->med_duration);
        if (strlen('' . $med_dosage_) < strlen($drug->med_dosage) - 1) { /// for loose format, it will send string and this string will be congated and left with numbers
            $drug->med_dosage = $drug->med_dosage;
            $drug->med_duration = $drug->med_duration;
        } else {
            $drug->med_dosage = $med_dosage_;
            $drug->med_duration = $med_duration_;
        }




        /// Check if the Medication has been saved bfore
        // $checkMedicationStmt = $db->prepare("SELECT * FROM  patient_ap_services WHERE app_no = ? AND hospital_no = ? AND drug_sn = ? and serv_group='Pharmacy'");
        // $checkMedicationStmt->execute(
        //     array(
        //         $request->appointment_number,
        //         $request->hospital_no,
        //         $drug->sn
        //     )
        // );

        // $checkMedicationRows = $checkMedicationStmt->rowCount();
        // if ($checkMedicationRows == 0) {
            ///  save if not saved before

			$amount_invoice = service_amount_cal($request->appointment_number, $interest, $insurance_type, $services_access, $hosp_price, $ext_price, $add_minus);
			$claim_amt= $amount_invoice["claim_amt"];
			 
if($insurance_type!='Private(Self)'){
	$stmt=$db->query("SELECT price FROM hmo_stocks_tariff WHERE stock_sn='$drug_sn' and price>0 and hmo='$insurance_no'");		
		  if($stmt->rowCount()>0) {
			  $row_dx = $stmt->fetch(PDO::FETCH_ASSOC);
			 	$claim_amt=$row_dx['price'];
			  	$ccop_int_charge=0;
		  }
		}
		
    
            
            ///// amount and invoice
            ///$amount_invoice = service_amount_cal($request->appointment_number, $interest, $insurance_type, $services_access, $item_amt);

            $save = save_patient_ap_service(
                $db,
                $request->appointment_number,
                $request->hospital_no,
                $services_access,
                $serv_group,
                $cat_type,
                $dept_id,
                $drug_sn,
                $item_services,
                $amount_invoice["item_amt"],
                $amount_invoice["claim_amt"],
				 $amount_invoice["ccop_int_charge"],
                "",
                $amount_invoice["invoice_no"],
                $_SESSION['fullname'],
                $amount_invoice["amount_paying"],
                $amount_invoice["pay_mode"],
                $drug->med_frequency,
                $drug->med_dosage,
                $drug->med_dosage_unit,
                $drug->med_duration,
                $drug->med_duration_unit,
                $drug->remarks,
                true
            );

            lockApppointment($db, $request->appointment_number);
        // }/


        echo json_encode(["status" => 200, "message" => $save]);
        exit;
    }


		
	
	
	

    if ($request->action == 'remove') {
        $drug = $request->drug;
        $mesage = 'Cannot remove, Already Paid';
        $status = 401;

        $PatientApService_ = $PatientApService->get(
            [ 
                'sn' => $drug->ap_services_id,
                'drug_sn' => $drug->sn,
                'serv_group' => 'Pharmacy',
                'app_no' => $request->appointment_number,
                'hospital_no' => $request->hospital_no
            ]
        );

      

        if($PatientApService_->created_by == $_SESSION["id"]){
            if (!empty($PatientApService_)) {
                /// not paid for the service
                if($PatientApService_->invoice_status == 0){
                    $delete = $PatientApService->delete(
                        [
        
                            'app_no' => $request->appointment_number,
                            'sn' => $drug->ap_services_id,
                            'drug_sn' => $drug->sn,
                            'serv_group' => 'Pharmacy',
                            'hospital_no' => $request->hospital_no,
                            'invoice_status' => 0
                        ]
                    );
        
                    $mesage = $delete ? "Removed" : "Action Failed";
                    $status = $delete ? 200 : 401;
                }else{
                    $mesage =  "Soryy, Drug has already been invoiced";
                }
               
            }
        }else{
            $mesage =  "Soryy, You cannot remove this drug";
        }
       
        echo json_encode(["status" => $status, "message" => $mesage]);
        exit;
    }
	
    if ($request->action == 'editPrescription') {
         $sn = intval($_POST['sn']);
     
        $remarks = $_POST['remarks'];
        $mesage = 'Cannot Change Prescription';

        $PatientApService_ = $PatientApService->find($sn);
        
            if (!empty($PatientApService_)) {
            if($PatientApService_->created_by == $_SESSION["id"]){
                    /// not paid for the service
                    $stmt = $db->prepare("UPDATE patient_ap_services SET remarks = ? WHERE sn = ? ");
                    $stmt->execute([$remarks, $sn]);
                
                    $mesage =  "Saved";
            }else{
                $mesage =  "Sorry, You cannot Edit this Prescription";
            }
        }else{
            $mesage =  "Sorry, You cannot Edit this Prescription";
        }
       
        echo json_encode(["status" => 200, "message" => $mesage]);
        exit;
    }
}





