<?php
include("../inc/session.php");
include("../Connections/Conn.php");
include('../doctor/objects.php');
include('../doctor/helpers.php');

$error_status = null;
$inbox = null;
$labels_string = null;
$data_string = null;
$data_string2 = null;
$count_draft = null;
$labels_string_tem = null;
$data_string_tem = null;
$labels_string_weight = null;
$labels_string_weight = null;
$labels_string_height = null;
$data_string_weight = null;
$qty_err  = null;
$display_status = null;
$templates = [];
$clinical_count = 0;

if (isset($_POST['open_patient_page'])) {
	$hospital_no = $_POST['hosp_no'];

?>
	<script type="text/javascript">
		location = "patient.php?hosp_no=<?= $hospital_no; ?>";
	</script>
<?php
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


?>

<!DOCTYPE html>
<html>
<?php
include("../inc/header.php");
include_once("../inc/alert_msg.php");
?>

<title>WebMedic | <?php if ($_SESSION['Designation'] != "") {
						echo $_SESSION['Designation'];
					} else {
						echo $_SESSION['speciality'];
					} ?></title>


<body>
	<div id="wrapper">


		<?php include("nav_side.php"); ?>

		<div id="page-wrapper" class="gray-bg">

			<?php include("nav_header.php"); ?>

			<div class="wrapper wrapper-content">

				<?php

				if (isset($_GET['vip'])) {
					$error_status = 3;
					$error_msg = "Patient designated as VIP";
				}

				if (strtoupper($_SESSION['password']) == 'STAFF123') {
					header("location:../profile/index.php?profile=$uname&changepassword");
				}

				if (isset($_GET['hosp_no'])) {
					header('location:patient.php?hosp_no=' . $_GET['hosp_no']);
				} elseif (isset($_GET['procedure'])) {
					include("../doctor/procedures/index.php");
				} elseif (isset($_GET['transplant'])) {
					include("../doctor/_transplant.php");
				} elseif (isset($_GET['rpt'])) {
					include("report.php");
				} elseif (isset($_GET['print'])) {
					include("print.php");
				} else {
					include("nurse.php");
				}
				?>
			</div>

			<div class="modal inmodal fade" id="staff_alert_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							<h4 class="modal-title" id="">Staff Alert !</h4>
						</div>

						<div class="modal-body" id="claims_body">

							<?php if ($display_status == 1) { ?>

								<form action="index.php" method="POST">

									<p><?php echo $my_note; ?></p>

									<button type="submit" name="read_msg" class="btn btn-primary">Yes, I have read it</button>
								</form>

							<?php } ?>
						</div>
					</div>
				</div>
			</div>



			<?php include('../modal_lock.php'); ?>

			<?php include("../inc/footer.php"); ?>
		</div>
	</div>



	<?php include("../inc/footer_scripts.php"); ?>


	</script>





	<script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
	<script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
	<script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
	<script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>


	<script>
		queue_list();

		function queue_list_display() {
			var vital_date = document.getElementById("vital_date").value;

			toastr.warning('Please wait ... Data loading ... ', 'Wait', {
				timeOut: 5000
			})
			$.ajax({
				url: "_patient_on_queue_table.php",
				method: "POST",
				data: {
					vital_date: vital_date
				},
				success: function(data) {
					toastr.clear();

					$('#_patient_on_queue_table').html(data);
				}
			});

		}


		<?php if ($error_status == 1) { ?>
			toastr.error('<?php echo $error_msg ?>', 'Error', {
				timeOut: 5000
			})

		<?php } else if ($error_status == 2) {
		?>
			toastr.success(' <?php echo $error_msg ?> ', 'Success', {
				timeOut: 5000
			})
		<?php } else if ($error_status == 3) {
		?>
			toastr.error(' <?php echo $error_msg ?> ', 'ATTENTION', {
				timeOut: 8000
			})
		<?php

		} ?>

		function queue_list() {
			var patient_on_queue;
			toastr.warning('Please wait ... Data loading ... ', 'Wait', {
				timeOut: 5000
			})
			$.ajax({
				url: "_patient_on_queue_table.php",
				method: "POST",
				data: {
					patient_on_queue: true
				},
				success: function(data) {
					toastr.clear();

					$('#_patient_on_queue_table').html(data);
				}
			});
		}



		function patient_on_icu_hdu(input) {
			var patient_on_icu_hdu_link;
			toastr.warning('Please wait ... Data loading ... ', 'Wait', {
				timeOut: 5000
			})
			$.ajax({
				url: "_patients_on_icu_hdu.php",
				method: "POST",
				data: {
					patient_on_icu_hdu_link: input
				},
				success: function(data) {
					toastr.clear();

					if (input == 'icu') {
						$('#patient_on_icu_hdu_').html(data);
					} else {
						$('#patient_on_icu_hdu_2').html(data);
					}
				}
			});
		}

		$('.dataTables-example').dataTable({
			responsive: true,
			"dom": 'T<"clear">lfrtip',
			"tableTools": {
				"sSwfPath": "../js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
			}
		});

		$('#data_5 .input-daterange').datepicker({
			keyboardNavigation: false,
			forceParse: false,
			autoclose: true
		});


		$(document).ready(function() {
			$("#inv_item").hide();
			$("#staff_status").hide();
			$("#rhmo_id").hide();
			$("#adm_type").hide();

			$('#rpt_type').on('change', function() {

				$("#staff_status").show();

				if (this.value == 'inv_drug') {
					$("#inv_item").show();
					$("#staff_status").show();
					$("#rhmo_id").hide();
					$("#adm_type").hide();
				}

				if (this.value == 'rhmo') {
					$("#inv_item").hide();
					$("#rhmo_id").show();
					$("#staff_status").show();
					$("#adm_type").hide();
				}

				if (this.value == 'adm') {
					$("#inv_item").hide();
					$("#rhmo_id").hide();
					$("#staff_status").hide();
					$("#adm_type").show();

				} else {

					$("#adm_type").hide();
				}

				if (this.value != 'rhmo' && this.value != 'inv_drug') {
					$("#inv_item").hide();
					$("#rhmo_id").hide();
					//$("#staff_status").show();
					//$("#adm_type").hide();

				}
			});
		});
	</script>

	<?php
	include_once('../search_patient_code_scripts.php');
	include_once('../inc/procedure_attach_to_file.php');
	?>



	<?php if ($display_status == 1) { ?>
		<script>
			$(document).ready(function() {
				$("#staff_alert_modal").modal('show');
			});
		</script>
	<?php  } ?>




	</script>

	<script src="../js/idle.js"></script>
	<script src="../inc/procedure_script.js"></script>
</body>

</html>