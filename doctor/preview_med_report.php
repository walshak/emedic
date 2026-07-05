<?php session_start();
include("../Connections/Conn.php");
include('../doctor/objects.php');
include('../doctor/helpers.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Report Preview</title>

    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        #page {
            width: 100%;
            margin: 20px auto;
            padding: 20px;
        }

        #heading {
            width: 250px;
            padding: 5px;
            margin: 1px auto;
            color: #fff;
            text-align: center;
            font-weight: bolder;
            background: #419666;
            margin-bottom: 10px;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;

            }

            td {}

            img {
                width: 50px;
            }

            h4 {
                font-size: 14px;
            }

            .sign-area {
                font-size: 10px;
            }
        }
    </style>
</head>

<body>


    <div class="card panel" id="page">
        <?php
        $token = base64_decode(filter_var($_GET['token'], FILTER_SANITIZE_STRING));
        $token_components = preg_split('/[-]/', $token);

        if (count($token_components) == 2) {
            $id = $token_components[1];

            if (isset($_POST['mark-as-printed'])) {
                if ($_POST['token'] == $id) {
                    $med_report_sent_stmt = $db->prepare("UPDATE medical_report_task SET action = 'Printed' WHERE id = ? ");
                    $save = $med_report_sent_stmt->execute(array($id));

                    if ($save) {
                        echo '<div class="alert alert-success"><b>Success: </b> Medical Report marked as printed.</div>';
                    }
                }
            }

            $med_report_sent_stmt = $db->prepare("SELECT * FROM medical_report_task WHERE id = ? ");
            $med_report_sent_stmt->execute(array($id));
            if ($med_report_sent_stmt->rowCount() > 0) {
                $med_report = $med_report_sent_stmt->fetch(PDO::FETCH_ASSOC);
                $id = $med_report['id'];
            }
        } else {
            exit;
        }

        ?>
        <div class="light-card">
            <div class="text-right" id="button-area">
                <button class="btn btn-sm btn-primary" onclick="print_page()"><i class="fa fa-print"></i> Print</button>
                <!-- <button class="btn btn-sm btn-primary" onclick="ClickheretoprintDiv('printable_area')"><i class="fa fa-print"></i> Print</button> -->
                <?php
                if ($med_report['action'] != 'Printed') {
                ?>
                    <form style="display:inline" action="preview_med_report.php?token=<?php echo $_GET['token']; ?>" method="post" onsubmit="return confirm('Please confirm to mark as completed')"><input type="hidden" name="token" value="<?php echo $id; ?>"> <button class="btn btn-sm btn-success" name="mark-as-printed"><i class="fa fa-check"></i> Mark as printed</button></form>
                <?php
                }
                ?>
            </div>
            <br>

            <div id="printable_area" style="border:2px solid #000;padding: 20px">
                <div class="row">
                    <div class="col-xl-12">
                    <?php
                            $hospital_info = $Hospital->get([]);
                            if (!empty($hospital_info[0])) {
                                echo ' 
                         <p class="text-center"> <img src="../img/logo.png" width="100px"> </p>
                        <h4 class="text-center"> ' . $hospital_info[0]->name . ' <br> ' . $hospital_info[0]->address . ' </h4> 
                        <div id="heading">'.$med_report["report_type"].'</div>
                       ';
                            }

                            ?>
                    </div>
                    <div class="col-xl-12">
                             <div style="padding: 10px;">
                                <!-- <h3><?php echo $notes = $med_report['report_type']; ?></h3> -->
                                <?php echo $notes = $med_report['notes']; ?>
                            </div>
                    </div>
                   
                   
                    
                </div>
                <div class="row" style="border:2px solid #000">
                <div class="col-lg-6">
                    <div style="padding: 10px; ">
                        <div class="pull-right">Sign & Date: _______________________________</div>
                        <b>Compiled By:</b> <br>

                        <?php

                        $user = $AdminUser->find($_SESSION['id']);
                        echo $user->fullname;

                        ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                    <div style="padding: 10px;">

                            <div class="pull-right">Sign & Date: _______________________________</div>
                            <b>Doctor:</b> <br>

                            <?php
                            echo $med_report['created_by_name'];
                            echo '<br>';
                            $user = $AdminUser->find($med_report['created_by']);
                         
                            echo isset($user->specialist) ? $user->specialist : $_SESSION['specialist'];

                            ?>
                            </div>
                    </div>
                </div>
            

            </div>
        </div>
    </div>
    <script>
        // window.print();

        function print_page() {
            document.getElementById('button-area').style.display = 'none'
            window.print();
            document.getElementById('button-area').style.display = 'block'

        }

        function ClickheretoprintDiv(div_id) {
            var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
            disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
            var content_vlue = document.getElementById(div_id).innerHTML;

            var docprint = window.open("", "", disp_setting);
            docprint.document.write('<html><head><title>.::Webmedic </title> <link rel="stylesheet" href="../css/bootstrap.min.css">');
            docprint.document.write('</head><body onLoad="self.print()" style="width: 100%; height="auto" font-size:16px; font-family:arial;">');
            docprint.document.write(content_vlue);
            docprint.document.write('</body></html>');
            docprint.document.close();
            docprint.focus();
        }
    </script>

</body>

</html>