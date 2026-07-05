<?php
include("../inc/session.php");
include("../Connections/Conn.php");
?>


<!DOCTYPE html>
<html>
<?php
include('../inc/header.php');

define('UPLOADPATH', '../uploads/staff/');
define('MAXFILESIZE', 300000);

$upload_type = $_POST["upload_type"];
$username = $_POST["username"];
$filename = $upload_type . '_' . $_POST["username"];

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "passport")) {

	$file_foto_type = $_FILES['file_foto']['type'];
	$file_foto_size = $_FILES['file_foto']['size'];
	$TempSrc = $_FILES['file_foto']['tmp_name']; // Temp name of image file stored in PHP tmp folder

	//if(($file_foto_size < 0) || ($file_foto_size > MAXFILESIZE)){
	//$complain="The size of the passport should not be more than 150kb";
	//		$msg_id='1';
	//		header ("Location: index.php?profile=$username&id=1");

	//exit;
	if (($file_foto_type == 'image/jpeg') || ($file_foto_type == 'image/jpg')) {
		$pixid = $filename . '.' . 'jpg';
		$target = UPLOADPATH . $pixid; //generate the destination path
		move_uploaded_file($_FILES['file_foto']['tmp_name'], $target);
		header("Location: index.php?profile=$username&id=0");
		//exit;
	} else {
		//$complain="The Passport must be in jpeg format";
		header("Location: index.php?profile=$username&id=2");
		exit;
	}
}





?>

<body>

	<div id="wrapper">

		<?php include("nav_side.php"); ?>
		<div id="page-wrapper" class="gray-bg">

			<?php include("nav_header.php");

			if (isset($_SESSION['username'])) {
				$profile = $_SESSION['username'];

				$stmt = $db->query("SELECT * FROM admin_users WHERE username='$profile'");
				if ($stmt->rowCount() > 0) {
					$row = $stmt->fetch(PDO::FETCH_ASSOC);
					$my_tips = $row['my_tips'];
				} else {
					header("location:index.php");
				}
			}
			?>

			<div class="wrapper wrapper-content">


				<?php
				if (isset($_GET['profile'])) {
					include('profile.php');
				} elseif (isset($_GET['LV'])) {
					include('leave.php');
				} elseif (isset($_GET['yLV'])) {
					include("apLeav.php");
				} elseif (isset($_GET['rLV'])) {
					include("rptLV.php");
				} elseif (isset($_GET['evalCat'])) {
					include('eval_cat.php');		//new code evaluation
				} elseif (isset($_GET['eval'])) {
					include('evaluation.php');		//new code evaluation
				} elseif (isset($_GET['rEval'])) {
					include('eval_rep.php');		//new code evaluation
				} elseif (isset($_GET['evalPeriod'])) {
					include('eval_period.php');		//new code evaluation	*/	
				}


				include('../modal_lock.php');
				?>



			</div>


			<?php include("../inc/footer.php"); ?>

		</div>
	</div>

	<?php include("../inc/footer_scripts.php"); ?>

	<?php if ($_GET['id'] == '0') { ?>
		<script>
			toastr.success('Uploaded Successfully!', 'Uploaded', {
				timeOut: 2000
			})
		</script>
	<?php } ?>

	<?php if ($_GET['id'] == '1') { ?>
		<script>
			toastr.error('The size of the Passport/Signature should not be more than 150kb', 'Error', {
				timeOut: 5000
			})
		</script>
	<?php } ?>

	<?php if ($_GET['id'] == '2') { ?>
		<script>
			toastr.error('The Passport/Signature must be in jpeg format', 'Error', {
				timeOut: 5000
			})
		</script>
	<?php } ?>

	<?php if (isset($_GET['sv']) or $sv == '1') { ?>
		<script>
			toastr.success('Successfully', 'Success', {
				timeOut: 5000
			})
		</script>
	<?php } ?>



	<script>
		<?php if (isset($_GET['changepassword'])) { ?>
			$('#password_modal').modal('show');
		<?php } ?>

		$('.chosen-select').chosen({
			width: "100%"
		});


		$('#data_1 .input-group.date').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true
		});


		$('#timepicker1').timepicker({});
		$('#timepicker2').timepicker({});

		$(document).ready(function() {
			$('.i-checks').iCheck({
				checkboxClass: 'icheckbox_square-green',
				radioClass: 'iradio_square-green',
			});


			$('.summernote').summernote();

		});


		$(".chosen-select").chosen({
			allow_single_deselect: true,
			enable_search_threshold: 10,
			no_results_text: 'Oops, nothing found!',
			width: "100%"
		});
		$('.chosen-drop').css({
			"width": "100%",
			"white-space": "nowrap"
		})


		var edit = function() {
			$('.click2edit').summernote({
				focus: true
			});
		};
		var save = function() {
			var aHTML = $('.click2edit').code(); //save HTML If you need(aHTML: array).
			$('.click2edit').destroy();
		};


		$(document).on('click', '.add_tips', function() {
			// $('.modal-content').empty();
			//$(this).removeData('bs.modal'); 
			$('#tips_form')[0].reset();
			$('#note').val("");
			$('#tips_modal').modal('show');

		});

		$(document).on('click', '.change_password', function() {
			$('#password_form')[0].reset();
			$('#password_modal').modal('show');

		});


		$(document).on('click', '.edit_profile', function() {
			$('#profile_form')[0].reset();
			$('#profile_modal').modal('show');

		});

		$(document).on('click', '.change_ppt', function() {
			//	$('#passport_form')[0].reset(); 
			$('#passport_modal').modal('show');

		});

		$(document).on('click', '.what_todo', function() {
			//	$('#passport_form')[0].reset(); 
			$('#what_todo_modal').modal('show');

		});

		$('#tips_form').on("submit", function(event) {
			event.preventDefault();

			var username = document.getElementById("username").value;

			//   if($('#msg').val() == "")  
			//  {  
			//       alert("Tips Required");  
			//  }  
			//    else  
			//  {  
			$.ajax({
				url: "insert_profile.php",
				method: "POST",
				data: $('#tips_form').serialize(),
				beforeSend: function() {
					$('#save').val("Saving");
				},
				success: function(data) {
					//alert({ title: 'Success!', text: 'Save Successfully', timer: 250 })
					$('#msg').val("");
					$('#tips_modal').modal('hide');
				},
				complete: function() {
					$('#save').val("Saved");
					location.href = "index.php?profile=" + username;
				},
				error: function(data) {

					alert("Oops...", "Something went wrong :(", "error");
					alert({
						title: 'Oops...!',
						text: 'Something went wrong ',
						type: 'error',
						timer: 500
					})
				}
			});

			//   }
		});


		$('#password_form').on("submit", function(event) {
			event.preventDefault();

			var text = document.getElementById("password2").value;
			let length = text.length;


			if ($('password2').val() == "") {
				alert("Password Required");
			} else if ($('#re_password').val() == "") {
				alert("Re-type Password Required");
			} else if ($('#re_password').val() != $('#password2').val()) {
				alert("The Two password entered are not the same");
			} else if (length < 8) {

				alert("Password must be atleast 8 character or more");
			} else {
				$.ajax({
					url: "insert_profile.php",
					method: "POST",
					data: $('#password_form').serialize(),
					beforeSend: function() {
						$('#save').val("Saving");
					},
					success: function(data) {
						//alert({ title: 'Success!', text: 'Save Successfully', timer: 250 })
						$('#msg').val("");
						$('#password_modal').modal('hide');
					},
					complete: function() {
						$('#save').val("Saved");
						alert('Saved Successfully ... System Automatically Logout to Re-login ... ')
						location.href = "../logout.php";

					},
					error: function(data) {

						alert("Oops...", "Something went wrong :(", "error");
						alert({
							title: 'Oops...!',
							text: 'Something went wrong ',
							type: 'error',
							timer: 500
						})
					}
				});

			}
		});


		$('#profile_form').on("submit", function(event) {
			event.preventDefault();
			var username = document.getElementById("username").value;

			if ($('#title').val() == "") {
				alert("Title Required");
			} else if ($('#name').val() == "") {
				alert("Name Required");
			} else if ($('#about_me').val() == "") {
				alert("About Me Required");
			} else {
				$.ajax({
					url: "insert_profile.php",
					method: "POST",
					data: $('#profile_form').serialize(),
					beforeSend: function() {
						$('#save').val("Saving");
					},
					success: function(data) {
						//alert({ title: 'Success!', text: 'Save Successfully', timer: 250 })
						$('#msg').val("");
						$('#profile_modal').modal('hide');
					},
					complete: function() {
						$('#save').val("Saved");
						location.href = "index.php?profile=" + username;
					},
					error: function(data) {

						alert("Oops...", "Something went wrong :(", "error");
						alert({
							title: 'Oops...!',
							text: 'Something went wrong ',
							type: 'error',
							timer: 500
						})
					}
				});

			}
		});


		$('#what_todo_form').on("submit", function(event) {
			event.preventDefault();
			var username = document.getElementById("username").value;

			if ($('#note').val() == "") {
				alert("Short Note Required");
			} else if ($('#note_time').val() == "") {
				alert("Note time Required");
			} else if ($('#note_date').val() == "") {
				alert("Note date Required");
			} else {
				$.ajax({
					url: "insert_profile.php",
					method: "POST",
					data: $('#what_todo_form').serialize(),
					beforeSend: function() {
						$('#save').val("Saving");
					},
					success: function(data) {
						//alert({ title: 'Success!', text: 'Save Successfully', timer: 250 })
					},
					complete: function() {
						$('#save').val("Saved");
						location.href = "index.php?profile=" + username;
						// window.location.reload() ; 
					},
					error: function(data) {

						alert("Oops...", "Something went wrong :(", "error");
						alert({
							title: 'Oops...!',
							text: 'Something went wrong ',
							type: 'error',
							timer: 500
						})
					}
				});

			}
		});
	</script>
	<script src="../js/vendors/editor/dist/trumbowyg.js"></script>
	<script src="../js/vendors/editor/plugins/fontsize/trumbowyg.fontsize.js"></script>
	<script src="../js/vendors/editor/plugins/colors/trumbowyg.colors.js"></script>
	<script src="../js/vendors/editor/plugins/table/trumbowyg.table.js"></script>
	<script>
		$(document).ready(function() {
			// $('.dataTables-example').dataTable({
			// 	pageLength: 20,
			// 	responsive: true,
			// 	"dom": 'T<"clear">lfrtip',
			// 	"tableTools": {
			// 		"sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
			// 	}
			// });

			$('.trumbowygEditor').trumbowyg({
				btns: [
					['viewHTML'],
					['undo', 'redo'], // Only supported in Blink browsers
					['formatting'],
					['strong', 'em', 'del'],
					['superscript', 'subscript'],
					['fontsize'],
					['foreColor', 'backColor'],
					['link'],
					['insertImage'],
					['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
					['unorderedList', 'orderedList'],
					['horizontalRule'],
					['removeformat'],
					['fullscreen'],
					['table'] // Adding only the 'table' button for simplicity
				],
				plugins: {
					fontsize: {
						sizeList: [
							'12px', '14px', '16px', '18px', '20px', '24px', '32px', '48px',
						]
					},
					table: {
						rows: 8, // Default number of rows in table
						columns: 8, // Default number of columns in table
						styler: 'table table-striped table-bordered', // Adding borders and bootstrap table styling
						allowEnterInCell: true, // Allow 'Enter' key within table cells
						dropdown: [{
								title: 'Rows',
								buttons: [
									'tableAddRowAbove', 'tableAddRow', 'tableDeleteRow'
								]
							},
							{
								title: 'Columns',
								buttons: [
									'tableAddColumnLeft', 'tableAddColumn', 'tableDeleteColumn'
								]
							},
							{
								title: 'Misc',
								buttons: [
									'tableMergeCells', 'tableUnmergeCells', 'tableDestroy'
								]
							}
						]
					}
				}
			});


			$('#trumbowygEditor')
				.trumbowyg()
				.on('tbwchange', function() {
					$('#trumbowygEditor').val($('#trumbowygEditor').html())
				});

		});
	</script>
	<?php include("js_modal.php"); ?>
	<script src="../js/idle.js"></script>

</body>

</html>