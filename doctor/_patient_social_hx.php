<?php

if (
    isset($_POST['loadSocialHx'])  ||
    isset($_POST['saveSocialHx'])
) {
    session_start();
    include("../Connections/Conn.php");
    include('objects.php');
    include('helpers.php');
}



if (isset($_POST['loadSocialHx'])) {
    header('Content-Type: application/json');

    $hospital_no = cleanInput($_POST['hospital_no']);
    // $app_no = cleanInput($_POST['app_no']);

    $social_hx = null;
    $sn = '';
    $complain = '';
    $social_hx_captured = '';

    $complain = '<table class="table table-striped table-bordered">
    <thead>
    <tr>
    <th width="5%">S/N</th>
    <th class="text-center">Social</th>
    <th width="50%" class="text-center">Answer <br> <small>[<b>Type below</b>]</small></th>
    
    </tr>
    </thead>
    <tbody>
    
            <tr>
                <td>1</td>
                <td>Do you smoke?</td>
                <td></td>
            </tr>
     
            <tr>
                <td>2</td>
                <td>Use smokeless tobacco?</td>
                <td></td>
            </tr>
     
            <tr>
                <td>3</td>
                <td>Do you drink alcohol?</td>
                <td></td>
            </tr>
     
            <tr>
                <td>4</td>
                <td>Beer cans per day?</td>
                <td></td>
            </tr>
     
            <tr>
                <td>5</td>
                <td>Do you take or have you taken recreational drugs?</td>
                <td></td>
            </tr>
     
            <tr>
                <td>6</td>
                <td>What kind of work do you do?</td>
                <td></td>
            </tr>
     
            <tr>
                <td>7</td>
                <td>Do you live alone?</td>
                <td></td>
            </tr>
     
            <tr>
                <td>8</td>
                <td>Who lives with you?</td>
                <td></td>
            </tr>
     
            <tr>
                <td>9</td>
                <td>How many children do you have?</td>
                <td></td>
            </tr>
     
            <tr>
                <td>10</td>
                <td>Does anyone complain that you snore?</td>
                <td></td>
            </tr>
     
            <tr>
                <td>11</td>
                <td>Do you stop breathing at night?</td>
                <td></td>
            </tr>
     
            <tr>
                <td>12</td>
                <td>Do you use CPAP?</td>
                <td></td>
            </tr>
     
            <tr>
                <td>13</td>
                <td>Caffeine intake</td>
                <td></td>
            </tr>
     
            <tr>
                <td>14</td>
                <td>Do you exercise?</td>
                <td></td>
            </tr>
     
            <tr>
                <td>15</td>
                <td>Are you at risk for AIDS or Hepatitis (e.g. sexual orientation, drug abuse, previous blood transfusion)?</td>
                <td></td>
            </tr>
    
            <tr>
                <td>15</td>
                <td>Others</td>
                <td></td>
            </tr>
     
    </tbody>
    </table>';
    $sh_sn = "";
    $stmt = $db->prepare("SELECT * FROM c_d_remarks WHERE hospital_no=? and cat_type='SH' AND status = '1' ORDER BY sn DESC LIMIT 1");
    $stmt->execute(array($hospital_no));
    $row = $stmt->fetch();
    if (!empty($row)) {
        $complain = $row['complain'];
        $sh_sn = $row['sn'];

        $social_hx = '<p>' . $complain . '</p>';
        $social_hx_captured = ' <br> Captured By: ' . $row["prepared_by"] . ' <br> Captured On: ' . date('d M,Y', strtotime($row['date_entry'])) . ' ';
    }


    echo json_encode(['social_hx_captured' => $social_hx_captured, 'social_hx' => $complain, 'sh_sn' => $sh_sn]);
    exit;
}


if (isset($_POST['saveSocialHx'])) {
    $social_hx_sn = intval(cleanInput($_POST['sh_sn']));
    $social_hx_notes = $_POST['sh_notes'];
    $hospital_no = cleanInput($_POST['hospital_no']);
    $app_no = cleanInput($_POST['app_no']);
    header('Content-Type: application/json');
    $social_hx = '';

    $now_setdate = date('Y-m-d H:i:s');

    if (!empty($social_hx_sn) && $social_hx_sn != 0) {

        $stmt = $db->prepare('UPDATE c_d_remarks SET complain = ?, updated_at =?, updated_by = ? WHERE sn=?');
        $save = $stmt->execute(array($social_hx_notes, $now_setdate, $_SESSION['id'], $social_hx_sn));
    } else {
        $stmt = $db->prepare('INSERT INTO c_d_remarks (complain, app_no, hospital_no, cat_type, prepared_by, created_by)  VALUES (?, ?, ?, ?, ?, ?) ');
        $save = $stmt->execute(array($social_hx_notes, $app_no, $hospital_no, 'SH', $_SESSION['fullname'], $_SESSION['id']));
    }

    if ($save) {
        $message = "Saved";
        $social_hx = '<p>' . $social_hx_notes . '</p><br> Captured By: ' . $_SESSION["fullname"] . ' <br> Captured On: ' . date('d M,Y', strtotime("" . date('D d M, Y'))) . ' ';
    }

    // Smoke alot and also dring alcohol and hard drugs

    echo json_encode(["message" => $message, "social_hx" => $social_hx]);

    exit;
}




?>


<div class='modal inmodal fade' id='____social_hx_modal____' tabindex='-1' role='dialog' aria-hidden='true' data-keyboard='false'>
    <div class='modal-dialog modal-lg' style='width: 60%;'>
        <div class='modal-content'>
            <div class='modal-header'>
                <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>×</button>
                <h4 class='modal-title' id=''>Add/Edit Patient Social Hx</h4>
            </div>
            <div class='modal-body' style='min-height: 200px'>
                <div id="____social_hx_modal_body____">
                    <div class="form-control trumbowygEditor" id="sh_notes">Placeholder</div>
                    <hr>
                    <div class="text-center">
                        <input type="hidden" id="sh_sn" value="">
                        <input type="hidden" id="sh_hospital_no" value="<?= $hospital_no; ?>">
                        <input type="hidden" id="sh_app_no" value="">
                        <button class="btn btn-primary " onclick="saveSocialHx()">Save </button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var sh_hospital_no = "<?php echo $hospital_no; ?>";
    var sh_app_no = "<?php echo $appointment_number; ?>";
    $(document).ready(function() {
        $(document).on('click', '#____add_edit_social_hx_btn____', function() {

            $('#____social_hx_modal____').modal('show')
            $('#____social_hx_wrap____').html('<h6 class="text-center text-danger"> Loading, please wait...</h6>')
            $.ajax({
                url: '_patient_social_hx.php',
                method: "POST",
                data: {
                    loadSocialHx: true,
                    hospital_no: sh_hospital_no,
                    app_no: sh_app_no
                },
                success: function(response) {

                    $('#sh_notes').html(response.social_hx)
                    refreshEditor('sh_notes')
                    $('#sh_sn').val(response.sh_sn)



                },
                error: function(err) {
                    console.log(err)
                }
            });


        });

        $(document).on('click', '#___social_hx_btn___', function() {

            $('#____social_hx_modal____').modal('show')
            $('#____social_hx_modal_body____').html('<h6 class="text-center text-danger"> Loading, please wait...</h6>')
            $.ajax({
                url: '_patient_social_hx.php',
                method: "POST",
                data: {
                    loadSocialHx: true,
                    hospital_no: sh_hospital_no,
                    app_no: sh_app_no
                },
                success: function(response) {
                    $('#____social_hx_modal_body____').html(response.html)
                    $("#____social_hx_wrap____").html(response.social_hx)
                },
                error: function(err) {
                    console.log(err)
                }
            });
        });
    })


    function saveSocialHx() {
        if ($('#sh_notes').html() != "" && $('#sh_notes').html().length > 5) {

            $.ajax({
                url: '_patient_social_hx.php',
                method: "POST",
                data: {
                    saveSocialHx: true,
                    sh_notes: $('#sh_notes').html(),
                    sh_sn: $('#sh_sn').val(),
                    app_no: phe_app_no,
                    hospital_no: phe_hospital_no
                },
                success: function(response) {
                    alert(response.message);
                    $("#____social_hx_wrap____").html(response.social_hx)
                    $('.trumbowygEditor').trumbowyg({
                        btns: []
                    });


                },
                error: function(err) {
                    console.log(err)
                }
            });
        } else {
            alert('Social is short or not provided')
        }
    }
</script>