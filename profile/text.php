<?php

if (isset($_POST["process_select_rq"])) {
	$pro_inv = $_POST['inv_all'];

	if (!empty($pro_inv)) {

		for ($i = 0; $i < count($pro_inv); $i++) {
			echo $inv_id = $pro_inv[$i];
		}
	} else {
		echo 'empty';
	}
}
