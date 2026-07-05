<?php if (isset($_POST['delete_confirm_payslip'])) {

    $stmt = $db->prepare("DELETE FROM hrpayslip WHERE date_range=:date_range AND ECode=:ECode");
    $stmt->bindParam(':date_range', $_POST["date_range"]);
    $stmt->bindParam(':ECode', $_POST["Ecode"]);
    $stmt->execute();
} ?>


<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>View Report of Earnings, Deductions, and Salaries Paid</h5>
            </div>


            <div class="ibox-content">
                <form method="POST" action="index.php?srp">
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form_sep">
                                <label for="reg_select" class="">Show Earnings Only</label>
                                <select name="Earnings" class="form-control">
                                    <option selected="selected" value="">... select ...</option>
                                    <option value="all">All Earnings</option>
                                    <?php
                                    $stmt = $db->query("SELECT distinct descriptn FROM hrpayslip WHERE pay_head='E' ORDER BY descriptn ASC");
                                    while ($row2 = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <option value="<?php echo $row2['descriptn']; ?>"><?php echo $row2['descriptn']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form_sep">
                                <label for="reg_select" class="">Show Deductions Only</label>
                                <select name="Deductions" class="form-control">
                                    <option selected="selected" value="">... select ...</option>
                                    <option value="all">All Deductions</option>
                                    <?php
                                    $stmt = $db->query("SELECT distinct descriptn FROM hrpayslip WHERE pay_head='D' ORDER BY descriptn ASC");
                                    while ($row2 = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <option value="<?php echo $row2['descriptn']; ?>"><?php echo $row2['descriptn']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>



                        </div>

                        <div class="col-sm-3">

                            <div class="form_sep">
                                <label for="reg_select" class="">Show Staff</label>
                                <select name="staff_pay" class="form-control">
                                    <option selected="selected" value="">... select ...</option>
                                    <option value="with">All Staff With Generated Payslip</option>
                                    <option value="without">All Staff Without Payslip (Pending)</option>
                                </select>
                            </div>
                            <div class="form_sep">
                                <label> <input type="hidden" value="month_year_only" name="dates" class="radio i-checks"> <i></i></label>
                                <div class="form_sep"></div>
                                <label> <input type="hidden" value="year_only" name="dates" class="radio i-checks"> <i></i></label>
                            </div>

                        </div>

                        <div class="col-sm-3">

                            <div class="form_sep">
                                <label for="reg_select" class="">Month</label>
                                <select name="month" id="month" class="form-control">
                                    <option selected="selected" value="">Select...</option>
                                    <option value="January">January</option>
                                    <option value="February">February</option>
                                    <option value="March">March</option>
                                    <option value="April">April</option>
                                    <option value="May">May</option>
                                    <option value="June">June</option>
                                    <option value="July">July</option>
                                    <option value="August">August</option>
                                    <option value="September">September</option>
                                    <option value="October">October</option>
                                    <option value="November">November</option>
                                    <option value="December">December</option>
                                </select>
                            </div>

                            <div class="form_sep">
                                <label for="reg_select" class="">Year</label>
                                <input type="text" id="year" name="year" class="form-control" value="<?php echo date("Y"); ?>" maxlength="4" required>
                            </div>

                        </div>



                        <div class="col-sm-3">
                            <br>
                            <div class="form_sep">
                                <button class="btn btn-info btn btn-sm" type="submit" name="apply_submit" id="apply_submit">Apply </button>

                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                                <a href="index.php?srp" class="btn btn-default btn btn-sm"> Refresh</a>
                            </div>
                            <div class="form_sep"></div>

                        </div>

                    </div>



                </form>
            </div>

        </div>
    </div>




    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Report Panel </h5>
            </div>

            <div class="ibox-content">

                <?php if (isset($_POST['apply_submit']) or isset($_POST['delete_confirm_payslip'])) {

                    if (isset($_POST['year']) and $_POST['year'] != '') {
                        $year = $_POST['year'];

                        if (isset($_POST['month']) and $_POST['month'] != '') {
                            $month = $_POST['month'];
                        }


                        if ((isset($_POST['Earnings']) and $_POST['Earnings'] != '') or (isset($_POST['Deductions']) and $_POST['Earnings'] != '')) {

                            if (isset($_POST['Earnings']) and $_POST['Earnings'] != '') {

                                ////// EARNINGS				 
                                $Earnings = $_POST['Earnings'];
                                if ($month != "") {
                                    // m/y
                                    if ($Earnings == 'all') {
                                        $search_part = "pay_head='E' and month='$month' and year='$year'";
                                        $search_title = "Showing Records:  " . '<strong>All Earnings</strong> ' . '/ ' . $month . ', ' . $year;
                                    } else {
                                        $search_part = "pay_head='E' and descriptn='$Earnings' and month='$month' and year='$year'";
                                        $search_title = "Showing Records:  " . '<strong>' . $Earnings . '</strong> ' . '/ ' . $month . ', ' . $year;
                                    }
                                } else {
                                    //y only

                                    if ($Earnings == 'all') {
                                        $search_part = "pay_head='E' and year='$year'";
                                        $search_title = "Showing Records:  " . '<strong>All Earnings</strong> ' . '/ ' . $year;
                                    } else {
                                        $search_part = "pay_head='E' and descriptn='$Earnings' and year='$year'";
                                        $search_title = "Showing Records:  " . '<strong>' . $Earnings . '</strong> ' . '/ ' . $year;
                                    }
                                }
                            } elseif (isset($_POST['Deductions']) and $_POST['Deductions'] != '') {

                                //////////// DEDUCTIONS				 
                                $Deductions = $_POST['Deductions'];
                                if ($month != "") {
                                    // m/y

                                    if ($Earnings == 'all') {
                                        $search_part = "pay_head='D' and month='$month' and year='$year'";
                                        $search_title = "Showing Records:  " . '<strong>All Deductions</strong> ' . '/ ' . $month . ', ' . $year;
                                    } else {
                                        $search_part = "pay_head='D' and descriptn='$Deductions' and month='$month' and year='$year'";
                                        $search_title = "Showing Records:  " . '<strong>' . $Deductions . '</strong> ' . '/ ' . $month . ', ' . $year;
                                    }
                                } else {
                                    //y only
                                    if ($Deductions == 'all') {
                                        $search_part = "pay_head='D' and year='$year'";
                                        $search_title = "Showing Records:  " . '<strong>All Deductions</strong> ' . '/ ' . $year;
                                    } else {
                                        $search_part = "pay_head='D' and descriptn='$Deductions' and year='$year'";
                                        $search_title = "Showing Records:  " . '<strong>' . $Deductions . '</strong> ' . '/ ' . $year;
                                    }
                                }
                            }


                            $stmt = $db->query("SELECT * FROM hrpayslip WHERE $search_part ORDER BY sn ASC");
                            if ($stmt->rowCount() > 0) { ?>

                                <h4><i><?php echo $search_title; ?></i></h4>
                                <hr>

                                <table class="table table-striped table-bordered table-hover dataTables-example">
                                    <thead>
                                        <tr>

                                            <th>Staff No</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                            <th>Month/Year</th>
                                            <th>Date Range</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php $totalAmount = 0;
                                        while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $totalAmount = $totalAmount + $rwx['amount'];
                                        ?>
                                            <tr>
                                                <td><?php echo $rwx['ECode']; ?></td>
                                                <td><?php echo $rwx['descriptn']; ?></td>
                                                <td><?php echo $rwx['amount']; ?></td>
                                                <td><?php echo $rwx['month'] . ', ' . $rwx['year']; ?></td>
                                                <td><?php echo $rwx['date_range']; ?></td>
                                                <td><?php echo $rwx['generate_date']; ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>

                                <div align="left" style=" font-size:14px;"><strong>Total: </strong> <?php echo number_format($totalAmount); ?></div>


                            <?php } else {
                                echo '<strong>No Records to Show ... </strong>';
                            }

                            /////// STAFF PAYSLIPS									  

                        } elseif (isset($_POST['staff_pay']) and $_POST['staff_pay'] != '') {
                            $staff_pay = $_POST['staff_pay'];

                            if ($month != "") {
                                if ($staff_pay == 'with') {
                                    $search_title = "Showing Records:  " . '<strong>Payslip Paid </strong> ' . '/ ' . $month . ', ' . $year;
                                } else {
                                    $search_title = "Showing Records:  " . '<strong>Employees without payment slip</strong> ' . '/ ' . $month . ', ' . $year;
                                }
                            ?>

                                <div id="content">
                                    <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                                        <tr>
                                            <td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>
                                            <td width="50%" align="right">
                                                <div style="font-size:18px; font:Verdana, Geneva, sans-serif""><strong>Rayfield Medical Services</strong></div> <br><div style=" font-size:14px">30, Raphael Davou Street,Fwavwei Rayfield,<br>Jos Plateau State, Nigeria. <br><br> 234 7038060560, 234 7044445504<< /div>
                                            </td>
                                        </tr>
                                    </table>
                                    <hr>

                                    <h4><i><?php echo $search_title; ?></i></h4>
                                    <hr>


                                    <table border="0" style="font-family: arial; font-size: 13px;text-align:left;width:100%; padding:10px; ">

                                        <thead>
                                            <tr>
                                                <th style="border-bottom: 1px solid #ddd; padding:5px;">No</th>
                                                <th style="border-bottom: 1px solid #ddd; padding:5px;">Staff No</th>
                                                <th style="border-bottom: 1px solid #ddd; padding:5px;">Name</th>
                                                <th style="border-bottom: 1px solid #ddd; padding:5px;">B/Salary</th>
                                                <th style="border-bottom: 1px solid #ddd; padding:5px;">Earning</th>
                                                <th style="border-bottom: 1px solid #ddd; padding:5px;">Deduction</th>
                                                <th style="border-bottom: 1px solid #ddd; padding:5px;">Net Pay</th>
                                                <th style="border-bottom: 1px solid #ddd; padding:5px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <?php

                                            //// month and year
                                            $n = 1;
                                            $Gtotal_earning = 0;
                                            $Gtotal_deduct = 0;
                                            $Gbasic_sal = 0;

                                            $stmt = $db->query("SELECT EmployeeCode,FirstName,MiddleName,LastName FROM hremp where status='0' ORDER BY sn ASC");
                                            if ($stmt->rowCount() > 0) {
                                                while ($rwxx = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                    $ECode = $rwxx['EmployeeCode'];
                                                    $fullname = $rwxx['FirstName'] . ', ' . $rwxx['LastName'];

                                                    /////////////// computer
                                                    $stmtt = $db->query("SELECT * FROM hrpayslip WHERE ECode='$ECode' and month='$month' and year='$year' order by sn");
                                                    if ($stmtt->rowCount() > 0) {

                                                        $total_earning = 0;
                                                        $total_deduct = 0;
                                                        $basic_sal = 0;
                                                        while ($rww = $stmtt->fetch(PDO::FETCH_ASSOC)) {

                                                            if ($rww['pay_head'] == 'E') {
                                                                $total_earning = $total_earning + $rww['amount'];
                                                                $Gtotal_earning = $Gtotal_earning + $rww['amount'];
                                                            } elseif ($rww['pay_head'] == 'D') {
                                                                $total_deduct = $total_deduct + $rww['amount'];
                                                                $Gtotal_deduct = $Gtotal_deduct + $rww['amount'];
                                                            } elseif ($rww['pay_head'] == 'B') {
                                                                $basic_sal = $basic_sal + $rww['amount'];
                                                                $Gbasic_sal = $Gbasic_sal + $rww['amount'];
                                                            }
                                                            $date_range = $rww['date_range'];
                                                            $days = $rww['days'];
                                                            $month = $rww['month'];
                                                            $year = $rww['year'];
                                                        } ?>

                                                        <?php if ($staff_pay == 'with') {
                                                            $view = 'srp'; ?>
                                                            <tr>
                                                                <td style="border-bottom: 1px solid #ddd; padding:5px;"><?php echo $n; ?></td>
                                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $ECode; ?></td>
                                                                <td style="border-bottom: 1px solid #ddd; padding:5px;"><?php echo $fullname; ?></td>
                                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $basic_sal; ?></td>
                                                                <td style="border-bottom: 1px solid #ddd; padding:5px;"><?php echo $total_earning; ?></td>
                                                                <td style="border-bottom: 1px solid #ddd;"><?php echo $total_deduct; ?></td>
                                                                <td style="border-bottom: 1px solid #ddd; padding:5px;">
                                                                    <?php $net = ($basic_sal + $total_earning) - $total_deduct;
                                                                    echo $net; ?></td>
                                                                <td style="border-bottom: 1px solid #ddd;">

                                                                    <a data-toggle="modal" data-target="#myModal5" class="confirm_payslip_delete" id="<?php echo $ECode . '/' . $date_range . '/' . $month . '/' . $year . '/' . $staff_pay . '/' . $view; ?>">Delete</a> &nbsp; | &nbsp;
                                                                    <a href="index.php?Ecode=<?php echo "$ECode/$month/$year/$date_range&lip"; ?>" target="_blank">Send Mail</a>
                                                                </td>
                                                            </tr>
                                                        <?php $n++;
                                                        } ?>

                                                    <?php } else { ?>

                                                        <?php if ($staff_pay == 'without') { ?>
                                                            <tr>
                                                                <td style="border-bottom: 1px solid #ddd; padding:5px;"><?php echo $n; ?></td>
                                                                <td style="border-bottom: 1px solid #ddd; padding:5px;"><?php echo $ECode; ?></td>
                                                                <td style="border-bottom: 1px solid #ddd; padding:5px;"><?php echo $fullname; ?></td>
                                                                <td style="border-bottom: 1px solid #ddd; padding:5px;"><?php echo '-'; ?></td>
                                                                <td style="border-bottom: 1px solid #ddd; padding:5px;"><?php echo '-'; ?></td>
                                                                <td style="border-bottom: 1px solid #ddd; padding:5px;"><?php echo '-'; ?></td>
                                                                <td style="border-bottom: 1px solid #ddd; padding:5px;"><?php echo '-'; ?></td>
                                                            </tr>
                                                        <?php $n++;
                                                        } ?>

                                            <?php }
                                                    ///// looping
                                                }
                                            } ?>

                                        </tbody>
                                    </table>
                                    <HR>
                                    <table width="100%">
                                        <tr>
                                            <td>
                                                <strong style="font-size:16px;">GRAND TOTAL</strong>
                                            </td>
                                            <td>
                                                <strong style="font-size:16px;">Basic Salary: <?php echo number_format($Gbasic_sal); ?></strong>
                                            </td>
                                            <td>
                                                <strong style="font-size:16px;">Earnings: <?php echo number_format($Gtotal_earning); ?></strong>
                                            </td>
                                            <td>
                                                <strong style="font-size:16px;">Deductions: <?php echo number_format($Gtotal_deduct); ?></strong>
                                            </td>
                                            <td>
                                                <strong style="font-size:16px;">NET PAY: <?php

                                                                                            $net = ($Gbasic_sal + $Gtotal_earning) - $Gtotal_deduct;
                                                                                            echo number_format($net); ?></strong>
                                            </td>
                                        </tr>
                                    </table>

                                    <div>
                                        <br>
                                        <a href="javascript:Clickheretoprint()" target="_blank" class="btn btn-primary btn-xs"><i class="fa fa-print"></i> Print Report </a>

                                    <?php    } else {

                                    ///// year 	

                                    if ($staff_pay == 'with') {
                                        $search_title = "Showing Records:  " . '<strong>Payslip Paid </strong> ' . $year;
                                    } else {
                                        $search_title = "Showing Records:  " . '<strong>Employees without payment slip</strong> ' . '/ ' . $year;
                                    }
                                    ?>

                                        <h4><i><?php echo $search_title; ?></i></h4>
                                        <hr>

                                        <?php

                                        for ($x = 1; $x <= 12; $x++) {
                                            $months = array(1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December');
                                            $month = $months[(int)$x];
                                            $Gtotal_earning = 0;
                                            $Gtotal_deduct = 0;
                                            $Gbasic_sal = 0;
                                        ?>

                                            <?php
                                            if ($staff_pay == 'with') {
                                                $stmtt = $db->query("SELECT * FROM hrpayslip WHERE month='$month' and year='$year' order by sn");
                                                if ($stmtt->rowCount() > 0) { ?>
                                                    <h4><?php echo $month; ?></h4>
                                                    <table class="table table-striped table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>No</th>
                                                                <th>Staff No</th>
                                                                <th>Name</th>
                                                                <th>B/Salary</th>
                                                                <th>Earning</th>
                                                                <th>Deduction</th>
                                                                <th>Net Pay</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php

                                                    }
                                                }


                                                if ($staff_pay == 'without') { ?>
                                                        <h4><?php echo $month; ?></h4>
                                                        <table class="table table-striped table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>No</th>
                                                                    <th>Staff No</th>
                                                                    <th>Name</th>
                                                                    <th>B/Salary</th>
                                                                    <th>Earning</th>
                                                                    <th>Deduction</th>
                                                                    <th>Net Pay</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php }

                                                            //// month and year
                                                            $n = 1;
                                                            $stmt = $db->query("SELECT EmployeeCode,FirstName,MiddleName,LastName FROM hremp where status='0' ORDER BY sn ASC");
                                                            if ($stmt->rowCount() > 0) {
                                                                while ($rwxx = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                                    $ECode = $rwxx['EmployeeCode'];
                                                                    $fullname = $rwxx['FirstName'] . ', ' . $rwxx['LastName'];

                                                                    /////////////// computer
                                                                    $stmtt = $db->query("SELECT * FROM hrpayslip WHERE ECode='$ECode' and month='$month' and year='$year' order by sn");
                                                                    if ($stmtt->rowCount() > 0) {

                                                                        $total_earning = 0;
                                                                        $total_deduct = 0;
                                                                        $basic_sal = 0;
                                                                        while ($rww = $stmtt->fetch(PDO::FETCH_ASSOC)) {
                                                                            if ($rww['pay_head'] == 'E') {
                                                                                $total_earning = $total_earning + $rww['amount'];
                                                                                $Gtotal_earning = $Gtotal_earning + $rww['amount'];
                                                                            } elseif ($rww['pay_head'] == 'D') {
                                                                                $total_deduct = $total_deduct + $rww['amount'];
                                                                                $Gtotal_deduct = $Gtotal_deduct + $rww['amount'];
                                                                            } elseif ($rww['pay_head'] == 'B') {
                                                                                $basic_sal = $basic_sal + $rww['amount'];
                                                                                $Gbasic_sal = $Gbasic_sal + $rww['amount'];
                                                                            }
                                                                        } ?>

                                                                        <?php if ($staff_pay == 'with') { ?>
                                                                            <tr>
                                                                                <td><?php echo $n; ?></td>
                                                                                <td><?php echo $ECode; ?></td>
                                                                                <td><?php echo $fullname; ?></td>
                                                                                <td><?php echo $basic_sal; ?></td>
                                                                                <td><?php echo $total_earning; ?></td>
                                                                                <td><?php echo $total_deduct; ?></td>
                                                                                <td><?php $net = ($basic_sal + $total_earning) - $total_deduct;
                                                                                    echo $net; ?></td>
                                                                            </tr>
                                                                        <?php $n++;
                                                                        } ?>

                                                                    <?php } else { ?>

                                                                        <?php if ($staff_pay == 'without') { ?>
                                                                            <tr>
                                                                                <td><?php echo $ECode; ?></td>
                                                                                <td><?php echo $fullname; ?></td>
                                                                                <td><?php echo '-'; ?></td>
                                                                                <td><?php echo '-'; ?></td>
                                                                                <td><?php echo '-'; ?></td>
                                                                                <td><?php echo '-'; ?></td>
                                                                            </tr>
                                                                        <?php } ?>

                                                            <?php }
                                                                }  //// EMPLOYEE LOOPING
                                                            } ?>
                                                            </tbody>
                                                        </table>

                                                        <?php if ($Gbasic_sal > 0) { ?>
                                                            <HR>
                                                            <table width="100%">
                                                                <tr>
                                                                    <td>
                                                                        <strong style="font-size:16px;">TOTAL</strong>
                                                                    </td>
                                                                    <td>
                                                                        <strong style="font-size:16px;">Basic Salary: <?php echo number_format($Gbasic_sal); ?></strong>
                                                                    </td>
                                                                    <td>
                                                                        <strong style="font-size:16px;">Earnings: <?php echo number_format($Gtotal_earning); ?></strong>
                                                                    </td>
                                                                    <td>
                                                                        <strong style="font-size:16px;">Deductions: <?php echo number_format($Gtotal_deduct); ?></strong>
                                                                    </td>
                                                                    <td>
                                                                        <strong style="font-size:16px;">NET PAY: <?php

                                                                                                                    $net = ($Gbasic_sal + $Gtotal_earning) - $Gtotal_deduct;
                                                                                                                    echo number_format($net); ?></strong>
                                                                    </td>
                                                                </tr>
                                                            </table><br>
                                                        <?php } ?>

                                                    <?php }    /// FOR NEXT MONTHS 

                                                    ?>
                                            <?php

                                        }
                                    }

                                            ?>




                                    <?php


                                } else {
                                    echo '<strong>Enter year before you continue ... </strong>';
                                }
                            } ?>


                                    </div>
                                </div>
            </div>


        </div>

    </div>
    <div class="modal inmodal fade" id="confirm_del_payslip_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="">Confirm Delete Payslip</h4>
                </div>
                <div class="modal-body" id="confirm_del_payslip_body">
                </div>

            </div>
        </div>
    </div>

    <script>
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