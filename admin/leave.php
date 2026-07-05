<?php

if (isset($_GET["dl"])) {

	$dl=$_GET['dl'];
  $deleteSQL = $db->prepare("DELETE FROM hrlv WHERE sn='$dl'");
		$deleteSQL->execute(); 
		
		header("location:index.php?LV");
}

if (isset($_GET["LV"])) {
		$LV=$_GET["LV"];
	if($LV!=''){
		$edit_mode=1;
		$stmt=$db->query("Select * from hrlv where sn='$LV'");
			$rowx=$stmt->fetch(PDO::FETCH_ASSOC);
	}else{
			$edit_mode=0;
	}
}


if (isset($_POST["LV"])) {
	
		if($_POST["edit_mode"]==0){
			$leave_type=$_POST['leave_type'];
					$stmt=$db->query("Select * from hrlv where leave_type='$leave_type'");
				if($stmt->rowCount()==0){
						$update = sprintf("INSERT INTO hrlv(leave_type,days,approver1,approver2) VALUES (%s,%s,%s,%s)",
					GetSQLValueString($_POST["leave_type"], "text"), GetSQLValueString($_POST["total_days"], "text"),
					GetSQLValueString($_POST["approver1"], "text"),GetSQLValueString($_POST["approver2"], "text"),
					GetSQLValueString($_POST["apply_type"], "text"));
					$db->exec($update);	
				}
		}else{
			  			$update = sprintf("UPDATE hrlv SET leave_type=%s, days=%s,approver1=%s,approver2=%s,apply_type=%s  WHERE sn=%s",
					GetSQLValueString($_POST['leave_type'], "text"),
					GetSQLValueString($_POST['total_days'], "text"),
					GetSQLValueString($_POST['approver1'], "text"),
					GetSQLValueString($_POST['approver2'], "text"),	
					GetSQLValueString($_POST['apply_type'], "text"),					
					GetSQLValueString($_POST['sn'], "text"));
					$db->exec($update);	
			}
									  
header("location:index.php?LV");				  
	
 }
?>                        


<div class="row">
<div class="col-lg-12">
    <div class="ibox float-e-margins">
    <div class="ibox-title"><h5>Set Leave Category</h5></div>
                <div class="ibox-content">


                        <form action="<?php echo $editFormAction; ?>" method="POST" id="subject" name="subject" enctype="multipart/form-data">

<div class="form_sep">
<label for="reg_input_no" class="req">Enter Leave Type</label>
<input type="text" id="leave_type" name="leave_type" class="form-control"  data-required="true" placeholder="" maxlength="50" value="<?php echo $rowx['leave_type']; ?>" required >
</div>
 
 
<div class="form_sep">
<label for="reg_input_no" class="req">Enter Total Days</label>
<input type="number" id="total_days" name="total_days" class="form-control"  data-required="true" min="1" value="<?php echo $rowx['days']; ?>" required >
</div>
  
<div class="form_sep">
<label for="reg_select" class="req">Select Leave Approver I </label>
 <select name="approver1[]" data-placeholder="   -- State Multi-Selection --" class="chosen-select" multiple style="width:350px;" tabindex="4">	
<!--<select name="approver1" id="approver1" class="form-control" required>
-->	
					<?php if ($rowx['approver1']!=''){ ?>               
                    <option value="<?php echo $rowx['approver1']; ?>"><?php echo $rowx['approver1']; ?></option>              
                             <?php }else { ?>
                                <option selected="selected" value="">Select ...</option>
                              <?php } ?>
	 <option value="HOD">Head of Department</option>
                              
	<?php 
	$stmt_apr = $db->query("SELECT DISTINCT Designation FROM hremp");
	while ($rwx = $stmt_apr->fetch(PDO::FETCH_ASSOC)){ ?>
<option value="<?php echo $rwx["Designation"]; ?>"><?php echo $rwx["Designation"]; ?></option>
          <?php } ?>
</select>
</div>
 
 
<div class="form_sep">
<label for="reg_select" class="req">Select Leave Approver II </label>
<select name="approver2" id="approver2" class="form-control" required>
					<?php if ($rowx['approver2']!=''){ ?>               
                    <option value="<?php echo $rowx['approver2']; ?>"><?php echo $rowx['approver2']; ?></option>              
                             <?php }else { ?>
                                <option selected="selected" value="">Select ...</option>
                              <?php } ?>
                              
	<?php 
	$stmt_apr = $db->query("SELECT DISTINCT Designation FROM hremp");
	while ($rwx = $stmt_apr->fetch(PDO::FETCH_ASSOC)){ ?>
<option value="<?php echo $rwx["Designation"]; ?>"><?php echo $rwx["Designation"]; ?></option>
          <?php } ?>
</select>
</div>

<div class="form_sep">
<label for="reg_select" class="req">Select Application Type </label>
<select name="apply_type" id="apply_type" class="form-control" required>
					<?php if ($rowx['apply_type']!=''){ ?>               
                    <option value="<?php echo $rowx['apply_type']; ?>"><?php echo $rowx['apply_type']; ?></option>              
                             <?php }else { ?>
                                <option selected="selected" value="">Select ...</option>
                              <?php } ?>
			<option value="Yealy">Yealy</option>
            <option value="Monthly">Monthly</option>
            <option value="Any Time">Any Time"</option>
</select>
</div>
		
<div class="form_sep">
<div class="pull-left">
    <button class="btn btn-success" type="submit" name="LV" id="LV" >Save</button>
    </div>
    
    <div class="pull-right">
    	<a href="index.php?LV" class="btn btn-warning">Cancel</a>
    </div>
    </div>
   <input type="hidden" name="edit_mode" value="<?php echo $edit_mode; ?>" />  
 <input type="hidden" name="sn" value="<?php echo $LV; ?>" />
    </form>
	
<hr>

  <?php  
	  
	  
                          
	$stmt = $db->query("SELECT * FROM hrlv order by sn");
				if($stmt->rowCount()>0){?>

			 <table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="9">
                                    <thead>
                                    <tr>
                                        <th data-toggle="true">SI.No</th>
                                        <th data-toggle="true">Leave Category</th>
                                         <th data-toggle="true">Days</th>
                                         <th data-toggle="true">Approver I</th>
                                         <th data-toggle="true">Approver II</th>
                                         <th data-toggle="true">Apply Type</th>
                                        <th data-toggle="true">Manage</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                        <?php 
											$n=1;
											while($row=$stmt->fetch(PDO::FETCH_ASSOC)) { 
											if($colordecide%2==0){$bgcolor="#F4F4F4";}else{$bgcolor="#FFFFFF";}
											?>
                                           <tr bgcolor="<?php echo $bgcolor; ?>">  
                                            <td><?php echo $row['sn']; ?></td>
                  							<td><?php echo $row['leave_type']; ?></td>
											<td><?php echo $row['days']; ?></td>
                                            <td><?php echo $row['approver1']; ?></td>
                                            <td><?php echo $row['approver2']; ?></td>
                                            <td><?php echo $row['apply_type']; ?></td>
                  <td><a href="index.php?<?php echo 'LV='.$row['sn'] ;?>"> [ Edit ] </a>
                  			&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
                       <a href="index.php?LV&<?php echo 'dl='.$row['sn'] ;?>"> [ Delete ] </a>     
                            
                  
                  </td>
                                            </tr>
                                        <?php 
										  $colordecide++; $n++;	
										}?>
											
                                    </tbody>
                                    
                                    </table>
                            <?php }else{echo 'No Records Found';}

?>	 
             
   </div>
</div>
</div>
</div>

<script>
</script>
