<?php
session_start();
include("../Connections/Conn.php");

$response_main = array(
	'batch_no' => '0',
	'status_result' => '0',
	'message' => ''
);

if (isset($_POST["check_batch_status"])) {

	$supplier_id   = $_POST['supplier_id'];
	$batch_number  = $_POST['batch_number'];
	$p_order_dept_id = $_POST['p_order_dept_id'];
	$p_order_dept = $_POST['p_order_dept_name'];

	if ($batch_number === 'new_po') {
		$batch_no = date('Y-m-d') . '-' . $supplier_id . $p_order_dept_id;
	} else {
		$batch_no = $batch_number;
	}

	$sql = "SELECT 1 FROM stock_table_procurment
            WHERE PO_dept_id = :PO_dept_id
              AND dept = :dept
              AND batch_no = :batch_no
              AND supplier_id = :supplier_id
            LIMIT 1";

	$stmt = $db->prepare($sql);
	$stmt->execute([
		':PO_dept_id' => $p_order_dept_id,
		':dept'       => $_SESSION['dept_id'],
		':batch_no'   => $batch_no,
		':supplier_id' => $supplier_id
	]);

	$exists = $stmt->fetch();

	echo json_encode([
		'status_result' => $exists ? 1 : 0
	]);
	exit;
}


if (isset($_POST["stock_name"])) {

	if ($_SESSION['dept_id'] != '' and $_SESSION['fullname'] != '') {
		$yes = 'yes';
		$stock_name = $_POST["stock_name"];
		$product_name = $_POST["stock_name"];
		$new_qty = $_POST["order_qty_"];
		$buying_cost = $_POST["buying_cost_"];
		$total = $_POST["total_"];
		$supplier_id = $_POST['supplier_id'];
		$stock_table = $_POST['stock'];
		$stock_sn = $_POST['stock_sn_'];
		$navigation = $_POST['navigation'];
		$stock_total_unit_ = $_POST['stock_total_unit_'];
		$generated_po = $_POST['generated_po'];
		$p_order_dept_id = $_POST['p_order_dept_id'];
		$p_order_dept = $_POST['p_order_dept_name'];
		$batch_number = $_POST['batch_number'];
		$status_result = $_POST['status_result'];
		$setdate = date('Y-m-d');


		if ($batch_number == 'new_po') {  /////////

			if ($status_result == 1) {
				$random_number = rand(1000, 9999);
				$batch_no = $setdate . '-' . $supplier_id . $p_order_dept_id . '-' . $random_number;
			} else {
				$setdate = date('Y-m-d');
				$batch_no = $setdate . '-' . $supplier_id . $p_order_dept_id;
			}
		} else {
			$batch_no = $batch_number;

			if ($p_order_dept === 'Search and Select Items') {

				$stmtv = $db->prepare(
					'SELECT PO_dept_name,PO_dept_id 
         FROM stock_table_procurment
         WHERE batch_no = :batch_no
         ORDER BY sn DESC
         LIMIT 1'
				);

				$stmtv->bindParam(':batch_no', $batch_no);
				$stmtv->execute();

				$rwxx = $stmtv->fetch(PDO::FETCH_ASSOC);

				if ($rwxx) {
					$p_order_dept = $rwxx['PO_dept_name'];
					$p_order_dept_id = $rwxx['PO_dept_id'];
				}
			}
		}


		if ($stock_table == 'Pharmacy') {
			$stock = 'p';
		} else {
			$stock = 'o';
		}

		$inven_desc = 'Procurement/PO';
		$all_product = '';


		$stmtv = $db->prepare('SELECT purchase_price, order_qty, order_date, total_cost, price_per_unit FROM stock_table_procurment 
		WHERE stock_sn = :stock_sn AND status = :status ORDER BY sn DESC LIMIT 1');
		$stmtv->bindParam(':stock_sn', $stock_sn);
		$stmtv->bindParam(':status', $yes);
		$stmtv->execute();

		if ($stmtv->rowCount() > 0) {
			$rwxx = $stmtv->fetch(PDO::FETCH_ASSOC);

			$l_buying_cost = $rwxx['purchase_price'];
			$l_order_qty = $rwxx['order_qty'];
			$l_order_date = $rwxx['order_date'];
			$l_total_cost = $rwxx['total_cost'];
			$l_price_per_unit = $rwxx['price_per_unit'];
		} else {
			$l_buying_cost = 0;
			$l_order_qty = 0;
			$l_order_date = '';
			$l_total_cost = '0';
			$l_price_per_unit = $stock_total_unit_;
		}
		if ($buying_cost <= 0 and $l_buying_cost > 0) {
			$buying_cost = $l_buying_cost;
		}

		$total_cost = $new_qty * $buying_cost;
		$status = "no";

		$sql = "SELECT * FROM stock_table_procurment 
        WHERE stock_sn = :stock_sn 
          AND status = :status 
          AND dept = :dept 
          AND batch_no = :batch_no 
          AND order_by = :order_by 
          AND pay_status = 0";

		// Prepare the statement
		$stmt = $db->prepare($sql);

		// Bind the parameters
		$stmt->bindValue(':stock_sn', $stock_sn, PDO::PARAM_STR);
		$stmt->bindValue(':status', $status, PDO::PARAM_STR);
		$stmt->bindValue(':dept', $_SESSION['dept_id'], PDO::PARAM_STR);
		$stmt->bindValue(':batch_no', $batch_no, PDO::PARAM_STR);
		$stmt->bindValue(':order_by', $_SESSION['fullname'], PDO::PARAM_STR);

		// Execute the statement
		$stmt->execute();

		// Check the row count
		if ($stmt->rowCount() == 0 && $_POST['supplier_id'] != '' && $new_qty > 0) {


			try {
				$db->beginTransaction();

				// Prepare the INSERT statement
				$insertSQL = "INSERT INTO stock_table_procurment (stock_sn, order_by, order_date, last_order_qty, last_order_date, last_purchase_price, 
		last_order_total_cost, order_qty, stock_name, stock_table, purchase_price, price_per_unit, total_cost, dept, PO_dept_name,PO_dept_id, batch_no, supplier_id, remarks, navigation) 
		VALUES (:stock_sn, :order_by, :order_date, :last_order_qty, :last_order_date, :last_purchase_price, :last_order_total_cost, 
		:order_qty, :stock_name, :stock_table, :purchase_price, :price_per_unit, :total_cost, :dept, :PO_dept_name,:PO_dept_id, :batch_no, :supplier_id, :remarks, :navigation)";

				// Prepare the statement
				$stmt = $db->prepare($insertSQL);

				// Bind the parameters
				$stmt->bindValue(':stock_sn', $stock_sn, PDO::PARAM_STR);
				$stmt->bindValue(':order_by', $_SESSION['fullname'], PDO::PARAM_STR);
				$stmt->bindValue(':order_date', $setdate, PDO::PARAM_STR);
				$stmt->bindValue(':last_order_qty', $l_order_qty, PDO::PARAM_STR);
				$stmt->bindValue(':last_order_date', $l_order_date, PDO::PARAM_STR);
				$stmt->bindValue(':last_purchase_price', $buying_cost, PDO::PARAM_STR);
				$stmt->bindValue(':last_order_total_cost', $l_total_cost, PDO::PARAM_STR);
				$stmt->bindValue(':order_qty', $new_qty, PDO::PARAM_STR);
				$stmt->bindValue(':stock_name', $product_name, PDO::PARAM_STR);
				$stmt->bindValue(':stock_table', $stock_table, PDO::PARAM_STR);
				$stmt->bindValue(':purchase_price', $buying_cost, PDO::PARAM_STR);
				$stmt->bindValue(':price_per_unit', $l_price_per_unit, PDO::PARAM_STR);
				$stmt->bindValue(':total_cost', $total_cost, PDO::PARAM_STR);
				$stmt->bindValue(':dept', $_SESSION['dept_id'], PDO::PARAM_STR);
				$stmt->bindValue(':PO_dept_name', $p_order_dept, PDO::PARAM_STR);
				$stmt->bindValue(':PO_dept_id', $p_order_dept_id, PDO::PARAM_STR);
				$stmt->bindValue(':batch_no', $batch_no, PDO::PARAM_STR);
				$stmt->bindValue(':supplier_id', $_POST['supplier_id'], PDO::PARAM_STR);
				$stmt->bindValue(':remarks', $inven_desc, PDO::PARAM_STR);
				$stmt->bindValue(':navigation', $navigation, PDO::PARAM_STR);

				// Execute the statement
				$stmt->execute();

				$updateSQL = "UPDATE stock_table SET supplier_id = :supplier_id, buying_cost = :buying_cost WHERE sn = :sn";
				$upd_stmt = $db->prepare($updateSQL);

				// Bind the parameters
				$upd_stmt->bindValue(':supplier_id', $_POST["supplier_id"], PDO::PARAM_STR);
				$upd_stmt->bindValue(':buying_cost', $buying_cost, PDO::PARAM_STR);
				$upd_stmt->bindValue(':sn', $inv_id, PDO::PARAM_STR);

				$upd_stmt->execute();

				// Commit transaction
				$db->commit();

				$response_main['batch_no'] = $batch_no;
				$response_main['message'] = 'Added Successfully!';
			} catch (PDOException $e) {
				// Rollback transaction
				$db->rollBack();

				$response_main['batch_no'] = $batch_no;
				$response_main['message'] = 'Error: ' . $e->getMessage();
			}

			echo  json_encode($response_main);
			exit;
		} else {
			$response_main['batch_no'] = $batch_no;
			$response_main['message'] = 'Item Selected Already Exist!' . $status_result;
		}
		echo  json_encode($response_main);
		exit;
	} else {

		$response_main['batch_no'] = $batch_no;
		$response_main['message'] = 'Logout And Re-login Again';
		echo  json_encode($response_main);
		exit;
	}
}


if (isset($_POST['supplier_id'])) {

	$supplier_id = $_POST['supplier_id'];

	$stmt2 = $db->query("SELECT batch_no FROM stock_table_procurment where supplier_id='$supplier_id' and status='no' GROUP BY batch_no ORDER BY MAX(sn) DESC");
?>
	<option value="">-- Select --</option>
	<?php
	while ($roww = $stmt2->fetch(PDO::FETCH_ASSOC)) { ?>
		<option value="<?php echo $roww["batch_no"]; ?>"><?php echo $roww["batch_no"]; ?></option>
	<?php } ?>
	<option value="new_po">New PO</option>

<?php
}




if (isset($_POST["item_to_delete"])) {
	$del = $_POST['item_to_delete'];
	$stmt = $db->prepare('DELETE FROM stock_table_procurment WHERE sn = :sn');
	$stmt->bindParam(':sn', $del);
	$stmt->execute();

	if ($stmt->rowCount() > 0) {
		echo 'Deleted';
	} else {
		echo 'Unable to Delete';
	}
}


if (isset($_POST["batch_no"])) {

	$batch_no = $_POST["batch_no"];

?>

	<h3 style="color: blue;">PURCHASE ORDER BATCH NUMBER: <?= $batch_no; ?></h3>
	<table class="table table-striped">
		<thead>
			<tr>
				<th>No</th>
				<th>Item</th>
				<th>Last Price</th>
				<th>Buying Price</th>
				<th>Quantity</th>
				<th>Total</th>
				<th>Delete</th>
			</tr>
		</thead>
		<tbody>
			<?php

			$n = 1;
			$total_ordered = 0;
			$stmt_up = $db->query("SELECT * FROM stock_table_procurment WHERE batch_no='$batch_no'");
			while ($row_up = $stmt_up->fetch(PDO::FETCH_ASSOC)) {
				$total_ordered = $total_ordered + $row_up['total_cost'];
			?>
				<tr>
					<td><?php echo $n; ///['stock_name']; 
						?></td>
					<td><?php echo $row_up['stock_name']; ?></td>
					<td><?php echo number_format($row_up['last_purchase_price']); ?></td>
					<td><?php echo number_format($row_up['purchase_price']); ?></td>
					<td><?php echo $row_up['order_qty']; ?></td>
					<td><?php echo number_format($row_up['total_cost']); ?></td>
					<td><button class="btn btn-danger btn-xs" name="" onClick="remove_order('<?= $row_up['sn']; ?>')">&nbsp;-&nbsp;</button>
					</td>

				</tr>

			<?php $n++;
			}   ?>
			<tr>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td>
					<h3>Grand Total:</h3>
				</td>
				<td>
					<h3><?= number_format($total_ordered); ?></h3>
				</td>
			</tr>
	</table>

<?php
}
