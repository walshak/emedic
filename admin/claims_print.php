<?php include('inc/header.php');
include('Connections/Conn.php');

if (isset($_GET['hos_no'])) {
    // Function to sanitize input (this should be a custom function you define or replace with a suitable method)
    function sanitize($data)
    {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    $app_no = sanitize($_GET['app_no']);
    $hos_no = sanitize($_GET['hos_no']);

    try {

        // Prepare the first query
        $query1 = "SELECT e.*, i.insurance_name, i.interest, i.payment_mode, i.services_access, i.insurance_type 
                   FROM enrollee as e 
                   INNER JOIN insurance_tbl as i ON e.hmo_no = i.insurance_no 
                   WHERE e.hospital_no = :hos_no AND e.status = :status";

        $stmt1 = $db->prepare($query1);
        $stmt1->execute([':hos_no' => $hos_no, ':status' => 'active']);

        if ($stmt1->rowCount() == 0) {
            // If no records found, redirect to dashboard or handle as needed
            // header("Location: dashboard.php");
        } else {
            $row_rstSelect_d = $stmt1->fetch();

            // Prepare the second query
            $query2 = "SELECT date_ap, ap_time, appt_no, auth_code, ap_type 
                       FROM apptm 
                       WHERE hospital_no = :hos_no 
                       ORDER BY sn DESC 
                       LIMIT 1";

            $stmt2 = $db->prepare($query2);
            $stmt2->execute([':hos_no' => $hos_no]);

            if ($stmt2->rowCount() > 0) {
                $row_rstSelect = $stmt2->fetch();
                $app_no = $row_rstSelect['appt_no'];
                $date_ap = $row_rstSelect['date_ap'];
                $ap_time = $row_rstSelect['ap_time'];
                $ap_type = $row_rstSelect['ap_type'];
                $auth_code = $row_rstSelect['auth_code'];
                echo $auth_code;
            } else {
                $date_ap = '';
            }
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}


function createRandomPassword()
{
    $chars = "003232303232023232023456789";
    srand((float)microtime() * 1000000);
    $i = 0;
    $pass = '';
    while ($i <= 7) {

        $num = rand() % 33;

        $tmp = substr($chars, $num, 1);

        $pass = $pass . $tmp;

        $i++;
    }
    return $pass;
}
?>

<!DOCTYPE html>
<html>

<head>

    <script language="javascript">
        function Clickheretoprint() {
            var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
            disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
            var content_vlue = document.getElementById("content").innerHTML;

            var docprint = window.open("", "", disp_setting);
            docprint.document.open();
            docprint.document.write('</head><body onLoad="self.print()" style="width: 800px; font-size: 13px; font-family: arial;">');
            docprint.document.write(content_vlue);
            docprint.document.close();
            docprint.focus();
        }
    </script>

</head>
<!-- mobile navigation -->
<nav id="mobile_navigation"></nav>

<section id="breadcrumbs">
    <div class="container">
        <ul>
            <li><a href="dashboard.php">WebMEDIC</a></li>
            <?php if (!isset($_GET['v'])) { ?>
                <li><span><a href="<?php echo "pos_main2.php?hos_no=$hos_no&insur"; ?>">Back to Billing</a></span></li>
            <?php } ?>
            <li><span>HMO CLAIMS</span></li>
        </ul>
    </div>
</section>
<section class="container clearfix main_section">
    <div id="main_content_outer" class="clearfix">
        <div id="main_content">
            <div class="alert alert-info"><strong>FEE FOR SERVICE CLAIMS</strong></div>

            <!-- main content -->
            <form action="" method="POST" id="hmo_no" name="">
            </form>
            <div class="row">
                <div class="col-sm-12" id="content">
                    <!--<div class="panel-heading">
                <h4 class="panel-title">Simple Validation</h4>
        </div>-->

                    <div style="font:bold 18px 'Arial'; width:200px; position: absolute;right: 0px; top: 0px; color:#C00"><?php
                                                                                                                            echo 'MEDICAL INVOICE' . '<br>' . $finalcode = 'INV' . createRandomPassword(); ?></div>
                    <div align="center">
                        <div style="font:bold 18px 'Arial';"><?php echo $row_rstSelect_d['insurance_name']; ?></div>

                        <?php echo $row_rstSelect_d['addr']; ?> <br><br>
                        <div style="font:bold 14px 'Arial';"><?php echo 'FEE FOR SERVICE CLAIMS FORM'; ?></div>
                        <br></br>
                    </div>
                    <div align="left" style="font:bold 14px 'Arial';">
                    </div>
                    <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                        <tr>
                            <td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>
                            <td width="50%" align="right"><img src="<?php if (file_exists(enrollee_p . $hos_no . '.' . 'jpg')) {
                                                                        echo enrollee_p . $hos_no . '.' . 'jpg';
                                                                    } else {
                                                                        echo 'img/user_avatar_lg.png';
                                                                    } ?>" alt="" height="100" width="100" class="img-thumbnail user_avatar"></td>
                        </tr>

                    </table>
                    <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                        <tr bgcolor="#FFCC66" style="font-weight:100">
                            <td style="font:bold 14px 'Arial';" width="30%">Patient No: </td>
                            <td style="font:bold 14px 'Arial';" width="30%">Insurance Coverage</td>
                            <td style="font:bold 14px 'Arial';" width="30%">Patient Name: </td>
                        </tr>
                        <tr>
                            <td><?php echo '<i><strong>Hospital Number:</strong></i>' . ' ' . $row_rstSelect_d['hospital_no']; ?></td>
                            <td><?php echo $row_rstSelect_d['nhis_no'] . ' (' . $row_rstSelect_d['insurance_type'] . ')'; ?></td>
                            <td><?php echo $row_rstSelect_d['surname'] . ', ' . $row_rstSelect_d['fname'] . ' ' . $row_rstSelect_d['oname']; ?></td>
                        </tr>
                        <tr bgcolor="#FFCC66">
                            <td style="font:bold 14px 'Arial';">Age: </td>
                            <td style="font:bold 14px 'Arial';">Sex: </td>
                            <td style="font:bold 14px 'Arial';">Last Presentation Date: </td>
                        </tr>
                        <tr>
                            <td><?php echo $row_rstSelect_d['age'] . 'year(s)'; ?></td>
                            <td><?php echo ucfirst($row_rstSelect_d['gender']); ?></td>
                            <td><?php
                                if ($date_ap != '') {
                                    echo date('d M, Y', strtotime($date_ap)) . ' - ' . date('h:i:s a', strtotime($ap_time));
                                }

                                ?></td>
                        </tr>

                        <tr bgcolor="#FFCC66">
                            <td style="font:bold 14px 'Arial';">OPD/In-patient No: </td>
                            <td style="font:bold 14px 'Arial';">Date of Addmission/Discharge: </td>
                            <td style="font:bold 14px 'Arial';">Referral Code/Authorization Code: </td>
                        </tr>
                        <tr>
                            <td><?php echo 'Hospital Appointment No: ' . $app_no;

                                $query3 = "SELECT date_admit, date_discharge FROM admission WHERE app_no = :app_no";
                                $stmt3 = $db->prepare($query3);
                                $stmt3->execute([':app_no' => $app_no]);

                                if ($stmt3->rowCount() > 0) {
                                    $row_rst_adm = $stmt3->fetch();
                                    $d = date('d M, Y', strtotime($row_rst_adm['date_admit'])) . ' / ';
                                    $d2 = $row_rst_adm['date_discharge'] ? date('d M, Y', strtotime($row_rst_adm['date_discharge'])) : '';
                                } else {
                                    $d = '-';
                                    $d2 = '';
                                }

                                echo $d . $d2; ?>
                            </td>
                            <td><?php echo $d . $d2; ?></td>
                            <td><?php
                                /// authorization code
                                if ($ap_type == '2' and $auth_code == '0000') {
                                    echo 'Enter Authorization Code';
                                    $auth_code_status = 1;
                                } elseif ($ap_type == '1') {
                                    echo '<i><strong>Access Type:</strong></i> Primary Care Service';
                                } else {
                                    echo $auth_code . ' - ' . 'Secondary Care Service';
                                } ?>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </table>


                    <?php
                    if (isset($_GET['v'])) {
                        $v = sanitize($_GET["v"]);
                        $part = explode("/", $v);
                        $from = $part['0'];
                        $to = $part['1'];
                        $process_claim_no = $part['2'];
                        include_once("claims_print_p2.php");
                    } else {
                        include_once("claims_print_p1.php");
                    } ?>



                </div>
            </div>
        </div>
    </div>



    <div class="form_sep" align="right">
        <div class="pull-right" style="margin-right:100px;">
            <a href="javascript:Clickheretoprint()" style="font-size:20px;"><button class="btn btn-success btn-large"><i class="icon-print"></i> Print</button></a>
        </div>
    </div>


</section>
<div id="footer_space"></div>
</div>

</html>

<?php
if ($rights == 'DR' or $rights == 'NS') {
    include('inc/footer_doc.php');
} else {
    include('inc/footer.php');
}

?>