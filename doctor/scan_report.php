<?php include("../Connections/Conn.php"); ?>

<?php

session_start();
?>


<!DOCTYPE html>
<html>

<head>

  <?php include("../inc/header.php"); ?>

<body>

  <div id="wrapper">

    <?php include("nav_side.php"); ?>


    <div id="page-wrapper" class="gray-bg">
      <?php include("nav_header.php"); ?>

      <?php


      if (isset($_GET["r"])) {
        $search = $_GET["r"];
        $part = explode("/", $search);
        $hosp_no = $part[1];
        $search = $part[0];
      } elseif (isset($_GET["e"])) {
        $search = $_GET["e"];
      }

      $stmt2 = $db->query("SELECT * FROM lab_manage WHERE labrequest_no='$search'");
      $row = $stmt2->fetch(PDO::FETCH_ASSOC);
      $hosp_no = $row['patient'];
      $report_status = $row['data_capture_status'];
      $entered_by = $row['entered_by'];
      $approved_by = $row['approved_by'];
      $business_service_center = $row['business_service_center'];
      $test_id = $row['test_id'];
      ?>

      <div class="wrapper wrapper-content">
        <div class="row">




          <div class="col-lg-12 animated fadeInRight">
            <div class="mail-box-header">
              <div class="pull-right tooltip-demo">
                <a href="index.php?vg=<?php echo $hosp_no; ?>" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Close & Return to Investigations"><i class="fa fa-times"></i> Close</a>
              </div>
              <h2>
                Report<?php echo ' /' . '<small>' . $title . '</small>'; ?>
              </h2>
              <?php echo $row['test_name']; ?>

            </div>



            <div class="mail-box">


              <?php if (isset($_GET['r'])) { ?>
                <div class="mail-body">

                  <?php echo $row['result_note'] . '<br>'; ?>
                  <br><br><br>
                  <hr>
                  <div class="pull-right"><?php if ($row['approved_by'] != "") {
                                            echo '<strong>APPROVED BY:</strong>' . '<br>' . $row['approved_by'];
                                          } ?></div>

                  <div class="clearfix"></div>

                  <div class="pull-left">

                  </div>
                  <div class="pull-right">
                  </div>
                </div>
              <?php } ?>






            </div>





          </div>

        </div>
      </div>
      <?php include("../inc/footer.php"); ?>

    </div>
  </div>


  <!-- Mainly scripts -->
  <script src="../js/jquery-2.1.1.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <script src="../js/plugins/metisMenu/jquery.metisMenu.js"></script>
  <script src="../js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

  <!-- Custom and plugin javascript -->
  <script src="../js/inspinia.js"></script>
  <script src="../js/plugins/pace/pace.min.js"></script>
  <script src="../js/plugins/chosen/chosen.jquery.js"></script>

  <!-- iCheck -->
  <script src="../js/plugins/iCheck/icheck.min.js"></script>

  <!-- SUMMERNOTE -->
  <script src="../js/plugins/summernote/summernote.min.js"></script>

  <script src="../js/plugins/toastr/toastr.min.js"></script>

  <?php if (isset($_GET['ach'])) { ?>
    <script>
      toastr.success('Attachment Uploaded Successfully!', 'Attachment', {
        timeOut: 2000
      })
    </script>
  <?php } ?>


  <script>
    $(document).on('click', '.attachment', function() {
      var attachement_id = $(this).attr("id");
      var res = attachement_id.split("__");

      //	$('#scan_request_no').val(res[0]);
      $('.modal-title').text('New Attachment:  ' + res[0] + ' (' + res[1] + ')');
      $('#attach_modal').modal('show');
      //	$('#attach_body').html(data); 

    });


    $(document).ready(function() {
      $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
      });


      $('.summernote').summernote();

    });


    $(".chosen-select").chosen({
      allow_single_deselect: true,
      enable_search_threshold: 10,
      no_results_text: 'Oops, nothing found!',
      width: "100%"
    });
    $('.chosen-drop').css({
      "width": "100%",
      "white-space": "nowrap"
    })


    var edit = function() {
      $('.click2edit').summernote({
        focus: true
      });
    };
    var save = function() {
      var aHTML = $('.click2edit').code(); //save HTML If you need(aHTML: array).
      $('.click2edit').destroy();
    };
  </script>


</body>

</html>