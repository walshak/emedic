     
<?php
if(isset($_POST['vistal_hx'])){
        include("../Connections/Conn.php");
        $selected_val = $_POST['vistal_hx'];
        $hospital_no = $_POST['hospital_no'];
        $columns = array(
            'bp' => 'bp',
            'temp' => 'temp',
            'weight' => 'weight',
            'resp_rate' => 'resp_rate',
            'height' => 'height',
            'pulse_read' => 'pulse_read',
            'muac_read' => 'muac_read',
            'spo2' => 'spo2',
            'fbs' => 'fbs',
            'rbs' => 'rbs',
            'ppbs' => 'ppbs',
            'GTT' => 'gtt'
        );
        
        $response = [];
        // Check if selected value is a valid key in the columns array
        if(isset($columns[$selected_val])) {
            $column = $columns[$selected_val];
            $title = strtoupper($column);
            try {
                $stmt = $db->prepare("SELECT date_ap, $column FROM vital_sign WHERE hospital_no = :hospital_no
                    AND $column IS NOT NULL AND status = '1' ORDER BY sn DESC LIMIT 150");
                $stmt->bindParam(':hospital_no', $hospital_no);
                $stmt->execute();
                $data = array();
                $labels = array();
                $BP_systolics = [];
                $BP_diastolics = [];
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $labels[] =  date('d M Y h:i A', strtotime(''.$row["date_ap"]));

                    if($column == 'bp'){
                        $bp_values = explode('/', $row[$column]);
                        $BP_systolics[] = floatval($bp_values[0]);
                        $BP_diastolics[] = floatval($bp_values[1]);
                        continue;
                    }
                    $data[] = floatval($row[$column]);
                    
                }

            

             if($column == 'bp'){
                $datasets = [[
                    "label" => 'BP systolic',
                    "data" => $BP_systolics,
                    "borderColor" => 'rgb(75, 192, 192)',
                   
                ],
                [
                    "label" => 'BP diastolic',
                    "data" => $BP_diastolics,
                    "borderColor" => 'rgb(75, 192, 192)'
                ]
            ];
            }else{
                $datasets = [[
                    "label" => $title,
                    "data" => $data,
                    "borderColor" => 'rgb(75, 192, 192)',
                    "tension" => 0.1
                ]];
            }

                echo json_encode([
                    'labels' => $labels,
                    'datasets' => $datasets
                ]);
            } catch(PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        } else {
            echo "Invalid value selected";
        }
    }
    
 

?>