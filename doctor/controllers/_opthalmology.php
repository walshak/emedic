<?php session_start();
include("../../Connections/Conn.php");
include('../objects.php');
include('../helpers.php');
header('Content-Type: application/json');


if (isset($_POST['action'])) {
    if ($_POST['action'] == 'saveOpthResult') {
        $hospital_no = $_POST['hospital_no'];
        $appointment_number = $_POST['appointment_number'];
        $left = $_POST['left'];
        $right = $_POST['right'];
        $ocular_result = $_POST['ocular_result'];
         $sn = $_POST['sn'];
         $view_mode = $_POST['view_mode'];
         
      
        

         $opthalmology_ = $Opthalmology->find_list($sn);
       
        $editorr = $opthalmology_->editorr;
        $ocular_list = $opthalmology_->ocular_list;

        $data = [
            'hospital_no' => $hospital_no,
            'app_no' => $appointment_number,
            'left' => $left,
            'right' => $right,
            'editorr' => $editorr,
            'ocular_result' => $ocular_result,
            'ocular_list' => $ocular_list,
            'entered_by' => $_SESSION['fullname'],
            'sn' => $sn,
            'view_mode' => $view_mode
        ];

        $is_saved = $Opthalmology->get(['hospital_no' => $hospital_no, 'ocular_sn' => $sn]);
        if(empty($is_saved)){
            $save = $Opthalmology->saveResult($data);
        }else{
            $save = $Opthalmology->updateResult($data);
        }

        $status = $save ? 200 : 401;
        $message = $save ? "Saved Successfully..." : "Oops! Something went wrong...";
        echo json_encode(["status" => $status, "message" => $message, 'hospital_no' => $hospital_no]);
        exit; 
       
    }

    if ($_POST['action'] == 'loadResultForm') {

         $hospital_no = $_POST['hospital_no'];
     
        $appointment_number = $_POST['appointment_number'];
        $sn = $_POST['sn'];

        $opthalmology_ = $Opthalmology->find_list($sn);
        $is_saved = $Opthalmology->get(['hospital_no' => $hospital_no, 'ocular_sn' => $sn]);
            $ocular_result = null;
            $LeftLeft = null;
            $RightRight = null;
            if(!empty($is_saved)){
                $ocular_result = $is_saved->result;
                $LeftLeft = $is_saved->left;
                $RightRight = $is_saved->right;;
            }
        

            if($opthalmology_->editorr == 0){
                $html = '
                <div class="form_sep">
                <label for="reg_select" class="req">Select Both Comment Here:</label>
                <select name="ocular_result" id="ocular_result" class="form-control" data-required="true" required>
       
                    <option selected="selected" value="">Select...</option>
       
                    <option value="Normal" '.($ocular_result == 'Normal' ? 'selected' : '').'>Both Normal</option>
                    <option value="Clear" '.($ocular_result == 'Clear' ? 'selected' : '').'>Both Clear</option>
                    <option value="White" '.($ocular_result == 'White' ? 'selected' : '').'>Both White</option>
                    <option value="Rediness" '.($ocular_result == 'Rediness' ? 'selected' : '').'>Both Rediness</option>
                    <option value="Discharge" '.($ocular_result == 'Discharge' ? 'selected' : '').'>Both Discharge</option>
                    <option value="Inflamed" '.($ocular_result == 'Inflamed' ? 'selected' : '').'>Both Inflamed</option>
                    <option value="Moderate" '.($ocular_result == 'Moderate' ? 'selected' : '').'>Both Moderate</option>
                    <option value="Tearing" '.($ocular_result == 'Tearing' ? 'selected' : '').'>Both Tearing</option>
       
                </select>
            </div>
            <div class="form_sep">
                <label for="reg_textarea_message" class="req">Enter your Comment below:</label>
                <table>
                    <tbody>
                        <tr>
                        </tr>
                        <tr>
                            <td>
                                Left Eye: <br>
                                <textarea name="LeftLeft" id="LeftLeft" cols="30" rows="4" class="form-control" data-required="true" data-minlength="10" required>'.$LeftLeft.'</textarea>
                            </td>
                        </tr>
                        <tr>
                        </tr>
                        <tr>
                            <td>
                                <br>
                                Right Eye: <br>
                                <textarea name="RightRight" id="RightRight" cols="30" rows="4" class="form-control" data-required="true" data-minlength="10" required>'.$RightRight.'</textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
       
            </div>
            <br>
            <div>
                <button class="btn btn-sm  btn-primary" style="width:100%;"> Save</button>
            </div>
                ';
            }else{
                $html = '
            <div class="form_sep">
                <label for="reg_textarea_message" class="req">Enter your Comment below:</label>
                <table>
                    <tbody>
                        <tr>
                            <td>
                                <br>
                                <textarea name="ocular_result" id="ocular_result" cols="30" rows="4" class="form-control" data-required="true" data-minlength="10" required>'.$ocular_result.'</textarea>
                            </td>
                        </tr>
                        <tr>
                        <td>
                            <br>
                           <input type="hidden" LeftLeft="" name="LeftLeft" id="LeftLeft">
                           <input type="hidden" value="" name="RightRight" id="RightRight">
                        </td>
                    </tr>
                    </tbody>
                </table>
       
            </div>
            <br>
            <div>
                <button class="btn btn-sm  btn-primary" style="width:100%;"> Save</button>
            </div>
                ';
            }
       

         echo json_encode(["status" => 200, "body" => $html]);
         exit;
    }

}else{
    echo json_encode(["status" => 200, "body" => "No request received"]);
         exit;
}
