<?php include("../Connections/Conn.php"); ?>

<?php

if (isset($_POST['hosp_no'])) {
  $hos_no = ($_POST['hosp_no']);
  $pro_inv = ($_POST['inv']);
  $app_no = ($_POST['app_no']);
  $names = ($_POST['names']);
  $invoice_type = ($_POST['invoice_type']);
  if ($invoice_type == '') {
    $invoice_type = 'inv';
  }
  $setdate = date("Y-m-d");
}

if (isset($_POST['print_inv'])) {
  if (!empty($_REQUEST['inv'])) {
    $header = 'INVOICE';
    //$sub_title='THIS IS NOT A RECEIPT';
    $action = 'i';
  } else {
    header("location:pos_main2.php?e&hos_no=$hos_no&$invoice_type");
  }
}

if (isset($_POST['print_recep'])) {

  if (!empty($_REQUEST['inv'])) {
    $header = 'RECEIPT';
    //$sub_title='RECEIPT';
    $action = 'r';
  } else {
    header("location:pos_main2.php?e&hos_no=$hos_no&$invoice_type");
  }
}



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <link rel="stylesheet" href="css/pages.css" type="text/css" />
  <link rel="icon" href="images/favicon.ico" type="image/x-icon">
  <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
  <script type="text/javascript" src="js/jquery.min.js"></script>
  <script type="text/javascript" src="js/watermarkify.0.6.js"></script>
  <script type="text/javascript" src="js/watermarkify.0.6.min.js"></script>
  <script type="text/javascript" src="js/jquery.easing.1.3.js"></script>
  <script type="text/javascript" src="js/jquery.js"></script>
  <script type="text/javascript" src="menu/jquery-1.4.2.min.js"></script>
  <script type="text/javascript" src="menu/jquery.fixedMenu.js"></script>
  <link rel="stylesheet" type="text/css" href="menu/fixedMenu_style1.css" />
  <script>
    $('document').ready(function() {
      $('.menu').fixedMenu();
    });
  </script>


  <title><?php echo $siteName; ?></title>
  <style type="text/css">
    .style2 {
      color: #FFFFFF;
      font-weight: bold;
    }

    .style7 {
      font-size: 10px;
      color: #999999;
    }

    .style10 {
      color: #999999;
      font-weight: bold;
      text-align: left;
      font-size: 11px;
    }

    .style11 {
      color: #000000
    }

    .style12 {
      color: #000000;
      font-weight: bold;
    }
  </style>
</head>

<body>
  <table width="278" height="353" border="0" cellpadding="2" cellspacing="2" id="searchBorder" bgcolor="#FFFFFF">
    <tr>
      <td height="34"><input type="button" onClick="window.print()" value="PRINT" /></td>
      <td height="34">

        <a href="<?php if (isset($_POST['view'])) {
                    echo "pos_main2.php?hos_no=$hos_no&prt";
                  } else {
                    echo "pos_main2.php?hos_no=$hos_no&$invoice_type";
                  } ?>">Close</a>
      </td>
    </tr>
    <tr>
      <td height="55" colspan="2">
        <div align="center"><b>
            <h3><?php echo $header; ?></h3>
          </b></div>
        <div align="center"><img src="../img/logo.png" width="98" height="55" /></div>
        <div align="center">(Hospital Copy)</div>
      </td>
    </tr>
    <tr>
      <td width="106" height="31"><b>Hospital No:</b></td>
      <td width="149"><?php echo $hos_no; ?></td>
    </tr>
    <tr>
      <td height="27"><b>Names:</b></td>
      <td height="27"><?php echo $names; ?></td>
    </tr>
    <tr>
      <td height="136" colspan="2">
        <table width="270" border="0">
          <tr>
            <td height="23"><b>S/N</b></td>
            <td><b>Description</b></td>
            <td><b>Qty</b></td>
            <td><b>Amount</b></td>
          </tr>
          <tr>
            <?php
            $cnt = 0;
            $TotalTrans = 0;
            $pro_inv = $_REQUEST['inv'];
            // echo $pro_inv;
            //print_r ($pro_inv);
            $amount = 0;
            for ($i = 0; $i < count($pro_inv); $i++) {
              //$inv_id = $pro_inv[$i];

              $inv_id = $pro_inv[$i];
              $break = explode("__", $inv_id);
              $sn = $break[0];
              $item_services = $break[1];
              $amt = $break[2];
              $hmo_amt = $break[3];
              $serv_grp = $break[4];
              $qty = $break[5];
              $paystatus = $break[6];
              $invoice_status = $break[7];

              if ($amt > 0) {
                $amount = $amt;
              } else {
                $amount = $hmo_amt;
              }

              if ($action == 'i' and $invoice_status == 1) {
                $cnt++; ?>
                <td height="24"><?php echo $cnt; ?></td>
                <td><?php echo $item_services; ?></td>
                <td><?php echo $qty; ?></td>
                <td><?php echo $amount; ?></td>
                <td><?php
                    $TotalTrans = $TotalTrans + $amount;
                  } elseif ($action == 'r' and $paystatus == 1) {
                    $cnt++; ?>
                <td height="24"><?php echo $cnt; ?></td>
                <td><?php echo $item_services; ?></td>
                <td><?php echo $qty; ?></td>
                <td><?php echo $amount; ?></td>
                <td><?php
                    $TotalTrans = $TotalTrans + $amount;
                  }
                    ?>

                </td>
          </tr>
        <?php
            }
        ?>
        <tr>
          <td height="13">&nbsp;</td>
          <td><b>Total:</b></td>
          <td>&nbsp;</td>
          <td>
            <hr style="border:1px dotted;" />
            <?php
            echo "<strong>" . number_format($TotalTrans) . "</strong>";
            ?>
            <hr style="border:1px dotted;" />
          </td>
        </tr>
        </table>
        <hr style="border:1px dotted;" />
      </td>
    </tr>
    <tr>
      <td height="27"><b>Date &amp; Time:</b></td>
      <td height="27"><?php echo $setdate; ?></td>
    </tr>
    <tr>
      <td height="27"><b> Generated By:</b></td>
      <td height="27"><?php echo $_SESSION['fullname']; ?></td>
    </tr>
  </table>
  <table width="278" height="317" border="0" cellpadding="2" cellspacing="2" id="searchBorder" bgcolor="#FFFFFF">
    <tr>
      <td height="55" colspan="2">
        <div align="center"><b>
            <h3><?php echo $header; ?></h3>
          </b></div>
        <div align="center"><img src="../img/logo.png" width="98" height="55" /></div>
        <div align="center">(Customer's Copy)</div>
      </td>
    </tr>
    <tr>
      <td width="106" height="31"><b>Hospital No:</b></td>
      <td width="149"><?php echo $hos_no; ?></td>
    </tr>
    <tr>
      <td height="27"><b>Names:</b></td>
      <td height="27"><?php echo $names; ?></td>
    </tr>
    <tr>
      <td height="136" colspan="2">
        <table width="270" border="0">
          <tr>
            <td height="23"><b>S/N</b></td>
            <td><b>Description</b></td>
            <td><b>Qty</b></td>
            <td><b>Amount</b></td>
          </tr>
          <tr>
            <?php
            $cnt = 0;
            $TotalTrans = 0;
            $pro_inv = $_REQUEST['inv'];
            // echo $pro_inv;
            //print_r ($pro_inv);
            $amount = 0;
            for ($i = 0; $i < count($pro_inv); $i++) {
              //$inv_id = $pro_inv[$i];

              $inv_id = $pro_inv[$i];
              $break = explode("__", $inv_id);
              $sn = $break[0];
              $item_services = $break[1];
              $amt = $break[2];
              $hmo_amt = $break[3];
              $serv_grp = $break[4];
              $qty = $break[5];
              $paystatus = $break[6];
              $invoice_status = $break[7];

              if ($amt > 0) {
                $amount = $amt;
              } else {
                $amount = $hmo_amt;
              }

              if ($action == 'i' and $invoice_status == 1) {
                $cnt++; ?>
                <td height="24"><?php echo $cnt; ?></td>
                <td><?php echo $item_services; ?></td>
                <td><?php echo $qty; ?></td>
                <td><?php echo $amount; ?></td>
                <td><?php
                    $TotalTrans = $TotalTrans + $amount;
                  } elseif ($action == 'r' and $paystatus == 1) {
                    $cnt++; ?>
                <td height="24"><?php echo $cnt; ?></td>
                <td><?php echo $item_services; ?></td>
                <td><?php echo $qty; ?></td>
                <td><?php echo $amount; ?></td>
                <td><?php
                    $TotalTrans = $TotalTrans + $amount;
                  }
                    ?>

                </td>
          </tr>
        <?php
            }
        ?>

      </td>

    <tr>
      <td height="13">&nbsp;</td>
      <td><b>Total:</b></td>
      <td>&nbsp;</td>
      <td>
        <hr style="border:1px dotted;" />
        <?php
        //  $sqlSum = "SELECT *, SUM(pay) AS TotalTrans FROM patient_ap_services WHERE app_no='$app_no' AND invoice_status='1'";
        //$resSum = mysql_query($sqlSum);
        //$fetchSum = mysql_fetch_assoc($resSum);
        echo "<strong>" . number_format($TotalTrans) . "</strong>";
        ?>
        <hr style="border:1px dotted;" />
      </td>
    </tr>
  </table>
  <hr style="border:1px dotted;" />
  </td>
  </tr>
  <tr>
    <td height="27"><b>Date &amp; Time:</b></td>
    <td height="27"><?php echo $setdate; ?></td>
  </tr>
  <tr>
    <td height="27"><b> Generated By:</b></td>
    <td height="27"><?php echo $_SESSION['fullname']; ?></td>
  </tr>
  </table>
</body>

</html>