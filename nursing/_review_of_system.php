<?php

if(isset($_POST['loadROSPage']) || 
    isset($_POST['loadROSCat']) ||
    isset($_POST['deleteROS']) ||
    isset($_POST['saveROS']) 
    ){
    session_start();
    include("../Connections/Conn.php");
    include('objects.php');
    include('helpers.php');

}

function addros($db, $data){
    $stmt = $db->prepare("INSERT INTO c_d_remarks(app_no, hospital_no,complain,cat_type,prepared_by,date_entry) VALUES (?, ?, ?, ?, ?, now())");
    return $stmt->execute([
        $data['app_no'],
        $data['hospital_no'],
        $data['complain'],
        'R',
        $_SESSION['fullname']
    ]);
        
}  


function currentROS($db, $hospital_no, $app_no){
    $tr = "";
    $stmt = $db->prepare("SELECT * FROM c_d_remarks WHERE app_no=? AND hospital_no=? AND cat_type='R' ORDER BY sn DESC");
    $stmt->execute([$app_no, $hospital_no]);	
    $ros_list = $stmt->fetchAll();
        foreach ($ros_list as $key => $ros) {
            $id = $ros['sn'];
            $tr .= '
                <tr>
                    <td>'.date('d M,Y h:i a', strtotime($ros['date_entry'])).'</td>
                    <td>'.$ros["complain"].'</td>
                    <td>'.$ros["prepared_by"].'</td>
                    <td>'.($ros["prepared_by"] ==  $_SESSION["fullname"] ? "<a href='#' <a href='#' class='text-danger'  onclick=deleteROS(".$id.")>[Remove]</a>":"" ).'</td>
                </tr>
            ';
        }


        return $tr;   
}

function pastROS($db, $hospital_no, $app_no){
    $tr = "";
    $stmt = $db->prepare("SELECT * FROM c_d_remarks WHERE app_no !=? AND hospital_no=? AND cat_type='R' ORDER BY sn DESC LIMIT 10");
    $stmt->execute([$app_no, $hospital_no]);	
    $ros_list = $stmt->fetchAll();
        foreach ($ros_list as $key => $ros) {
            $id = $ros['sn'];
            $tr .= '
                <tr>
                    <td>'.date('d M,Y h:i a', strtotime($ros['date_entry'])).'</td>
                    <td>'.$ros["complain"].'</td>
                    <td>'.$ros["prepared_by"].'</td>
                </tr>
            ';
        }


        return $tr;   
}


if(isset($_POST['deleteROS'])){
    $sn = intval(cleanInput($_POST['id']));

    header('Content-Type: application/json');
    $stmt = $db->prepare("SELECT * FROM c_d_remarks WHERE sn=? AND cat_type='R'");
    $stmt->execute([$sn]);
        if($stmt->rowCount() > 0){
            $row = $stmt->fetch();

            $stmt = $db->prepare("DELETE FROM c_d_remarks WHERE sn=? AND cat_type='R'");
            $stmt->execute([$sn]);

            $current_tr = currentROS($db, $row["hospital_no"], $row["app_no"]);
            echo json_encode(["message" => "Removed", "current_tr" => $current_tr, "status" => 200]);
          
        }	
        exit; 
}

    if(isset($_POST['loadROSCat'])){

         $cat = cleanInput($_POST['cat']);

         if($cat != 'other'){
            $stmt = $db->prepare("SELECT * FROM records WHERE cat = ? ");
            $stmt->execute([$cat]);	
            if($stmt->rowCount()>0){
                $cats = $stmt->fetchAll();
                echo '</br> <strong style="font-size:16px"> Select <u>'. $cat . '</u> Options:</strong>  ';
                foreach ($cats as $key => $row) {
                 echo '<input class="ros_opt" type="checkbox" value="'. $row['item'] . '" />' . ' ' .$row['item']; }	?>
                <a rel="facebox" href="#" class="cat_summary_btn" data-name="<?= $cat;?>"><strong style="font-size:14px">[ Category Summary - Specify]</strong>
            
                </a> &nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; 
                <button class="btn btn-success" type="submit" class="addROSbtn" onclick="addROSbtn()" >+</button>
                <?php echo '</br>';
                }
         }else{
            echo '
            <label for="reg_textarea_message" class="req">Type Others ROS Below</label>
            <textarea name="other_complain" id="other_complain" cols="30" rows="3" class="form-control" data-required="true" data-minlength="10" spellcheck="false"></textarea>
            <br> <button class="btn btn-success" type="submit" name="add" id="add" onclick="addROSSummarybtn()">Save + </button>
            ';
         }       
        exit;
    }


    if(isset($_POST['saveROS'])){
        header('Content-Type: application/json');
        $hospital_no = cleanInput($_POST['hospital_no']);
        $app_no = cleanInput($_POST['app_no']);
        $selected_options = $_POST['selected_options'];
        $cat = $_POST['cat'];
            
            $compl = '  <strong>'.$cat.'</strong>' .':  ';
            foreach ($selected_options as $key => $ros) {
                if(!empty($ros)){
                    $compl .=' ' .$ros.',  ';
                }
            }    
            
            $save = addros($db, [
                'hospital_no' => $hospital_no,
                'app_no' => $app_no,
                'complain' => $compl
            ]);

            if($save){               
                $tr = currentROS($db, $hospital_no, $app_no);
                echo json_encode(["message" => "Saved", "tr" => $tr, "status" => 200]);
                exit;
            }
            echo json_encode(["message" => "Not saved", "tr" => null, "status" => 401]);
       exit;
   }


    if(isset($_POST['loadROSPage'])){
        header('Content-Type: application/json');

        $hospital_no = cleanInput($_POST['hospital_no']);
        $app_no = cleanInput($_POST['app_no']);
        $html = '
        <input type="hidden" value="'.$app_no.'" id="ros_app_no"> 
        <input type="hidden" value="'.$hospital_no.'" id="ros_hospital_no"> 
        <div class="row">
                        <div class="col-md-12">
                        <label for="reg_select" class="req">Review of System</label>
                            <select name="ros_complain" id="ros_complain"  class="form-control" data-required="true" style="font-size:14px;">
                                <option  selected="selected" value="">--select review of system--</option>
                                <option value="other">Other Review of System</option>
                                <option value="General">General</option>
                                <option value="Blood/lymph">Blood/lymph</option>
                                <option value="Breasts">Breasts</option>
                                <option value="Chest">Chest</option>
                                <option value="Ears">Ears</option>
                                <option value="Endocrine">Endocrine</option>
                                <option value="Eyes">Eyes</option>
                                <option value="Gastroint">Gastroint</option>
                                <option value="Genitourin">Genitourin</option>
                                <option value="Head">Head</option>
                                <option value="Heart">Heart</option>
                                <option value="Man">Man</option>
                                <option value="Woman">Woman</option>
                                <option value="Mouth">Mouth</option>
                                <option value="Throat">Throat</option>
                                <option value="Musculosk">Musculosk</option>
                                <option value="Neurologic">Neurologic</option>
                                <option value="Nose">Nose</option>
                                <option value="Psychologic">Psychologic</option>
                                <option value="Skin">Skin</option>
                            </select>
                            <br>
                            <div id="ros_cat_wrap">

                            </div>


                            <div style="margin-top:20px;">
                                <div class="row">
                                    <div class="col-sm-12"> 
                                    <div class="panel panel-default">
                                
                                    <div class="panel-heading">
                                                <h4 class="panel-title"> Current ROS List <strong>[ CAPTURED ]</strong> </h4>
                                            </div>
                    
                                                    <table class="table table-striped">	
                                                    <thead>
                                                    <tr>
                                                        <th data-hide="phone,tablet">Date</th>
                                                        <th data-toggle="true">Review of System</th>
                                                        <th data-toggle="true">Reviewed By</th>
                                                        <th >Manage</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="____current_ros_wrap____">
                                                    
                                                </tbody>
                                            </table>
                                
                                    </div>
                                    </div>
                                </div>
                                </div>
                                <div style="margin-top:20px;">
                                <div class="row">
                                    <div class="col-sm-12"> 
                                    <div class="panel panel-default">
                                
                                    <div class="panel-heading">
                                                <h4 class="panel-title"> Past ROS List <strong>[ CAPTURED ]</strong> </h4>
                                            </div>
                    
                                                    <table class="table table-striped">	
                                                    <thead>
                                                    <tr>
                                                        <th data-hide="phone,tablet">Date</th>
                                                        <th data-toggle="true">Review of System</th>
                                                        <th data-toggle="true">Reviewed By</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="____past_ros_wrap____">
                                                    
                                                </tbody>
                                            </table>
                                
                                    </div>
                                    </div>
                                </div>
                                </div>
                        </div>
                        
                        
                    </div>

        ';

        $current_tr = currentROS($db, $hospital_no, $app_no);
        $past_tr = pastROS($db, $hospital_no, $app_no);
        echo json_encode(["html" => $html, "current_tr" => $current_tr, 'past_tr' => $past_tr]);
        exit;
    }


?>

<div class="modal inmodal fade" id="____review_of_system_modal____" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
    <div class="modal-dialog modal-lg" style="width: 60%;">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Review of System (ROS)</h4>
            </div>
			<div style=" max-height:600px; overflow:auto" >
            <div class="modal-body" id="____review_of_system_modal____body____">
                    
            </div>
            </div>
           <div class="modal-footer">
		<button type="button" class="btn btn-sm btn-danger " data-dismiss="modal">Close</button>
                        </div>
        </div>

	</div>
	            
</div>


<div class="modal inmodal fade" id="___ros_category_summary___modal___" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
    <div class="modal-dialog modal-lg" style="width: 30%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body" id="___rose_category_summary___body">
            <h4 class="modal-title" id=""><div class="alert alert-info"><strong>REVIEW OF SYSTEM</strong></div></h4>
            <label for="reg_textarea_message" class="req"> <span id="___ros_category_summary___item___"></span> [Specify Below] </label>
            <textarea name="other_complain" id="other_complain" cols="30" rows="3" class="form-control" data-required="true" data-minlength="10" spellcheck="false"></textarea>
            <br> <button class="btn btn-success" type="submit" onclick="addROSSummarybtn()">Add Data </button>
            </div>
        </div>
    </div>
</div>

<script>
    var selected_options = [];
    $(document).on('click', '#____review_of_system_btn____', function() { 
        selected_options = [];
        $('#____review_of_system_modal____').modal('show')
        $('#____review_of_system_modal____body____').html('<h6 class="text-center text-danger"> Loading, please wait...</h6>')
        $.ajax({
            url: '_review_of_system.php',
            method: "POST",
            data: { loadROSPage: true, hospital_no: "<?php echo $hospital_no;?>", app_no:  "<?php echo $appointment_number;?>"},
            success: function(response) {
                $('#____review_of_system_modal____body____').html(response.html)
                $('#____current_ros_wrap____').html(response.current_tr)
                $('#____past_ros_wrap____').html(response.past_tr)
            },
            error: function(err){console.log(err)}
        });
    })


    $(document).on('change', '#ros_complain', function() {
        selected_options = [];
        $('#ros_cat_wrap').html('<h6 class="text-center text-danger"> Loading, please wait...</h6>')
        
            $.ajax({
            url: '_review_of_system.php',
            method: "POST",
            data: { loadROSCat: true, cat: $(this).val()},
            success: function(response) {
                $('#ros_cat_wrap').html(response)
            },
            error: function(err){console.log(err)}
        });

    })

    $(document).on('click', '.cat_summary_btn', function() {
        $('#___ros_category_summary___modal___').modal('show')
        $('#___ros_category_summary___item___').html($(this).attr("data-name"))

    })



    $(document).on('change', '.ros_opt', function() {
       
       if($(this).is(':checked') ){
           selected_options.push($(this).val())
       }else{
        var index = selected_options.indexOf($(this).val());
        if (index !== -1) {
            selected_options.splice(index, 1);
        }
       }

       console.log(selected_options)
    })

   function addROSbtn(){
        if(selected_options.length > 0){
            $.ajax({
            url: '_review_of_system.php',
            method: "POST",
            data: { 
                saveROS: true, 
                selected_options: selected_options, 
                cat: $('#ros_complain').val(),
                app_no: $('#ros_app_no').val(),
                hospital_no: $('#ros_hospital_no').val()
            },
            success: function(response) {
                alert(response.message);
                $("#____current_ros_wrap____").html(response.tr)
              console.log(response)
            },
            error: function(err){console.log(err)}
        });
        }else{
            alert('No Option/Comment is provided')
        }
        
    }


function addROSSummarybtn(text_area_id){
    selected_options = [$('#other_complain').val()];
    addROSbtn()
    $('#other_complain').val("")
}

function deleteROS(id){
    $.ajax({
        url: '_review_of_system.php',
        method: "POST",
        data: { 
            deleteROS: true, 
            id: id
        },
        success: function(response) {
            alert(response.message);
            $("#____current_ros_wrap____").html(response.current_tr)
          console.log(response)
        },
        error: function(err){console.log(err)}
    });
}
    
    
    
</script>