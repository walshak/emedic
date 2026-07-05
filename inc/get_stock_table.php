<?php 
session_start();
require_once('../Connections/Conn.php'); ?>

<?php
if(isset($_POST['stock_combo_cat'])) {
				$stock_combo_cat=$_POST['stock_combo_cat'];
	$stmt2 = $db->query("SELECT distinct category 
		FROM stock_table WHERE navigation='$stock_combo_cat' and status='active' and category!='' order by category");
	echo "echo <option value=''>-- Select --</option>";
	?>
<option value="all">All</option>
    <?php    while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){ ?>
<option value="<?php  echo $row2["category"]; ?>"><?php  echo $row2["category"]; ?></option>

        <?php }	
	
}elseif(isset($_POST['category_item'])) {
			$category_item=$_POST['category_item'];
			
			if($category_item!='all'){
				$ext_searc=" and category='$category_item'";
				}else{
				$ext_searc="";
					}
			$stock_table=$_POST['stock_table'];
	$stmt2 = $db->query("SELECT * FROM stock_table 
	WHERE stock_table='$stock_table' $ext_searc and status='active' order by product_name");
	echo "echo <option value=''>-- Select --</option>";
        while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){ ?>
<option value="<?php  echo $row2["sn"] .'__'. $row2["product_name"] .'__'. $row2["category"]; ?>"><?php  echo $row2["product_name"]; ?></option>
        <?php }	
	
} else {
	header('location: ./');
}
?>
