<?php 
session_start();
require_once('../Connections/Conn.php'); ?>
<?php

if(isset($_POST['download_type'])) {
	
$download_type=$_POST['download_type'];
	
	if($download_type=='Pharmacy' or $download_type=='Nursing Consumable' or $download_type=='Store'){ 
	
	$stmt2 = $db->query("SELECT distinct category FROM stock_table WHERE stock_table='$download_type'");
		
		echo "<option value=''>-- Select --</option>";
        while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){ ?>
				<option value="<?php  echo $row2["category"]; ?>"><?php  echo $row2["category"]; ?></option>
        <?php }	
	
	
	}elseif($download_type=='Investigations'){ 
		
			$stmt2 = $db->query("SELECT distinct category FROM lab_scan");
		
		echo "<option value=''>-- Select --</option>";
        while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){ ?>
				<option value="<?php  echo $row2["category"]; ?>"><?php  echo $row2["category"]; ?></option>
        <?php }	
		
	}else{
		
	$stmt2 = $db->query("SELECT distinct category FROM prices_table WHERE price_table='$download_type'");
	echo "<option value=''>-- Select --</option>";
        while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){ ?>

				<option value="<?php  echo $row2["category"]; ?>"><?php  echo $row2["category"]; ?></option>

        <?php }	
	
			
	}
}

?>

