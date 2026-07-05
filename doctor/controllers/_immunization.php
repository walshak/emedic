<?php session_start();
include("../../Connections/Conn.php");
header('Content-Type: application/json');
include('../objects.php');
include('../helpers.php');







    $action = $_POST['action'];

    if($action == 'enrol'){
        $request = json_decode(json_encode(
            [
                "hospital_no" => $_POST['hospital_no'],
                "appointment_number" => $_POST['appointment_number'],
                "mother_name" => $_POST['mother_name'],
                "father_name" => $_POST['father_name'],
                "gestation_at_birth" => $_POST['gestation_at_birth'],
                "neonatal_complication" => $_POST['neonatal_complication'],
                "mode_of_delivery" => $_POST['mode_of_delivery']
            ]
        ));



        if(empty($Immunization->get(['hospital_no' => $request->hospital_no]))){
            $enrol = $Immunization->enrol($request);
            if($enrol){
                echo json_encode(["status" => 200, "message" => "Enrolled Successfully..."]);
              
            }else{
                echo json_encode(["status" => 505, "message" => "Oops! Something went wrong..."]);
            }
        }else{
            echo json_encode(["status" => 401, "message" => "Oops! Enrolled before..."]);
        }
 

   
    exit;

    }

    if($action == 'addVaccine'){
        
        $data =  [
                "hospital_no" => $_POST['hospital_no'],
                "appointment_number" => $_POST['appointment_number'],
                "antigen" => $_POST['antigen'],
                "date_given" => $_POST['date_given']
            ];

            if($Immunization->is_vaccine_given($data) == false ){
                $addVaccine = $Immunization->addVaccine($data);
                if($addVaccine){
                    echo json_encode(["status" => 200, "message" => "Vaccine Given saved Successfully..."]);
                  
                }else{
                    echo json_encode(["status" => 505, "message" => "Oops! Something went wrong..."]);
                }
            }else{
                echo json_encode(["status" => 401, "message" => "Oops! Enrolled before..."]);
            }
        
    }

?>