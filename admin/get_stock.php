<?php 
session_start();
require_once('../Connections/Conn.php'); ?>

<?php
if(isset($_POST['stock_table_type_id'])) {
			$stock_table_type_id=$_POST['stock_table_type_id'];

	$stmt2 = $db->query("SELECT * FROM stock_table WHERE stock_table='$stock_table_type_id' order by product_name");
	echo "<option value=''>-- Select --</option>";
        while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){ ?>
<option value="<?php echo $row2["sn"]; ?>"><?php  echo $row2["product_name"]; ?></option>
        <?php }	
	
} else {
	header('location: ./');
}
?>

