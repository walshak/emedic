<?php

if (
    isset($_POST['loadPHEPage'])  ||
    isset($_POST['deletePHE'])  ||
    isset($_POST['addPhExamination'])
) {
    session_start();
    include("../Connections/Conn.php");
    include('objects.php');
    include('helpers.php');
}
$ph_exams = [
    ['name' => 'Abdomen', 'col' => 'abdo'],
    ['name' => 'Anorectal', 'col' => 'ano'],
    ['name' => 'Cardiovascular', 'col' => 'card'],
    ['name' => 'Eyes', 'col' => 'eyes'],
    ['name' => 'ENT', 'col' => 'ent'],
    ['name' => 'Extremities', 'col' => 'extre'],
    ['name' => 'Gastrointestinal', 'col' => 'gas'],
    ['name' => 'Genitourinary', 'col' => 'gen'],
    ['name' => 'General Appearance', 'col' => 'general_a'],
    ['name' => 'Head and Neck', 'col' => 'neck'],
    ['name' => 'Respiratory', 'col' => 'res'],
    ['name' => 'Lymph Nodes', 'col' => 'lymph'],
    ['name' => 'Musculoskeletal', 'col' => 'mus'],
    ['name' => 'Neurological', 'col' => 'neu'],
    ['name' => 'Nutritional Status', 'col' => 'nu'],
    ['name' => 'Skin', 'col' => 'skin'],
    ['name' => 'Others', 'col' => 'others'],
];

if (isset($_POST['loadPHEPage'])) {
    header('Content-Type: application/json');

    $hospital_no = cleanInput($_POST['hospital_no']);
    $app_no = cleanInput($_POST['app_no']);
    $html =  '<div style="margin-top:20px;">
    <div class="row">
        <div class="col-sm-12"> 
        <div class="panel panel-default">
    
        <div class="panel-heading">
                    <h4 class="panel-title"> Current Physical Examination  <strong>[ CAPTURED ]</strong> </h4>
                </div>

                        <table class="table table-striped">	
                        <thead>
                        <tr>
                            <th data-hide="phone,tablet">Date</th>
                            <th data-toggle="true">Physical Examination</th>
                            <th data-toggle="true">Captured By</th>
                            <th >Manage</th>
                        </tr>
                    </thead>
                    <tbody id="____current_phe_wrap____">
                        
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
                    <h4 class="panel-title"> Past Physical Examination   </h4>
                </div>

                        <table class="table table-striped">	
                        <thead>
                        <tr>
                            <th data-hide="phone,tablet">Date</th>
                            <th data-toggle="true">Physical Examination</th>
                            <th data-toggle="true">Captured By</th>
                        </tr>
                    </thead>
                    <tbody id="____past_phe_wrap____">
                        
                    </tbody>
                </table>
    
        </div>
        </div>
    </div>
    </div>
    ';


    $tr = currentPHE($db, $hospital_no, $app_no, $ph_exams);
    $past_tr = pastPHE($db, $hospital_no, $app_no, $ph_exams);

    echo json_encode(["html" => $html, 'current_tr' => $tr, 'past_tr' => $past_tr]);
    exit;
}






function currentPHE($db, $hospital_no, $app_no, $ph_exams)
{
    $tr = "";
    $stmt = $db->prepare("SELECT * FROM physical_examination WHERE app_no=? AND hospital_no=?  ORDER BY sn DESC");
    $stmt->execute([$app_no, $hospital_no]);
    $phe_list = $stmt->fetchAll();
    foreach ($phe_list as $key => $phe) {
        $string = "";
        $id = $phe['sn'];
        foreach ($ph_exams as $key => $ph_exam) { //// loop through all columns in a row
            if (!empty($phe[$ph_exam["col"]])) {
                $string .= '<b>' . $ph_exam["name"] . ': </b> ' . $phe[$ph_exam["col"]] . ' ,';
            }
        }

        $tr .= '
            <tr>
            <td>' . date('d M,Y h:i a', strtotime($phe['date_captured'])) . '</td>
            <td>' . $string . '</td>
            <td>' . $phe["doctor_name"] . '</td>
            <td>' . ($phe["doctor_name"] ==  $_SESSION["fullname"] ? "<a href='#' <a href='#' class='text-danger'  onclick=deletePHE(" . $id . ")>[Remove]</a>" : "") . '</td>
        </tr>
            ';
    }


    return $tr;
}


function pastPHE($db, $hospital_no, $app_no, $ph_exams)
{
    $tr = "";
    $stmt = $db->prepare("SELECT * FROM physical_examination WHERE app_no !=? AND hospital_no=?  ORDER BY sn DESC LIMIT 10");
    $stmt->execute([$app_no, $hospital_no]);
    $phe_list = $stmt->fetchAll();
    foreach ($phe_list as $key => $phe) {
        $string = "";
        $id = $phe['sn'];
        foreach ($ph_exams as $key => $ph_exam) { //// loop through all columns in a row
            if (!empty($phe[$ph_exam["col"]])) {
                $string .= '<b>' . $ph_exam["name"] . ': </b> ' . $phe[$ph_exam["col"]] . ' ,';
            }
        }

        $tr .= '
                <tr>
                <td>' . date('d M,Y h:i a', strtotime($phe['date_captured'])) . '</td>
                <td>' . $string . '</td>
                <td>' . $phe["doctor_name"] . '</td>
            </tr>
                ';
    }


    return $tr;
}



if (isset($_POST['deletePHE'])) {
    $sn = intval(cleanInput($_POST['id']));
    $hospital_no = cleanInput($_POST['hospital_no']);
    $app_no = cleanInput($_POST['app_no']);
    header('Content-Type: application/json');
    $stmt = $db->prepare("DELETE FROM physical_examination WHERE sn=?");
    $stmt->execute([$sn]);

    $current_tr = currentPHE($db, $hospital_no, $app_no, $ph_exams);
    echo json_encode(["message" => "Removed", "current_tr" => $current_tr, "status" => 200]);

    exit;
}


if (isset($_POST['addPhExamination'])) {
    header('Content-Type: application/json');
    $hospital_no = cleanInput($_POST['hospital_no']);
    $app_no = cleanInput($_POST['app_no']);
    $phe_comment = cleanInput($_POST['phe_comment']);
    $phe_strings = cleanInput($_POST['phe']);
    $now_setdate = date('Y-m-d H:i:s');

    $phe_arr = explode('__', $phe_strings);
    $phe = $phe_arr[0];
    $phe_tbl_column = $phe_arr[1];

    if (!empty($phe_tbl_column)) {

        $stmt = $db->prepare("SELECT * FROM physical_examination WHERE app_no=? AND hospital_no=? ");
        $stmt->execute([$app_no, $hospital_no]);
        if ($stmt->rowCount() == 0) {
            $sql = "INSERT INTO physical_examination (hospital_no, app_no, $phe_tbl_column, doctor_name,date_captured) VALUES (?, ?, ?, ?,?)";
            $stmt = $db->prepare($sql);
            $saved = $stmt->execute([$hospital_no, $app_no, $phe_comment, $_SESSION['fullname'], $now_setdate]);
        } else {
            $sql = "UPDATE physical_examination SET $phe_tbl_column = ? WHERE hospital_no = ? AND app_no = ?";
            $stmt = $db->prepare($sql);
            $saved = $stmt->execute([$phe_comment, $hospital_no, $app_no]);
        }



        if ($saved) {
            $tr = currentPHE($db, $hospital_no, $app_no, $ph_exams);
            echo json_encode(["message" => "Saved", "current_tr" => $tr, "status" => 200]);
            exit;
        }
    }


    exit;
}



?>

<div class="modal inmodal fade" id="____physical_examination_modal____" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
    <div class="modal-dialog modal-lg" style="width: 60%;">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Physical Examination</h4>
            </div>

            <div class="modal-body">

                <div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="reg_select" class="req">Physical Examination</label>
                            <select name="phe" id="phe" class="form-control" data-required="true" style="font-size:14px;">
                                <option selected="selected" value="">-- Select Physical Examination --</option>
                                <option value="Abdomen__abdo">Abdomen</option>
                                <option value="Anorectal__ano">Anorectal</option>
                                <option value="Cardiovascular__card">Cardiovascular</option>
                                <option value="Eyes__eyes">Eyes</option>
                                <option value="ENT__ent">ENT</option>
                                <option value="Extremities__extre">Extremities</option>
                                <option value="Gastrointestinal__gas">Gastrointestinal</option>
                                <option value="Genitourinary__gen">Genitourinary</option>
                                <option value="General Appearance__general_a">General Appearance</option>
                                <option value="Head and Neck__neck">Head and Neck</option>
                                <option value="Respiratory__res">Respiratory</option>
                                <option value="Lymph Nodes__lymph">Lymph Nodes</option>
                                <option value="Musculoskeletal__mus">Musculoskeletal</option>
                                <option value="Neurological__neu">Neurological</option>
                                <option value="Nutritional Status__nu">Nutritional Status</option>
                                <option value="Skin__skin">Skin</option>
                                <option value="Others__others">Others</option>


                            </select>
                        </div>
                        <div class="col-md-6">
                            <div>
                                <label for="">Enter Comment [e.g. Normal or Abnormal]</label>
                                <textarea name="phe_comment" id="phe_comment" cols="30" rows="3" class="form-control" data-required="true" data-minlength="10" spellcheck="false"></textarea>

                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="text-right"> <button class="btn btn-success" type="submit" onclick="addPhExamination()">Add Data </button></div>
                    <hr>

                </div>
                <div style=" max-height:400px; overflow:auto">
                    <div id="____physical_examination_modal____body_____">

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger " data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>




<script>
    var phe_hospital_no = "<?php echo $hospital_no; ?>";
    var phe_app_no = "<?php echo $appointment_number; ?>";
    $(document).on('click', '#____physical_examination_btn____', function() {
        selected_options = [];
        $('#____physical_examination_modal____').modal('show')
        $('#____physical_examination_modal____body_____').html('<h6 class="text-center text-danger"> Loading, please wait...</h6>')
        $.ajax({
            url: '_physical_exams.php',
            method: "POST",
            data: {
                loadPHEPage: true,
                hospital_no: "<?php echo $hospital_no; ?>",
                app_no: "<?php echo $appointment_number; ?>"
            },
            success: function(response) {
                $('#____physical_examination_modal____body_____').html(response.html)
                $("#____current_phe_wrap____").html(response.current_tr)
                $("#____past_phe_wrap____").html(response.past_tr)
            },
            error: function(err) {
                console.log(err)
            }
        });
    });

    function addPhExamination() {
        if ($('#phe').val() != "" && $('#phe_comment').val() != "") {

            $.ajax({
                url: '_physical_exams.php',
                method: "POST",
                data: {
                    addPhExamination: true,
                    phe: $('#phe').val(),
                    phe_comment: $('#phe_comment').val(),
                    app_no: phe_app_no,
                    hospital_no: phe_hospital_no
                },
                success: function(response) {
                    alert(response.message);

                    $("#____current_phe_wrap____").html(response.current_tr)
                    $('#phe').val('');
                    $('#phe_comment').val('');

                },
                error: function(err) {
                    console.log(err)
                }
            });
        } else {
            alert('Select Physical Exam And Enter Comment!')
        }
    }



    function deletePHE(id) {
        $.ajax({
            url: '_physical_exams.php',
            method: "POST",
            data: {
                deletePHE: true,
                app_no: phe_app_no,
                hospital_no: phe_hospital_no,
                id: id
            },
            success: function(response) {
                alert(response.message);
                $("#____current_phe_wrap____").html(response.current_tr)
                console.log(response)
            },
            error: function(err) {
                console.log(err)
            }
        });
    }
</script>