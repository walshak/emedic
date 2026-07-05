<?php

include('../Connections/Conn.php');
?>

<!DOCTYPE html>
<html>

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">


  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link href="../font-awesome/css/font-awesome.css" rel="stylesheet">
  <link href="../css/animate.css" rel="stylesheet">
  <link href="../smernote/css/plugins/summernote/summernote.css" rel="stylesheet">
  <link href="../smernote/css/plugins/summernote/summernote-bs3.css" rel="stylesheet">
  <link href="../css/style.css" rel="stylesheet">

</head>

<body>

  <?php
  if (isset($_POST["patient_nos"]) or isset($_POST["note_id"])) {

    if (isset($_POST["patient_nos"])) {

      $patient_nos = $_POST["patient_nos"];
      $parts = explode("__", $patient_nos);
      $app_no = $parts[0];
      $hos_no = $parts[1];
      $names = $parts[2];
      $mode = 'add';
    } else {

      $note_id = $_POST["note_id"];
      $parts = explode("__", $note_id);
      $id = $parts[0];
      $hos_no = $parts[2];
      $mode = 'edit';

      $stmt3 = $db->query("SELECT * FROM notes WHERE sn='$id'");
      if ($stmt3->rowCount() > 0) {
        $row_billing = $stmt3->fetch(PDO::FETCH_ASSOC);
      }
    }

  ?>
    <div id="wrapper">

      <div class="row">
        <div class="col-lg-12">
          <div class="ibox float-e-margins">
            <div class="ibox-content no-padding">

              <form method="POST" action="index.php?progress">

                <div class="form_sep">
                  <label for="reg_select" class="">Focus</label>
                  <input type="text" id="focus" name="focus" class="form-control input-sm" placeholder="Type Your Focus Here">
                </div>

                <div class="form_sep">

                  <textarea name="pro_note" id="pro_note" cols="45" rows="5" maxlength="160" class="summernote" placeholder="Type Your Message Here">
						<?php if ($mode == 'edit') {
              echo $row_billing['notes'];
            } else {

              echo "<br>";
            }
            ?>
                            
                        </textarea>
                </div>

                <button class="btn btn-success btn btn-sm" type="submit" name="save_notes" id="save_notes">Save Note</button>
                <input type="hidden" name="hos_no" value="<?php echo $hos_no; ?>" />
                <input type="hidden" name="app_no" value="<?php echo $app_no; ?>" />
                <input type="hidden" name="mode" value="<?php echo $mode; ?>" />
                <input type="hidden" name="sn" value="<?php echo $row_billing['sn']; ?>" />
              </form>
            </div>
          </div>
        </div>
      </div>



    </div>



  <?php } ?>


  <?php include("../inc/footer_scripts.php"); ?>


  <script>
    $(document).ready(function() {

      $('.summernote').summernote();

    });
  </script>

</body>

</html>