<?php

include("../Connections/Conn.php");
//include('../doctor/objects.php');
//include('../doctor/helpers.php');

session_start();

if (isset($_POST["view_results_list"])) {


	$show_print_button = '';
	$enter_results_id = $_POST["view_results_list"];

	if ($enter_results_id != 'null') {

		$pp = explode("____", $enter_results_id);
		$app_no = $pp[0];
		$hosp_no = $pp[1];
		$list_tests_sn = $pp[2];
		$list_tests_sn = substr_replace($list_tests_sn, "", -1);
		$th_arrays = explode(",", $list_tests_sn);
		$result = count($th_arrays);
?>
		<div id="display_view_results_list">
			<table class="table table-striped table-bordered table-hover dataTables-example">
				<thead>
					<tr>
						<th>#</th>
						<th>Dates</th>
						<th>Investigation</th>
						<th>Requester/Approver</th>
						<th>.</th>
					</tr>

				</thead>
				<tbody>

					<?php
					$n = 1;
					$result_status = '';


					for ($x = 0; $x < $result; $x++) {
						$test_sn = $th_arrays[$x];

						$stmt_list = $db->query("SELECT test_id,test_name,data_capture_status,result_date,labrequest_no,request_date,approved_by,request_by FROM lab_manage WHERE sn='$test_sn'");
						if ($stmt_list->rowCount() > 0) {
							$row = $stmt_list->fetch(PDO::FETCH_ASSOC);
							$data_capture_status = $row['data_capture_status'];
							$result_date = $row['result_date'];
							$request_date = $row['request_date'];
							$approved_by = $row['approved_by'];
							$request_by = $row['request_by'];
							$test_name = $row['test_name'];
							$labrequest_no = $row['labrequest_no'];
							$test_id = $row['test_id'];


							$Current_date = date("Y-m-d");
							$date1 = new DateTime($Current_date);
							$date2 = new DateTime($result_date);
							$diff = $date2->diff($date1);
							$day = $diff->format('%a');


							if ($result_date != '' and $data_capture_status != 'queue') {
								$rslt_date = date('d,M y h:i a', strtotime($result_date));
							} else {
								$rslt_date = '';
							}

							$rq_date = date('d,M y h:i a', strtotime($request_date));
						} else {
							$data_capture_status = '';
							$result_date = '';
							$request_date = '';
							$approved_by = '';
						}
					?>
						<tr>
							<td><?php echo $n; ?></td>
							<td><?php echo '<B>REQ: </B>' . $rq_date . '<br>' . '<B>APR: </B>' . $rslt_date; ?></td>
							<td><?php echo $test_name; ?></td>
							<td><?php echo $request_by . '<br>' . $approved_by; ?></td>

							<td>

								<?php if ($data_capture_status == 'approve') { ?>
									<input type="button" name="edit" value="View" onClick=" view_result_only_2('<?php echo $labrequest_no . '__' . $hosp_no . '__' . $test_id . '__' . $test_name; ?>')" class="btn btn-success btn-xs" />
								<?php } else { ?>
									<strong>Pending</strong>
								<?php } ?>
							</td>




						</tr>
					<?php $n++;
					} ?>

				</tbody>
			</table>





		</div>
	<?php } else { ?>

		<h2>No Investigation to Display</h2>

<?php }
}
?>

<div id="show_result_only_view_me"></div>

<script src="../js/vendors/editor/dist/trumbowyg.js"></script>
<script src="../js/vendors/editor/plugins/fontsize/trumbowyg.fontsize.js"></script>
<script src="../js/vendors/editor/plugins/colors/trumbowyg.colors.js"></script>

<script>
	function close_dashboard_2() {

		$("#display_view_results_list").show();
		$("#show_result_only_view_me").hide();
	}



	function view_result_only_2(details) {

		var close_me = 'view_result_list';

		$("#display_view_results_list").hide();
		toastr.warning('Wait Please ...', '', {
			timeOut: 2000
		})
		$.ajax({
			url: "enter_result_process.php",
			method: "POST",
			data: {
				show_result_only: details,
				close_me: close_me
			},
			success: function(data) {

				$("#show_result_only_view_me").html(data);
				$("#show_result_only_view_me").show();

			}
		});
	}




	display_alert_('<?php echo $hosp_no; ?>');

	function display_alert(hosp_no) {
		$.ajax({
			url: "../inc/set_alert_patients.php",
			method: "POST",
			data: {
				check_alert: hosp_no
			},
			success: function(data) {
				document.getElementById("patient-alert-tbody").innerHTML = data;
			}
		});
	}

	function display_alert_(hosp_no) {
		$.ajax({
			url: "../inc/set_alert_patients.php",
			method: "POST",
			data: {
				check_alert_2: hosp_no
			},
			success: function(data) {
				var json = JSON.parse(data);
				if (json["status"] == 0) {
					display_alert('<?php echo $hosp_no; ?>');

					toastr.error(json["data"], '', {
						closeButton: true,
						progressBar: true,
						preventDuplicates: false,
						positionClass: "toast-top-center",
						onclick: null,
						showDuration: 10000,
						hideDuration: 10000,
						timeOut: 30000,
						extendedTimeOut: 1000,
						showEasing: "swing",
						hideEasing: "linear",
						showMethod: "fadeIn",
						hideMethod: "fadeOut"
					})
				}
			}
		});
	}
</script>