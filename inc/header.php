<head>
  <title>WebMedic | <?= !empty($_SESSION['Designation']) ? $_SESSION['Designation'] : $_SESSION['speciality'] ?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <link href="../font-awesome/css/font-awesome.css" rel="stylesheet">
  <link href="../css/plugins/iCheck/custom.css" rel="stylesheet">

  <link href="../css/plugins/summernote/summernote.css" rel="stylesheet">
  <link href="../css/plugins/summernote/summernote-bs3.css" rel="stylesheet">

  <link href="../css/plugins/chosen/chosen.css" rel="stylesheet">

  <link href="../css/plugins/colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">

  <link href="../css/plugins/cropper/cropper.min.css" rel="stylesheet">

  <link href="../css/plugins/switchery/switchery.css" rel="stylesheet">

  <link href="../css/plugins/jasny/jasny-bootstrap.min.css" rel="stylesheet">

  <link href="../css/plugins/nouslider/jquery.nouislider.css" rel="stylesheet">

  <!-- <link href="../css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css" rel="stylesheet"> -->

  <!-- <link href="../css/plugins/clockpicker/clockpicker.css" rel="stylesheet"> -->

  <!-- <link href="../css/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet"> -->

  <link href="../css/plugins/datapicker/datepicker3.css" rel="stylesheet">

  <link href="../css/plugins/ionRangeSlider/ion.rangeSlider.css" rel="stylesheet">
  <link href="../css/plugins/ionRangeSlider/ion.rangeSlider.skinFlat.css" rel="stylesheet">

  <link href="../css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
  <link href="../css/plugins/dataTables/dataTables.responsive.css" rel="stylesheet">
  <link href="../css/plugins/dataTables/dataTables.tableTools.min.css" rel="stylesheet">

  <!-- <link href="css/plugins/dataTables/datatables.min.css" rel="stylesheet"> -->

  <!-- c3 Charts -->
  <link href="../css/plugins/c3/c3.min.css" rel="stylesheet">

  <link href="../css/plugins/toastr/toastr.min.css" rel="stylesheet">
  <link href="../css/others.css" rel="stylesheet">
  <link href="../css/plugins/textSpinners/spinners.css" rel="stylesheet">
  <link href="../css/animate.css" rel="stylesheet">
  <link href="../css/style.css" rel="stylesheet">
  <link href="../css/sweetalert2.min.css" rel="stylesheet">
  <link href="../css/select2.min.css" rel="stylesheet">
  <link href="../css/select2-bootstrap.min.css" rel="stylesheet">
  <link href="../css/bootstrap-timepicker.min.css">
  <link href="../css/timepicker.css">
  <!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"> -->
  <link rel="stylesheet" href="../js/vendors/editor/dist/ui/trumbowyg.css">
  <link rel="stylesheet" href="../js/vendors/editor/dist/plugins/colors/ui/trumbowyg.colors.min.css">
  </style>

  <style>
    .search-table-outter {
      overflow-x: scroll;
    }

    .blink {
      animation: blink 1s steps(1, end) infinite;
    }


    @keyframes blink_2 {
      50% {
        opacity: 0;
      }
    }


    @keyframes blink {
      0% {
        opacity: 1;
      }

      50% {
        opacity: 0;
      }

      100% {
        opacity: 1;
      }
    }

    .nav.nav-tabs>li.active {
      background-color: aquamarine;

    }

    .nav.nav-tabs>li {
      list-style-position: inside;
      border-top: 1px solid #B2BEB5;
      border-right: 1px solid #B2BEB5;
      border-left: 1px solid #B2BEB5;
      border-radius: 3px;
    }

    .modal_lock {
      display: none;
      align-items: center;
      justify-content: center;
      position: fixed;
      z-index: 9999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content_lock {
      background-color: #fefefe;
      padding: 20px;
      border: 1px solid #888;
      width: 100%;
      height: 100%;
      max-width: 4000px;
      /* Adjust as needed */
    }

    .close {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
    }

    .close:hover,
    .close:focus {
      color: black;
      text-decoration: none;
      cursor: pointer;
    }


    #patientAlertModal {
      z-index: 1060 !important;
      /* Ensure it's on top of others */
    }

    .modal-backdrop.show {
      z-index: 1059 !important;
      /* Just below the modal */
    }

    #refer_appoint {
      display: none;
    }


    .typeahead {
      max-height: 200px;
      /* adjust as needed */
      overflow-y: auto;
      overflow-x: hidden;
    }
  </style>


  <style>
    .results-available-alert {
      position: fixed;
      top: 10px;
      left: 500px;
      background: #ebeaca;
      padding: 10px 15px;
      border-radius: 5px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      z-index: 1000;
      height: 33px;
    }

    .queue-alert {
      position: fixed;
      top: 10px;
      left: 300px;
      background: #ebeaca;
      padding: 10px 15px;
      border-radius: 5px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      z-index: 1000;
      height: 33px;
    }


    .pharm-queue-alert {
      position: fixed;
      top: 10px;
      left: 300px;
      background: #ebeaca;
      padding: 10px 15px;
      border-radius: 5px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      z-index: 1000;
    }

    .pharm-queue-alert {
      height: 35px;
      overflow: hidden;
      /* prevents content overflow */
    }

    .claim-queue-alert {
      position: fixed;
      top: 10px;
      right: 835px;
      background: #ebeaca;
      padding: 10px 15px;
      border-radius: 5px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      z-index: 1000;
    }
  </style>
</head>

<?php
$alertCount = 0;
$grp_idv_no  = 0;
$bill_account_status  = 0;
$title  = null;

?>