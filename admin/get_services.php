<?php
session_start();
require_once('../Connections/Conn.php'); ?>

<?php

//	$roww["sn"] .'__'. $roww["item_service"].'__'. $roww["hosp_price"].'__'. $roww["nhis_price"].'__'. $roww["insurance_type"].'__'. 
//$roww["dept"].'__'. $roww["category"].'__'. $roww["price_table"].'__'. $roww["duration"].'__'. $roww["file_amt"].'__'. $roww["specialist_id"];

if (isset($_POST['cat_id'])) {



	$cat_id = $_POST['cat_id'];
	$dept_id = $_POST['dept_id'];
	$part = explode("__", $cat_id);
	$speciaty = $part[10];

	//$dept_id=$part[5];
	///$where_string_1 = " h.Department='$dept_id' AND ";
	$where_string_1 = "";


	/*	if($dept_id =='more'){
		
				if($speciaty!=''){
						$speciaty_search = "and u.specialist='$speciaty'";
				}else{
					$speciaty_search='';
				}
		
		$dept_search = "";
				}else{
			$dept_search = "and h.Department='$dept_id'";
		}*/

	$dept_search = '';
	$speciaty_search = '';

	$stmt2 = $db->query("SELECT h.FirstName,h.LastName,h.Designation,u.username 
FROM hremp as h 
inner join admin_users as u on h.EmployeeCode=u.EmployeeCode WHERE (u.rights = 'DR' or u.rights = 'AD' or u.rights = 'MD' or h.Designation = 'Radiologist') 
AND h.status = '0' $dept_search $speciaty_search order by h.FirstName");
?>
	<option value="">.. Select ..</option>
	<?php
	while ($roww = $stmt2->fetch(PDO::FETCH_ASSOC)) { ?>

		<option value="<?php echo $roww["username"]; ?>"><?php echo $roww["FirstName"] . ' ' . $roww["LastName"] . '/ ' . $roww["Designation"]; ?></option>
	<?php }
} else if (isset($_POST['cat_id2'])) {

	$cat_id = $_POST['cat_id2'];
	$part = explode("__", $cat_id);
	$speciaty = $part[10];
	$stmt2 = $db->query("SELECT * FROM specialists where ID='$speciaty' order by name");
	while ($roww = $stmt2->fetch(PDO::FETCH_ASSOC)) { ?>
		<option value="<?php echo $roww["id"]; ?>" selected><?php echo $roww["name"]; ?></option>
<?php }
} else {
	header('location: ./');
}

?>