<?php

$setdate = date('Y-m-d H:i:s');

if (isset($_GET['dl'])) {
    $sn = $_GET['dl'];

    $stmt = $db->prepare('DELETE FROM hred_details WHERE sn = :sn');
    $stmt->execute([':sn' => $sn]);

    $stmt = $db->prepare('DELETE FROM hred_income_services WHERE id = :id');
    $stmt->execute([':id' => $sn]);
}

if (isset($_GET['de'])) {
    $sn = $_GET['de'];
    $stmt = $db->prepare('DELETE FROM hred_details_sal WHERE sn = :sn');
    $stmt->execute([':sn' => $sn]);
}

if (isset($_POST['save_income_earn'])) {
    $service_type_earn = $_POST['service_type_earn'];
    $ecode = trim($_POST['Ecode']);

    if ($_POST['duration'] == '0') {
        $bal = 'always';
        $duration = '0';
    } elseif ($_POST['duration'] > 0) {
        $bal = $_POST['duration'];
        $duration = $_POST['duration'];
    } else {
        $bal = '1';
        $duration = '1';
    }

    $stmt = $db->prepare('SELECT * FROM hred_details WHERE employee_no = :ecode');
    $stmt->execute([':ecode' => $ecode]);
    $avail = $stmt->rowCount() > 0 ? 1 : 0;

    $stmt = $db->prepare("SELECT * FROM hred_details WHERE employee_no = :ecode AND service_type_earn = 'All Hospital Services'");
    $stmt->execute([':ecode' => $ecode]);

    if ($stmt->rowCount() == 0 && $avail == 0 && $service_type_earn == 'All Hospital Services') {
        $sub = sv_income($service_type_earn, $ecode, $duration, $bal);
    } elseif ($stmt->rowCount() == 0 && $avail == 1 && $service_type_earn == 'All Hospital Services') {
        $err_title = "Delete existing setting before you can add $service_type_earn Option";
    } elseif ($stmt->rowCount() == 0 && $service_type_earn != 'All Hospital Services') {
        $sub = sv_income($service_type_earn, $ecode, $duration, $bal);
    } else {
        $err_title = "Delete existing setting before you can add $service_type_earn Option";
    }
}

function sv_income($service_type_earn, $ecode, $duration, $bal)
{
    global $db; // Assuming $db is available as a global variable

    $setdate = date('Y-m-d H:i:s');
    $stmt = $db->prepare('SELECT * FROM hred_details WHERE employee_no = :ecode AND service_type_earn = :service_type_earn');
    $stmt->execute([':ecode' => $ecode, ':service_type_earn' => $service_type_earn]);

    if ($stmt->rowCount() == 0) {
        $stmt = $db->prepare('INSERT INTO hred_details (employee_no, service_type_earn, flat_percent, flat_percent_value, duration, bal, setby, set_date)
                              VALUES (:employee_no, :service_type_earn, :flat_percent, :flat_percent_value, :duration, :bal, :setby, :set_date)');
        $stmt->execute([
            ':employee_no' => $_POST['Ecode'],
            ':service_type_earn' => $_POST['service_type_earn'],
            ':flat_percent' => $_POST['flat_percent'],
            ':flat_percent_value' => $_POST['flat_percent_amt'],
            ':duration' => $duration,
            ':bal' => $bal,
            ':setby' => $_SESSION['fullname'],
            ':set_date' => $setdate,
        ]);
        $sv = 1;
    }
}

if (isset($_POST['basic_salary'])) {
    $ecode = $_POST['Ecode'];

    $stmt = $db->prepare("SELECT * FROM hred_details_sal WHERE employee_no = :ecode AND EorD = 'B'");
    $stmt->execute([':ecode' => $ecode]);

    if ($stmt->rowCount() == 0) {
        $stmt = $db->prepare("INSERT INTO hred_details_sal (employee_no, descriptn, EorD, flat_percent, flat_percent_value, duration, bal, setby, set_date)
                              VALUES (:employee_no, 'Basic Salary', 'B', '0', :flat_percent_value, '0', '0', :setby, :set_date)");
        $stmt->execute([
            ':employee_no' => $ecode,
            ':flat_percent_value' => $_POST['basic_sal'],
            ':setby' => $_SESSION['fullname'],
            ':set_date' => $setdate,
        ]);
        $sv = 1;
    } else {
        $stmt = $db->prepare("UPDATE hred_details_sal SET flat_percent_value = :flat_percent_value, setby = :setby, set_date = :set_date
                              WHERE employee_no = :employee_no AND descriptn = 'Basic Salary'");
        $stmt->execute([
            ':flat_percent_value' => $_POST['basic_sal'],
            ':setby' => $_SESSION['fullname'],
            ':set_date' => $setdate,
            ':employee_no' => $ecode,
        ]);
        $sv = 1;
    }
}

if (isset($_POST['gross_salary'])) {
    $ecode = $_POST['Ecode'];

    $stmt = $db->prepare("SELECT * FROM hred_details_sal WHERE employee_no = :ecode AND EorD = 'B'");
    $stmt->execute([':ecode' => $ecode]);

    if ($stmt->rowCount() == 0) {
        // do nothing, base sal is not set yet.
        // TODO: throw a proper error here
        $sv = 1;
    } else {
        // save the gross sal to the basic salary row of the staff
        $stmt = $db->prepare("UPDATE hred_details_sal SET flat_percent_value_gross = :flat_percent_value_gross
                              WHERE employee_no = :employee_no AND descriptn = 'Basic Salary'");
        $stmt->execute([
            ':flat_percent_value_gross' => $_POST['gross_sal'],
            ':employee_no' => $ecode,
        ]);
        $sv = 1;
    }
}

if (isset($_POST['save_earnings']) || isset($_POST['save_deductions'])) {
    $ecode = $_POST['Ecode'];
    $EorD = isset($_POST['save_earnings']) ? 'E' : 'D';
    $flat_percent = isset($_POST['flat_percent']) ? $_POST['flat_percent'] : $_POST['flat_percent2'];
    $flat_percent_amt = isset($_POST['flat_percent_amt']) ? $_POST['flat_percent_amt'] : $_POST['flat_percent_amt2'];
    $desc = '';

    if ($EorD === 'E') {
        $select_earn = $_POST['select_earn'];
        $desc = ($select_earn === 'Others' && !empty($_POST['other_earning'])) ? $_POST['other_earning'] : $select_earn;
    } else {
        $select_earn = $_POST['select_deduct'];
        $desc = ($select_earn === 'Others' && !empty($_POST['other_deduct'])) ? $_POST['other_deduct'] : $select_earn;
    }

    if (isset($_POST['duration2'])) {
        $duration = $_POST['duration2'] === '0' ? '0' : $_POST['duration2'];
        $bal = $_POST['duration2'] === '0' ? 'always' : $_POST['duration2'];
    } else {
        $duration = $_POST['duration'] === '0' ? '0' : $_POST['duration'];
        $bal = $_POST['duration'] === '0' ? 'always' : $_POST['duration'];
    }

    $stmt = $db->prepare('SELECT * FROM hred_details_sal WHERE employee_no = :ecode AND descriptn = :desc AND EorD = :EorD');
    $stmt->execute([':ecode' => $ecode, ':desc' => $desc, ':EorD' => $EorD]);

    if ($stmt->rowCount() == 0) {
        $stmt = $db->prepare('INSERT INTO hred_details_sal (employee_no, descriptn, EorD, flat_percent, flat_percent_value, duration, bal, setby, set_date)
                              VALUES (:employee_no, :descriptn, :EorD, :flat_percent, :flat_percent_value, :duration, :bal, :setby, :set_date)');
        $stmt->execute([
            ':employee_no' => $ecode,
            ':descriptn' => $desc,
            ':EorD' => $EorD,
            ':flat_percent' => $flat_percent,
            ':flat_percent_value' => $flat_percent_amt,
            ':duration' => $duration,
            ':bal' => $bal,
            ':setby' => $_SESSION['fullname'],
            ':set_date' => $setdate,
        ]);
        $sv = 1;
    }
}
?>

<?php if (isset($_POST['generate_my_payslip']) or isset($_POST['save_payslip'])) {?>

<div class="row">
<div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title"><h5>Payslip Confirmation</h5></div>
            
                        <div class="ibox-content">
 <div class="row">
<div class="col-lg-8">
                                                       
<?php

        $month = $_POST['month'];
    $year = $_POST['year'];
    $start = $_POST['start'];
    $end = $_POST['end'];
    $sal_type = $_POST['sal_type'];
    $days = $_POST['days'];
    $Ecode = $_POST['Ecode'];
    $date_range = $start.' - '.$end;

    if ($sal_type == 'full') {
        $ts = strtotime("$month $year");
        $days = date('t', $ts);
    } elseif ($days != '') {
        // calculate days worked
        $days = $_POST['days'];
    } else {
        // / part

        date_default_timezone_set('Africa/Lagos');
        $date1 = date_create($start);
        $date2 = date_create($end);
        $diff = date_diff($date1, $date2);
        $days = $diff->format('%a');
    }

    // ////////////// check save mode
    if (isset($_POST['save_payslip'])) {
        $save_mode = 'yes';
        $send_email = $_POST['send_email'];

        // /------------------------------------------------
        if (!empty($_REQUEST['inv'])) {
            $item_list = $_REQUEST['inv'];
            for ($i = 0; $i < count($item_list); ++$i) {
                $values = $item_list[$i];
                $break = explode('__', $values);
                $sn = $break[0];
                $sn = $break[0];
                $descriptn = $break[1];
                $duration = $break[2];
                $bal = $break[3];
                $pay_head = 'E';
                $amount = $break[5];
                $row_sn = $break[6];
                $pay_head_type = 'Earnings_Income';

                $sub = add_payslip($Ecode, $descriptn, $pay_head, $amount, $month, $year, $date_range, $days, $setdatetime, $duration, $bal, $row_sn, $pay_head_type);
            }
        }
    // /----------------------------------------
    } else {
        $save_mode = 'no';
    }
    // ////////////////////// end save mode

    include 'payslip_generate_body.php';

    ?>	
    <br><br>
    
	
   
                                        
    <div class="form_sep">
 	<!--	<label><input type="checkbox" name="send_email"  class="checkbox i-checks" value="yes"> &nbsp; Send Payslip to staff</label>   &nbsp; &nbsp; &nbsp; | &nbsp; &nbsp; &nbsp; -->
<button class="btn btn-info btn btn-sm" type="submit" name="save_payslip" id="save_payslip" >Save Payslip</button>
		&nbsp; &nbsp; &nbsp; | &nbsp; &nbsp; &nbsp; 
<a href="index.php?sal=<?php echo $Ecode; ?>" class="btn btn-warning btn btn-sm" >Cancel</a>
    </div>
        
		<input type="hidden" name="month" value="<?php echo $month; ?>">
        <input type="hidden" name="year" value="<?php echo $year; ?>">
        <input type="hidden" name="start" value="<?php echo $start; ?>">
        <input type="hidden" name="end" value="<?php echo $end; ?>">
        <input type="hidden" name="sal_type" value="<?php echo $sal_type; ?>"> 
        <input type="hidden" name="Ecode" value="<?php echo $Ecode; ?>">       

</form>
              </div>
                        
             </div>
         </div>
     </div>
     
<script>

function UpdateCost() {
  var sum = 0;
  var ern = 0;
  var netpay = 0;
  var gn, elem;
  for (i=1; i<<?php echo $inv_count; ?>; i++) {
    gn = 'add_m_'+i;
	
//swal({ title: 'Success!', text: sum, timer: 1000 })
		
    elem = document.getElementById(gn);
	var mystr = elem.value;
	var myarr = mystr.split("__");
			  
    if (elem.checked == true) { sum += Number(myarr[5]); ern += Number(myarr[5]); }
  }
  	sum=sum+<?php echo $total_earnings + $basic_sal; ?>;
	netpay=sum-<?php echo $total_deduction; ?>
	
  document.getElementById('totalcost' ).value = netpay.toFixed(0);
  document.getElementById('earning_income' ).value = ern.toFixed(0);
}
window.onload=UpdateCost

</script>	



<?php } else { ?>

<div class="row">
<div class="col-lg-8">
    <div class="ibox float-e-margins">
    <div class="ibox-title"><h5>Employee Salary Settings</h5></div>
    
                <div class="ibox-content">

<div class="row">
<form action="index.php?sal" method="POST" id="subject" name="subject" enctype="multipart/form-data" >

<div class="col-md-8"> 

<div id="">
            <label for="reg_input_no" class="req">Search Employee</label>
            <select name="EmployeeCode"  class="input-sm chosen-select" style="width:350px;" >
             <option selected="selected" value="">Search and Select Staff</option>
            <?php $stmt = $db->query('SELECT EmployeeCode, FirstName, LastName FROM hremp WHERE status=0 order by EmployeeCode');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
<option value="<?php echo $row['EmployeeCode']; ?>"><?php echo $row['EmployeeCode'].' '.$row['FirstName'].', '.$row['LastName']; ?></option>
            <?php } ?>
            </select>
        
        </div>

</div>

<div class="col-md-2"> 
                             
                            <div id="">
               <label for="reg_input_no" class=""><strong style="color:#F00">.</strong></label><br>
                             <button class="btn btn-primary btn btn-sm" type="submit" name="apply_approve" >Apply</button> 
                             </div>
</div>


<div class="col-md-2"> 
                             
                            <div align="right" id="">
               <label for="reg_input_no" class="">.</label><br>
               <a href="index.php?sal=<?php echo $ECode; ?>" class="btn btn-warning btn btn-sm">Reset</a>
                             </div>
</div>


</form>
</div>

<hr>	

               <?php
        if (isset($_GET['sal']) and $_GET['sal'] != '') {
            $ECode = $_GET['sal'];
        }
    ?>

<?php
     $stmt_list = $db->query("SELECT * FROM hred_details_sal WHERE employee_no='$ECode' and EorD='B'");
    if ($stmt_list->rowCount() > 0) {
        $row = $stmt_list->fetch(PDO::FETCH_ASSOC);
        $basic_sal = $row['flat_percent_value'];
        $basic_status = 1;
        echo '<strong>Basic Salary</strong>';
    } elseif ($stmt_list->rowCount() == 0 and $ECode != '') {
        $basic_status = 0;
        ?>

<table width="100%"><tr><td><strong>Basic Salary</strong></td><td><strong style="color:#F03">Enter Basic Salary before you continue ... </strong></td></tr></table>

			<?php }	?>

<?php if ($ECode != '') { ?>

<form method="POST" id="" action="index.php?sal=<?php echo $ECode; ?>">
    <div class="form_sep">
    <input type="text" id="basic_sal" name="basic_sal" class="input-sm form-control"  value="<?php
    echo $bc = $row['flat_percent_value']; ?>">
    </div>

    <div align="right" class="form_sep ">
    <button class="btn btn-success btn-sm" type="submit" name="basic_salary" id="basic_salary">Save Basic Salary</button>
    </div>

    
<input type="hidden" name="Ecode" value="<?php echo $ECode; ?>" /> 
</form>
<hr>
<?php } ?>


<?php if ($basic_status == 1) { ?>

    <h3>Gross salary(Optional - for ease of computation only)</h3>
    <form method="POST" id="" action="index.php?sal=<?php echo $ECode; ?>">
        <div class="form_sep">
        <input type="text" id="gross_sal" name="gross_sal" class="input-sm form-control"  value="<?php
        echo $row['flat_percent_value_gross']; ?>">
        </div>

        <div align="right" class="form_sep ">
        <button class="btn btn-success btn-sm" type="submit" name="gross_salary" id="gross_salary">Save Gross Salary</button>
        </div>

        
        <input type="hidden" name="Ecode" value="<?php echo $ECode; ?>" /> 
    </form>
    <hr>
<?php
// set the gross as a global variable
$_gross_salary_global = $row['flat_percent_value_gross'];
    ?>
<strong style="color:#00C">EARNINGS/ALLOWANCE TABLE</strong>
<table class="table table-striped table-bordered table-hover" >                 
            <thead>
            <tr>
                <th></th>
                <th>Description</th>
                <th width="12%">.</th>
                <th width="15%">Duration</th>
                <th width="10%">Amount</th>
                <th width="20%">Set by/Date</th>
                <th width="10%">.</th>
            </tr>
            </thead>
            <tbody>
<?php
            $total_earning = 0;
    $stmt_list = $db->query("SELECT * FROM hred_details_sal WHERE employee_no='$ECode' and EorD='E' order by sn");
    if ($stmt_list->rowCount() > 0) {
        $c = 1;
        while ($row = $stmt_list->fetch(PDO::FETCH_ASSOC)) {
            $p = $row['flat_percent_value'];

            if ($row['flat_percent'] == 'Percent') {
                $percent = $p / 100;
                $earn_amt = $percent * $bc;
                $total_earning = $total_earning + $earn_amt;
            }

            if ($row['flat_percent'] == 'Percent_Of_Gross') {
                $percent = $p / 100;
                $earn_amt = $percent * $_gross_salary_global;
                $total_earning = $total_earning + $earn_amt;
            }

            if ($row['flat_percent'] == 'Flat') {
                $total_earning = $total_earning + $p;
                $earn_amt = $p;
            }

            ?>
                                    <tr>
                                <td><?php echo $c; ?></td>
                                <td><?php echo $row['descriptn']; ?></td>
                                <td><?php echo $row['flat_percent_value'].' '.$row['flat_percent']; ?></td>
                                <td><?php echo $row['duration'].' / '.$row['bal']; ?></td>
                                <td><?php echo number_format($earn_amt); ?></td>
                                <td><?php echo $row['setby'].'<br>'.date('d M,y', strtotime($row['set_date'])); ?></td>
                                <td><a href="index.php?sal=<?php echo $ECode.'&de='.$row['sn']; ?>">Delete</a></td>
                                    </tr>
                                    <?php
                                    ++$c;
        }?>
                                    <tr>
                                <td></td>
                                <td colspan="3" align="right"><strong style="font-size:16px;">Total Earnings:</strong> </td>
                                <td><strong style="font-size:16px;"><?php echo number_format($total_earning); ?></strong></td>
                                <td></td><td></td>                                        
				<?php } ?>
                                    </tbody>
                                    </table>
        <input type="button" name="users" value="+ Add Earning" data-target="#modal" id="<?php echo $ECode; ?>" class="btn btn-success btn-sm add_earning" /><hr>






				<strong style="color:#F03"><?php echo $err_title; ?></strong>
<?php
     $stmt_list = $db->query("SELECT * FROM hred_details WHERE employee_no='$ECode'");
    if ($stmt_list->rowCount() > 0) {
        ?>     					
				<strong style="color:#00C">EARNINGS FROM INCOMES TABLE</strong>
						<table class="table table-striped table-bordered table-hover" >                 
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th>Description</th>
                                        <th>.</th>
                                        <th>Duration</th>
                                        <th>Set by</th>
                                        <th>Date</th>
                                         <th>.</th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                                $c = 1;
        while ($row = $stmt_list->fetch(PDO::FETCH_ASSOC)) {?>
                                    <tr>
                                <td><?php echo $c; ?></td>
                                <td><?php echo $row['service_type_earn']; ?></td>
                                <td><?php echo $row['flat_percent'].' ('.$row['flat_percent_value'].' )'; ?></td>
                                <td><?php echo $row['duration']; ?></td>
                                <td><?php echo $row['setby']; ?></td>
                                <td><?php echo date('d M,y', strtotime($row['set_date'])); ?></td>
                                <td>
                                <?php if ($row['service_type_earn'] != 'All Hospital Services') {?>

<a data-toggle="modal" data-target="#myModal5" class="income_add_services" id="<?php echo $row['sn'].'__'.$row['service_type_earn'].'__'.$ECode; ?>">Add Services List</a>
           
                                		&nbsp; | &nbsp;
                                <?php } ?>
                                        <a href="index.php?sal=<?php echo $ECode.'&dl='.$row['sn']; ?>">Delete</a>
                                </td>
                                    </tr>
                                    <?php
                                    ++$c;
        }?>

                                    </tbody>
                                    </table>
                                    
        <input type="button" name="users" value="+ Add Income" data-target="#modal" id="<?php echo $staff_no; ?>" class="btn btn-success btn-sm income_earning" />
                                    
				<?php } ?>
                
                
                
<hr>
<strong style="color: #F00">DEDUCTIONS TABLE</strong>
<table class="table table-striped table-bordered table-hover" >                 
            <thead>
            <tr>
                <th></th>
                <th>Description</th>
                <th width="12%">.</th>
                <th width="15%">Duration</th>
                <th width="10%">Amount</th>
                <th width="20%">Set by/Date</th>
                <th width="10%">.</th>
            </tr>
            </thead>
            <tbody>
<?php
        $total_deduction = 0;
    $stmt_list = $db->query("SELECT * FROM hred_details_sal WHERE employee_no='$ECode' and EorD='D' order by sn");
    if ($stmt_list->rowCount() > 0) {
        $c = 1;
        while ($row = $stmt_list->fetch(PDO::FETCH_ASSOC)) {
            $p = $row['flat_percent_value'];

            if ($row['flat_percent'] == 'Percent') {
                $percent = $p / 100;
                $earn_amt = $percent * $bc;
                $total_deduction = $total_deduction + $earn_amt;
            }

            if ($row['flat_percent'] == 'Percent_Of_Gross') {
                $percent = $p / 100;
                $earn_amt = $percent * $_gross_salary_global;
                $total_deduction = $total_deduction + $earn_amt;
            }

            if ($row['flat_percent'] == 'Flat') {
                $total_deduction = $total_deduction + $p;
                $earn_amt = $p;
            }

            ?>
                                    <tr>
                                <td><?php echo $c; ?></td>
                                <td><?php echo $row['descriptn']; ?></td>
                                <td><?php echo $row['flat_percent_value'].' '.$row['flat_percent']; ?></td>
                                <td><?php echo $row['duration'].' / '.$row['bal']; ?></td>
                                <td><?php echo number_format($earn_amt); ?></td>
                                <td><?php echo $row['setby'].'<br>'.date('d M,y', strtotime($row['set_date'])); ?></td>
                                <td><a href="index.php?sal=<?php echo $ECode.'&de='.$row['sn']; ?>">Delete</a>
                                </td>
                                    </tr>
                                    <?php
                                    ++$c;
        }?>
                                    <tr>
                                <td></td>
                              <td colspan="3" align="right"><strong style="font-size:16px;">Total Deductions:</strong> </td>
                                <td><strong style="font-size:16px;"><?php echo number_format($total_deduction); ?></strong></td>
                                <td></td><td></td>                                        
				<?php } ?>
                                    </tbody>
                                    </table>

        <input type="button" name="users" value="+ Add Deduct" data-target="#modal" id="<?php echo $ECode; ?>" class="btn btn-danger btn-sm add_deduct" /><hr>

             
<div align="right"><strong style="font-size:25px;">Net Pay: </strong><strong style="font-size:20px;">N
<?php $rslt = ($total_earning + $bc) - $total_deduction;
    echo number_format($rslt); ?></strong></div>

<?php } ?>           

   </div>
</div>
</div>


<div class="col-lg-4">
    <div class="ibox float-e-margins">
    <div class="ibox-title"><h5>Employee Payroll</h5></div>
    
                <div class="ibox-content">

			<?php

                        if (isset($_POST['EmployeeCode']) and $_POST['EmployeeCode'] != '') {
                            $ECode = $_POST['EmployeeCode'];
                            header("location:index.php?sal=$ECode");
                        }

    if (isset($_GET['sal']) and $_GET['sal'] != '') {
        $ECode = $_GET['sal'];
        $stmt = $db->query("SELECT EmployeeCode, FirstName, LastName FROM hremp WHERE EmployeeCode='$ECode'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        ?>
			<div class="form_sep">
            <strong><?php echo $ECode.' / '.$row['FirstName'].', '.$row['LastName']; ?></strong>
            </div>

			<div class="form_sep">
            <strong><?php echo 'Earnings:  / '.number_format($total_earning).' | &nbsp; Deductions: / '.number_format($total_deduction); ?></strong>
            </div>
            
 			<div class="form_sep"><strong style="font-size:15px;">Net Pay: </strong><strong style="font-size:15px;">N
<?php $rslt = ($total_earning + $bc) - $total_deduction;
        echo number_format($rslt); ?></strong></div>


 <?php if ($basic_status == 1) { ?>
        
		    <div class="form_sep">
            <p>Set earning for Income Generate <br>by Staff here... </p>
        <input type="button" name="users" value="+ Add Income &nbsp;&nbsp;&nbsp;&nbsp;" data-target="#modal" id="<?php echo $staff_no; ?>" class="btn btn-success btn-sm income_earning" />
            
			</div>
	
    <hr>
    <?php
                $ECode = $_GET['sal'];
     $stmt = $db->query("SELECT status FROM hremp WHERE EmployeeCode='$ECode'");
     $row = $stmt->fetch(PDO::FETCH_ASSOC);
     // print_r($row);
     $status = $row['status'];
     ?>
    <?php if ($status == 0) { ?>
    <form action="index.php?sal=<?php echo $ECode; ?>" method="POST" style="background-color:#FFC; padding:15px; ">
            <h4><strong>Prepare Pay Slip here ...</strong></h4><hr>  
                 
            <div class="form_sep">
            <label for="reg_select" class="req">Month</label>
                <select name="month" id="month" class="form-control" required>
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
            <label for="reg_select" class="req">Year</label>
<input type="text" id="year" name="year" class="form-control"  value="<?php echo date('Y'); ?>" maxlength="4" required>
            </div>
            	
 <div class="form_sep">
               <label for="reg_input_no" class="req">Set Dates Range</label><br>
                <div class="form_sep" id="">
                <div class="input-daterange input-group" id="">
                <input type="date" class="form-control" name="start" value="" required/>
                <span class="input-group-addon">to</span>
                <input type="date" class="form-control" name="end" value="" required />
                </div>

            </div>
            </div>           
<br><strong>Choose one of the option below:</strong>
<div class="radio i-checks"><label> <input type="radio" value="full" name="sal_type" required> &nbsp;Pay Full Month </label></div>
<div class="radio i-checks"><label> <input type="radio" value="days" name="sal_type" required> &nbsp;Pay Days Worked </label></div>
                                                
            <div class="form_sep">
            <label for="reg_select" class="">Enter Total Days Worked</label>
<input type="number" id="days" name="days" class="form-control" min="1">
            </div>
            
                        
		    <div class="form_sep">
		<button class="btn btn-info btn btn-sm" type="submit" name="generate_my_payslip" id="generate_my_payslip" >Generate Payslip</button>
			</div>
     <input type="hidden" name="Ecode" value="<?php echo $ECode; ?>" />        
</form>     
<?php } elseif ($status == 1) { ?>
    <p style="background-color:#FFC; padding:15px; ">This user has been disengaged, you can't generate a payslip for them</p>
<?php } elseif ($status == 2) { ?>
    <p style="background-color:#FFC; padding:15px; ">This user has been suspended, you can't generate a payslip for them</p>
<?php } ?>
         
  <hr>
  
   <div class="form_sep">
  		<a href="index.php?slp=<?php echo $ECode; ?>">View Payslip Reports </a>
   </div>         
            
<?php }
 }
    ?>
             
   </div>
</div>
</div>

</div>

<?php } ?>


<?php

    function add_payslip($Ecode, $descriptn, $pay_head, $amount, $month, $year, $date_range, $days, $setdatetime, $duration, $bal, $row_sn, $pay_head_type)
{
    include '../Connections/Conn.php';

    // Check if the payslip already exists
    $chk = $db->prepare("SELECT * FROM hrpayslip WHERE ECode = ? AND descriptn = ? AND pay_head = ? AND month = ? AND year = ? AND date_range = ?");
    $chk->execute([$Ecode, $descriptn, $pay_head, $month, $year, $date_range]);

    if ($chk->rowCount() == 0) {
        // Insert new payslip
        $insertSQL = "INSERT INTO hrpayslip (ECode, descriptn, pay_head, amount, month, year, date_range, days, generate_by, generate_date) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($insertSQL);
        $stmt->execute([
            $Ecode, 
            $descriptn, 
            $pay_head, 
            $amount, 
            $month, 
            $year, 
            $date_range, 
            $days, 
            $_SESSION['fullname'], 
            $setdatetime
        ]);

        $sv = 1;

        // Update duration for 'Earnings' or 'deductions'
        if ($bal != 'always' && ($pay_head_type == 'Earnings' || $pay_head_type == 'deductions')) {
            $duration -= 1;
            $updateSQL = "UPDATE hred_details_sal SET duration = ? WHERE sn = ?";
            $stmt = $db->prepare($updateSQL);
            $stmt->execute([$duration, $row_sn]);
            $sv = 1;
        }

        // Update duration for 'Earnings_Income'
        if ($bal != 'always' && $pay_head_type == 'Earnings_Income') {
            $duration -= 1;
            $updateSQL = "UPDATE hred_income_services SET duration = ? WHERE sn = ?";
            $stmt = $db->prepare($updateSQL);
            $stmt->execute([$duration, $row_sn]);
            $sv = 1;
        }
    }
}

?>


<div class="modal inmodal fade" id="services_earnin_modal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm" >
        <div class="modal-content">
            <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Earning from Income Generated</h4>
			</div>

                <div class="modal-body">  
                     <form method="POST" id="" action="index.php?sal=<?php echo $ECode; ?>">

                <div class="form_sep">
                <label for="reg_select" class="req">Select Income Services</label>
                <select name="service_type_earn" id="service_type_earn" class="form-control" required>
                			<option selected="selected" value="">Select...</option>
                        <option value="All Hospital Services">All Hospital Services</option>
                        <option value="Category">Selected Category/Department</option>
                        <option value="Specify">Selected Services/Items</option>
                </select>
                 </div>	    

                <div class="form_sep">
                <label for="reg_select" class="req">Flat/Percentage</label>
                <select name="flat_percent" id="flat_percent" class="form-control" required>
                			<option selected="selected" value="">Select...</option>
                        <option value="Flat">Flat</option>
                        <option value="Percent">Percentage</option>
                </select>
                 </div>	
                  
                <div class="form_sep">
                <label for="reg_input_name" class="req">Flat/Percentage Value/Amount:</label>
                <input type="text" id="flat_percent_amt" name="flat_percent_amt" class="form-control" maxlength="12" required>
                </div>

                <div class="form_sep">
                <label for="reg_input_name" class="req">Duration: &nbsp; <small>Enter Zero(0) if duration is unlimited</small></label>
                <input type="number" id="duration" name="duration" class="form-control" required>
                </div>

 				
                <div class="form_sep">
                <div class="pull-left">
                <button class="btn btn-success btn-sm" type="submit" name="save_income_earn" id="save_income_earn" >Save</button>
                </div>
                <div class="pull-right">
                	<a href="" class="btn btn-warning btn-sm">Close</a>
                </div>
                </div>
					<input type="hidden" name="Ecode" value="<?php echo $ECode; ?>" /> 
</form>


                </div>  
           </div>  
      </div>  
 </div>


<div class="modal inmodal fade" id="add_earning_modal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm" >
        <div class="modal-content">
            <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Add Earnings / Allowances </h4>
			</div>

                <div class="modal-body">  
                     <form method="POST" id="" action="index.php?sal=<?php echo $ECode; ?>">

                <div class="form_sep">
                <label for="reg_select" class="req">Select Earning/Allowances</label>
                <select name="select_earn" id="select_earn" class="form-control" required>
                			<option selected="selected" value="">Select...</option>
                            
            <?php $stmt = $db->query("SELECT * FROM hred WHERE type='E' order by descriptn");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
<option value="<?php echo $row['descriptn']; ?>"><?php echo $row['descriptn']; ?></option>
            <?php } ?>
                </select>
                 </div>	
                <div class="form_sep">
                <label for="reg_input_name" class="">Other Earnings/Allowances (Specify here)</label>
                <input type="text" id="other_earning" name="other_earning" class="form-control" maxlength="50">
                </div>                 

                <div class="form_sep">
                <label for="reg_select" class="req">Flat/Percentage</label>
                <select name="flat_percent" id="flat_percent" class="form-control" required>
                			<option selected="selected" value="">Select...</option>
                        <option value="Flat">Flat Amount</option>
                        <option value="Percent">Percentage of Basic Salary</option>
                        <?php if ($_gross_salary_global > 0) { ?>
                        <option value="Percent_Of_Gross">Percentage of Gross Salary</option>
                        <?php } ?>
                </select>
                 </div>	
                  
                <div class="form_sep">
                <label for="reg_input_name" class="req">Enter Percentage or Flat Rate:</label>
                <input type="text" id="flat_percent_amt" name="flat_percent_amt" class="form-control" maxlength="12" required>
                </div>

                <div class="form_sep">
                <label for="reg_input_name" class="req">Duration: &nbsp; <small>Enter Zero(0) if duration is unlimited</small></label>
                <input type="number" id="duration" name="duration" class="form-control" required>
                </div>

 				
                <div class="form_sep">
                <div class="pull-left">
                <button class="btn btn-success btn-sm" type="submit" name="save_earnings" id="save_earnings" >Add Earnings</button>
                </div>
                <div class="pull-right">
                	<a href="" class="btn btn-warning btn-sm">Close</a>
                </div>
                </div>
					<input type="hidden" name="Ecode" value="<?php echo $ECode; ?>" /> 
</form>


                </div>  
           </div>  
      </div>  
 </div>
 
 
 
 <div class="modal inmodal fade" id="add_earning_modal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm" >
        <div class="modal-content">
            <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Add Earnings / Allowances </h4>
			</div>

                <div class="modal-body">  
                     <form method="POST" id="" action="index.php?sal=<?php echo $ECode; ?>">

                <div class="form_sep">
                <label for="reg_select" class="req">Select Earning/Allowances</label>
                <select name="select_earn" id="select_earn" class="form-control" required>
                			<option selected="selected" value="">Select...</option>
                            <option value="Others">Others (Specify Below)</option>
                            
            <?php $stmt = $db->query("SELECT * FROM hred WHERE type='E' order by descriptn");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
<option value="<?php echo $row['descriptn']; ?>"><?php echo $row['descriptn']; ?></option>
            <?php } ?>
                </select>
                 </div>	
                <div class="form_sep">
                <label for="reg_input_name" class="">Other Earnings/Allowances (Specify here)</label>
                <input type="text" id="other_earning" name="other_earning" class="form-control" maxlength="50">
                </div>                 

                <div class="form_sep">
                <label for="reg_select" class="req">Flat/Percentage</label>
                <select name="flat_percent" id="flat_percent" class="form-control" required>
                			<option selected="selected" value="">Select...</option>
                        <option value="Flat">Flat Amount</option>
                        <option value="Percent">Percentage of Basic Salary</option>
                </select>
                 </div>	
                  
                <div class="form_sep">
                <label for="reg_input_name" class="req">Enter Percentage or Flat Rate:</label>
                <input type="text" id="flat_percent_amt" name="flat_percent_amt" class="form-control" maxlength="12" required>
                </div>

                <div class="form_sep">
                <label for="reg_input_name" class="req">Duration: &nbsp; <small>Enter Zero(0) if duration is unlimited</small></label>
                <input type="number" id="duration" name="duration" class="form-control" required>
                </div>

 				
                <div class="form_sep">
                <div class="pull-left">
                <button class="btn btn-success btn-sm" type="submit" name="save_earnings" id="save_earnings" >Add Earnings</button>
                </div>
                <div class="pull-right">
                	<a href="" class="btn btn-warning btn-sm">Close</a>
                </div>
                </div>
					<input type="hidden" name="Ecode" value="<?php echo $ECode; ?>" /> 
</form>


                </div>  
           </div>  
      </div>  
 </div>
 
 
 <div class="modal inmodal fade" id="add_deduct_modal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm" >
        <div class="modal-content">
            <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Add Deductions </h4>
			</div>

                <div class="modal-body">  
                     <form method="POST" id="" action="index.php?sal=<?php echo $ECode; ?>">

                <div class="form_sep">
                <label for="reg_select" class="req">Select Deductions</label>
                <select name="select_deduct" id="select_deduct" class="form-control" required>
                			<option selected="selected" value="">Select...</option>
                            
            <?php $stmt = $db->query("SELECT * FROM hred WHERE type='D' order by descriptn");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
<option value="<?php echo $row['descriptn']; ?>"><?php echo $row['descriptn']; ?></option>

            <?php } ?>
            <option value="Others">Others (Specify Below)</option>
                </select>
                 </div>	
                <div class="form_sep">
                <label for="reg_input_name" class="">Other Deductions (Specify here)</label>
                <input type="text" id="other_deduct" name="other_deduct" class="form-control" maxlength="50">
                </div>                 

                <div class="form_sep">
                <label for="reg_select" class="req">Flat/Percentage</label>
                <select name="flat_percent2" id="flat_percent2" class="form-control" required>
                			<option selected="selected" value="">Select...</option>
                        <option value="Flat">Flat Amount</option>
                        <option value="Percent">Percentage of Basic Salary</option>
                        <?php if ($_gross_salary_global > 0) { ?>
                        <option value="Percent_Of_Gross">Percentage of Gross Salary</option>
                        <?php } ?>
                </select>
                 </div>	
                  
                <div class="form_sep">
                <label for="reg_input_name" class="req">Enter Percentage or Flat Rate:</label>
                <input type="text" id="flat_percent_amt2" name="flat_percent_amt2" class="form-control" maxlength="12" required>
                </div>

                <div class="form_sep">
                <label for="reg_input_name" class="req">Duration: &nbsp; <small>Enter Zero(0) if duration is unlimited</small></label>
                <input type="number" id="duration2" name="duration2" class="form-control" required>
                </div>

 				
                <div class="form_sep">
                <div class="pull-left">
                <button class="btn btn-success btn-sm" type="submit" name="save_deductions" id="save_deductions" >Add Deductions</button>
                </div>
                <div class="pull-right">
                	<a href="" class="btn btn-warning btn-sm">Close</a>
                </div>
                </div>
					<input type="hidden" name="Ecode" value="<?php echo $ECode; ?>" /> 
</form>
					

                </div>  
           </div>  
      </div>  
 </div>
 
<div class="modal inmodal fade" id="add_income_services_modal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Add Services</h4>
			</div>
                            <div class="modal-body" id="add_income_services_body">  
                </div>
                
        </div>
    </div>
</div> 
