<?php 
session_start();
require_once('../Connections/Conn.php'); ?>

<?php
if(isset($_POST['target'])) {


	$target=$_POST['target'];
	$add_service_id_package_id=$_POST['add_service_id_package_id'];
			
	
	if($target =='medical_services'){
		$service_type = $target;
		$service_id = $_POST['medical_services'];
		$pp = explode("___", $service_id);
		$service_id = $pp[0];
		$service_title = $pp[1];
		$how_many=$_POST['how_many'];
	}elseif($target =='investigations'){
		$service_type = $target;
		$service_id = $_POST['investigations'];
		$pp = explode("___", $service_id);
		$service_id = $pp[0];
		$service_title = $pp[1];
		$how_many=$_POST['how_many2'];		
	}elseif($target =='pharmacy'){
		$service_type = $target;
		$service_id = $_POST['pharmacy'];
				$pp = explode("___", $service_id);
		$service_id = $pp[0];
		$service_title = $pp[1];
		$how_many=$_POST['how_many3'];
	}else{
		$service_type = $target;
		$service_id = $_POST['others'];
		$service_title = $_POST['others'];
	}

		if($how_many =='' or $how_many =='0'){
			$how_many =1;
		}
$stmt = $db->query("SELECT * FROM special_package 
WHERE service_table_id='$add_service_id_package_id' and service_id='$service_id'");
	if($stmt->rowCount()==0 and $service_id!=''){
			 
$sql = $db->prepare("INSERT INTO special_package (service_table_id, service_id,service_title, service_type,duration) 
	VALUES (:service_table_id,:service_id,:service_title, :service_type, :duration)");
				$sql->bindParam(':service_table_id',$add_service_id_package_id, PDO::PARAM_STR);
				$sql->bindParam(':service_id',$service_id, PDO::PARAM_STR);
				$sql->bindParam(':service_title',$service_title, PDO::PARAM_STR);
				$sql->bindParam(':service_type',$service_type, PDO::PARAM_STR);
				$sql->bindParam(':duration',$how_many, PDO::PARAM_STR);
				$sql->execute();
	

	
}
}


if(isset($_POST['target_table'])) { 
	
$n=1;
	$blk=0;
	
	///echo $_POST['del'];
	
	if(isset($_POST['del']) and $_POST['del']=='100') {
		$blk=1;
	}
		
		
$stmtt =$db->prepare("SELECT * FROM special_package  WHERE service_table_id=:service_table_id");
$stmtt->bindParam(':service_table_id', $_POST['target_table'], PDO::PARAM_STR);
$stmtt->execute();	
if($stmtt->rowCount()>0){ ?>

	<div class="panel-heading">
			</div>

	<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
				<thead>
					<tr>
						<th data-toggle="true">Sn</th>
						<th data-toggle="true">ITEM</th>
						<th data-toggle="true">TIMES(X)</th>
						<th data-toggle="true"></th>
					</tr>
				</thead>
				<tbody>
				<?php  while($row = $stmtt->fetch(PDO::FETCH_ASSOC)){ ?>
					<tr>
					<td><?php echo $n++; ?></td>
					<td><?php echo $row['service_title']; ?></td>
					<td><?php echo $row['duration']; ?></td>
						
			<td>
				<?php if($blk == 0){?>
		<a href="index.php?price=m&service_del=<?php echo $row['sn']; ?>" onclick="return confirm('Are you sure you want to DELETE?')">Delete</a>
			<?php 	}?>
	</td>
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
	
	
	
<?php 
								  }
?>
