<?php

if (isset($_POST["search_detials"])) {
	include("Connections/Conn.php");

	$search_detials = trim($_POST["search_detials"]);
	$search_detials2 = trim($_POST["search_detials"]);
	$search_detials_split = $search_detials;
	$search_detials = "%$search_detials%";
	$length_search = strlen($search_detials);
	$length_search = strlen($search_detials2);


	$word_count = str_word_count($search_detials);
	$partt = explode(" ", $search_detials_split);
	if (count($partt) == 1) {

		if (preg_match("/[a-z]/i", $search_detials)) {

			$query = $db->prepare("SELECT e.hospital_no, e.surname, e.fname, e.phone, e.oname, i.insurance_name  
								   FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
									   WHERE surname like :surname or fname like :fname or oname like :oname");
			$query->bindParam(':surname', $search_detials);
			$query->bindParam(':fname', $search_detials);
			$query->bindParam(':oname', $search_detials);
		} elseif ($length_search >= 3  && $length_search <= 6) {
			$query = $db->prepare("SELECT e.hospital_no, e.surname, e.fname,e.phone , e.oname, i.insurance_name  
									FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
									WHERE e.hospital_no like :hospital_no or e.old_hospital_no like :old_hospital_no");
			$query->bindParam(':hospital_no', $search_detials);
			$query->bindParam(':old_hospital_no', $search_detials);
		} elseif ($length_search >= 10) {
			$query = $db->prepare("SELECT e.hospital_no, e.surname,e.fname,e.phone , e.oname, i.insurance_name  
									FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no WHERE e.phone like :phone");
			$query->bindParam(':phone', $search_detials);
		} else {
			echo 'Search Text Specified Not Available!';
			exit;
		}

		$query->execute();
	} else if (count($partt) == 2) {

		$partt = explode(" ", $search_detials_split);
		$name1 = $partt[0];
		$name2 = $partt[1];
		$name1 =  "$name1%";
		$name2 =  "$name2%";

		$query = $db->prepare("SELECT e.hospital_no, e.surname, e.fname, e.phone, e.oname, i.insurance_name  
							   FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
							   WHERE 
								   (e.surname LIKE :name1 AND e.fname LIKE :name2)  OR
								   (e.surname LIKE :name3 AND e.fname LIKE :name4)  OR
								   (e.oname LIKE :name5 AND e.surname LIKE :name6) OR
								   (e.oname LIKE :name7 AND e.surname LIKE :name8) OR
								   (e.fname LIKE :name9 AND e.oname LIKE :name10) OR 
								   (e.fname LIKE :name11 AND e.oname LIKE :name12) OR
								   (e.fname LIKE :name13 AND e.oname LIKE :name14) 
								   
								   ");

		$query->bindParam(':name1', $name1);
		$query->bindParam(':name2', $name2);
		$query->bindParam(':name3', $name2);
		$query->bindParam(':name4', $name1);
		$query->bindParam(':name5', $name1);
		$query->bindParam(':name6', $name2);
		$query->bindParam(':name7', $name2);
		$query->bindParam(':name8', $name1);
		$query->bindParam(':name9', $name1);
		$query->bindParam(':name10', $name2);
		$query->bindParam(':name11', $name2);
		$query->bindParam(':name12', $name1);
		$query->bindParam(':name13', $search_detials_split);
		$query->bindParam(':name14', $search_detials_split);
		$query->execute();
	} else if (count($partt) == 3) {

		$partt = explode(" ", $search_detials_split);
		$name1 = $partt[0];
		$name2 = $partt[1];
		$name3 = $partt[2];



		$name1 =  "$name1%";
		$name2 =  "$name2%";
		$name3 =  "$name3%";

		$query = $db->prepare("SELECT e.hospital_no, e.surname, e.fname, e.phone, e.oname, i.insurance_name  
							   FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
							   WHERE 
								   (e.fname LIKE :name2 AND e.surname LIKE :name1 AND e.oname LIKE :name3)  OR
								   (e.fname LIKE :name4 AND e.surname LIKE :name5 AND e.oname LIKE :name6)  OR
								   (e.fname LIKE :name7 AND e.surname LIKE :name8 AND e.oname LIKE :name9)  OR
								   (e.fname LIKE :name10 AND e.surname LIKE :name11 AND e.oname LIKE :name12)  OR
								   (e.fname LIKE :name13 AND e.surname LIKE :name14 AND e.oname LIKE :name15)  OR
								   (e.fname LIKE :name16 AND e.surname LIKE :name17 AND e.oname LIKE :name18)  OR
	   
								   (e.surname LIKE :name22 AND e.oname LIKE :name23 ) OR
								   (e.surname LIKE :name24 AND e.fname LIKE :name25 ) OR
								   (e.fname LIKE :name26 AND e.surname LIKE :name27 ) OR
								   (e.fname LIKE :name28 AND e.oname LIKE :name29 ) OR
								   (e.oname LIKE :name30 AND e.fname LIKE :name31 ) OR
								   (e.oname LIKE :name32 AND e.surname LIKE :name33 ) 
								   
								   ");

		// surname fname oname : 1 2 3
		$query->bindParam(':name1', $name2);
		$query->bindParam(':name2', $name1);
		$query->bindParam(':name3', $name3);

		// fname surname oname 
		$query->bindParam(':name4', $name1);
		$query->bindParam(':name5', $name2);
		$query->bindParam(':name6', $name3);
		//
		//  oname fname surname
		$query->bindParam(':name7', $name2);
		$query->bindParam(':name8', $name3);
		$query->bindParam(':name9', $name1);

		//  fname  oname surname
		$query->bindParam(':name10', $name1);
		$query->bindParam(':name11', $name3);
		$query->bindParam(':name12', $name2);

		// surname oname fname 
		$query->bindParam(':name13', $name3);
		$query->bindParam(':name14', $name1);
		$query->bindParam(':name15', $name2);

		//  oname  surname fname
		$query->bindParam(':name16', $name3);
		$query->bindParam(':name17', $name2);
		$query->bindParam(':name18', $name1);

		$name22 = $name2 . ' ' . $name1;
		$name23 = $name3;
		$name24 = $name2 . ' ' . $name3;
		$name25 = $name1;
		$name26 = $name1 . ' ' . $name2;
		$name27 = $name3;
		$name28 = $name1 . ' ' . $name3;
		$name29 = $name2;
		$name30 = $name3 . ' ' . $name2;
		$name31 = $name2;
		$name32 = $name3 . ' ' . $name1;
		$name33 = $name1;

		$query->bindParam(':name22', $name21); // sf :21
		$query->bindParam(':name23', $name22); // o : 3
		$query->bindParam(':name24', $name23); // so :23
		$query->bindParam(':name25', $name24); // f : 1
		$query->bindParam(':name26', $name25); // fs :12
		$query->bindParam(':name27', $name26); // o  : 3
		$query->bindParam(':name28', $name27); // fo 13
		$query->bindParam(':name29', $name28); // s : 2
		$query->bindParam(':name30', $name29); // os : 32
		$query->bindParam(':name31', $name30); // f : 1
		$query->bindParam(':name32', $name31); // os :32
		$query->bindParam(':name33', $name32); // o  : 1
		$query->execute();
	} else {

		$partt = explode(" ", $search_detials_split);
		$query = $db->prepare("SELECT e.hospital_no, e.surname,fname, e.phone , e.oname, i.insurance_name  
						   FROM enrollee e INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
							   WHERE e.surname like :surname and e.fname like :fname");
		$query->bindParam(':surname', $partt[0]);
		$query->bindParam(':fname', $partt[1]);
		$query->bindParam(':fname', $partt[1]);
		$query->execute();
	}





	if ($query->rowCount() > 0) {

		if ($query->rowCount() == 1) {
			header('Content-Type: application/json');

			$roww = $query->fetch(PDO::FETCH_ASSOC);
			echo json_encode(["status" => 200, "redirect" => true, "hosp_no" => $roww['hospital_no']]);
			exit;
		}

?>


		<!-- 				<input type="text" id="search_input" placeholder="Narrow Search" class="form-control">
	   -->
		<table class="table table-striped" id="search_table">
			<thead>
				<tr>
					<th data-toggle="true">Hospital No</th>
					<th data-toggle="true">Surname</th>
					<th data-toggle="true">First Name</th>
					<th data-toggle="true">Other Name</th>
					<th data-toggle="true">Phone</th>
					<th data-toggle="true">Insurance</th>
					<th data-toggle="true">Action</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$n = 1;
				while ($roww = $query->fetch(PDO::FETCH_ASSOC)) {
				?>
					<tr>
						<td><?php echo $hospital_no = $roww['hospital_no']; ?></td>
						<td><?php echo $roww['surname']; ?></td>
						<td><?php echo $roww['fname']; ?></td>
						<td><?php echo $roww['oname']; ?></td>
						<td><?php echo $roww['phone']; ?></td>
						<td><?php echo $roww['insurance_name']; ?></td>
						<td>
							<a href="patient.php?hosp_no=<?= $roww['hospital_no']; ?>" class="btn btn-primary btn-xs"><i class="fa fa-search-plus"></i> &nbsp;Go</a>
						</td>

					</tr>
				<?php } ?>
			</tbody>
		</table>

<?php   } else {
		echo '<div class="alert alert-danger">
    <h3>PATIENT NOT FOUND. PRESS THE ESC KEY OR CLICK ANYWHERE TO CLOSE THE SCREEN AND TRY AGAIN.</h3>
</div>';
		exit;
	}
	exit;
}


?>
<h2>General Patient Search</h2><strong></strong>

<div class="form_sep">
	<label for="reg_input_no" class="">(Name or Phone or Hospital Number)</label>
	<input type="text" id="search" name="search" class="form-control" style="border-color: black;" required>
</div>
<br>
<div class="form_sep">
	<button type="submit" class="btn btn-primary btn btn-sm" name="apply_action" id="apply_action" onclick="search_patient_x()"><i class="fa fa-search"></i>&nbsp;Apply Search</button>
</div>



<div class="modal inmodal fade" id="patient_search_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Patient Search Modal</h4>
			</div>
			<div class="modal-body" id="patient_search_body">

			</div>
		</div>
	</div>
</div>