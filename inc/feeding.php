<div class="row">
<div class="col-lg-8"> 
<div class="ibox ">
<div class="ibox-content">
	
	
<?php 
	
session_start();			 
require_once('../Connections/Conn.php'); 
$hospital_no=$_POST['hospital_no'];			
				
$appointment_number=$_POST['appointment_number'];			
				
$stmt =$db->prepare("SELECT app_no FROM admission WHERE hospital_no=:hospital_no and adm_status='3' order by sn desc limit 1");
$stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmt->execute();
	
if($stmt->rowCount()>0){ 
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$app_no=$row['app_no'];	
}
if($app_no==''){$app_no=$appointment_number;}
	
	
$stmtt =$db->prepare("SELECT * FROM feedings WHERE app_no=:app_no and hospital_no=:hospital_no order by sn desc limit 30");
$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmtt->execute();	
if($stmtt->rowCount()>0){ ?>
	
	<h4>Current Feeding Progress List</h4>
	<div class="panel-heading">
			</div>

	<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
				<thead>
					<tr>
						<th data-hide="phone,tablet">Date</th>
						<th data-toggle="true">Nature of Feeding</th>
						<th data-toggle="true">Quantity of Feeding</th>
						<th data-toggle="true">Comments</th>
						<th data-toggle="true">Responsible</th>
						<th data-toggle="true">.</th>
					</tr>
				</thead>
				<tbody>
				<?php  while($row = $stmtt->fetch(PDO::FETCH_ASSOC)){ ?>
					<tr>
					<td><?php echo $row['time_feeding']; ?></td>
					<td><?php echo $row['nature_feed']; ?></td>
					<td><?php echo $row['quantity_feed']; ?></td>
					<td><?php echo $row['feed_comment']; ?></td>
					<td><?php echo $row['prepared_by']; ?></td>
					<?php if ($_SESSION['fullname']==$row['prepared_by']){ ?> 
					<td><a href="patient.php?hosp_no=<?= $hospital_no; ?>&fd=<?php echo $row['sn']; ?>" onclick="return confirm('Are you sure you want to DELETE?')">Delete</a></td>
					 <?php 	}?>
					</tr>
				<?php 	}?>

				</tbody>
				<tfoot class="hide-if-no-paging">
					<tr>
						<td colspan="6" class="text-center">
							<ul class="pagination pagination-sm"></ul>
						</td>
					</tr>
				</tfoot>
			</table>
	<?php } ?>

	
	<hr>
	
	<?php
$stmtt =$db->prepare("SELECT * FROM feedings WHERE app_no!=:app_no and hospital_no=:hospital_no order by sn desc limit 30");
$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmtt->execute();	
if($stmtt->rowCount()>0){ ?>
	
	<h4 style="color: darkred; "><u>Previous</u> Feeding Progress List</h4>
	<div class="panel-heading">
			</div>

	<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
				<thead>
					<tr>
						<th data-hide="phone,tablet">Date</th>
						<th data-toggle="true">Nature of Feeding</th>
						<th data-toggle="true">Quantity of Feeding</th>
						<th data-toggle="true">Comments</th>
						<th data-toggle="true">Responsible</th>
					</tr>
				</thead>
				<tbody>
				<?php  while($row = $stmtt->fetch(PDO::FETCH_ASSOC)){ ?>
					<tr>
					<td><?php echo $row['time_feeding']; ?></td>
					<td><?php echo $row['nature_feed']; ?></td>
					<td><?php echo $row['quantity_feed']; ?></td>
					<td><?php echo $row['feed_comment']; ?></td>
					<td><?php echo $row['prepared_by']; ?></td>
					</tr>
				<?php 	}?>

				</tbody>
				<tfoot class="hide-if-no-paging">
					<tr>
						<td colspan="6" class="text-center">
							<ul class="pagination pagination-sm"></ul>
						</td>
					</tr>
				</tfoot>
			</table>
	<?php } ?>
	

</div>	
</div>	
</div>	
	
	
	
	
	
	
<div class="col-lg-4">
<div class="ibox ">
<div class="ibox-content">
<h2 align="center">Feeding Chart</h2><hr>

					<form action="patient.php?hosp_no=<?php echo $hospital_no; ?>" method="POST">

        <div class="form_sep">
            <label for="reg_textarea_message" class="req">Nature of Feed:</label>
<textarea name="nature" id="nature" cols="30" rows="2" class="form-control"  required></textarea>
        </div>        

        <div class="form_sep">
   <label for="reg_input_no" class="req">Quantity of Feed (ml):</label>
            <input type="text" id="qty" name="qty" class="form-control" required  maxlength="5">
                    </div>
        
        <div class="form_sep">
            <label for="reg_textarea_message" class="req">Comment:</label>
<textarea name="Comment" id="Comment" cols="30" rows="2" class="form-control" required ></textarea>
        </div>
			
			
	<div class="form_sep">
		<label for="reg_select" class="req">Date:</label>
            <div class="input-group date">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input type="date" name="ddate" class="form-control" required>
            </div>
        </div>
			

         <div class="form_sep">

<label for="reg_select" class="req">Time:</label>
			   <input type="time" name="ttime" id="ttime" class="form-control" required>
                                
            </div>   
				                                           

 
<div class="form_sep">
<button class="btn btn-success" type="submit" name="add_feeding" id="add_feeding" >Add Feeding</button>
 <input type="hidden" name="app_no" value="<?php echo $app_no; ?>" />
 <input type="hidden" name="hosp_no" value="<?php echo $hospital_no; ?>" />
</div>

</form>

	

			
	</div>
	</div>
	</div>
	</div>