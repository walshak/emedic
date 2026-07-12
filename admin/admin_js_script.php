<?php
$hospital_no = $hosp_no;
include("../inc/patient_alert.php"); ?>
<script>
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


	<?php

	if ($error_status == 1) { ?>
		toastr.error('<?php echo $error_msg ?>', 'Error', {
			timeOut: 5000
		})

	<?php } elseif ($error_status == 2) {
	?>
		toastr.success(' <?php echo $error_msg ?> ', 'Success', {
			timeOut: 5000
		})
	<?php

	}

	?>

	$(document).on('click', '#print-medical-report-btn', function() {
		$('#print_medical_report_modal').modal('show');

	});
	<?php if (isset($_GET['er'])) { ?>
		toastr.error('<?php echo 'Name Already Exist '; ?>', 'Error', {
			timeOut: 5000
		})
	<?php } ?>
	<?php if (isset($_GET['bk_err'])) { ?>
		toastr.error('<?php echo 'Unable to book appointment re-start again '; ?>', 'Error', {
			timeOut: 5000
		})
	<?php } ?>

	<?php if (isset($_GET['error'])) { ?>
		toastr.error('<?php echo 'Unable to Reverse - Invalid Quantity'; ?>', 'Error', {
			timeOut: 5000
		})
	<?php } ?>

	<?php if (isset($_GET['sv_close'])) { ?>
		toastr.warning('<?php echo 'Payment was Successful. <br>Close Transaction still pending!'; ?>', 'Attention', {
			timeOut: 5000
		})
	<?php } ?>

	<?php if (isset($_GET['Error_payment'])) { ?>
		toastr.error('Error Deleting with Outstanding Payment ', 'Error', {
			timeOut: 5000
		})
	<?php } ?>

	<?php if (isset($_GET['err_sel'])) { ?>
		toastr.error('<?php echo 'Selection Error'; ?>', 'Error', {
			timeOut: 5000
		})
	<?php } ?>
	<?php if (isset($_GET['invalid_dates'])) { ?>
		toastr.error('<?php echo 'Invalid Expiration Dates'; ?>', 'Error', {
			timeOut: 5000
		})
	<?php } ?>

	<?php if (isset($_GET['PatientExist'])) { ?>
		toastr.error('<?php echo 'Patient Already Exist '; ?>', 'Error', {
			timeOut: 5000
		})
	<?php } ?>

	<?php if (isset($_GET['HospExist'])) { ?>
		toastr.error('<?php echo 'Hospital Number Already Exist '; ?>', 'Error', {
			timeOut: 5000
		})
	<?php } ?>

	<?php if (isset($_GET['sv']) or isset($_GET['drn'])  or $sv == '1') { ?>
		toastr.success('<?php echo 'Successful'; ?>', 'Successful', {
			timeOut: 5000
		})
	<?php } ?>


	<?php if (isset($_GET['sv_adjust_rights'])) { ?>
		toastr.warning('<?php echo 'Update successful. We noticed you have changed the user rights. Please verify the user/s access by going to the Privileges page to check or uncheck the appropriate rights before proceeding.. '; ?>', 'ATTENTION', {
			timeOut: 12000
		})
	<?php } ?>


	<?php if (isset($upload_err)) { ?>
		toastr.error('<?php echo $upload_err; ?>', 'Error', {
			timeOut: 5000
		})
	<?php } ?>

	<?php if (isset($_GET['dl']) or isset($_GET['deleted']) or $dl == '1') { ?>
		toastr.error('<?php echo 'Deleted'; ?>', 'Deleted', {
			timeOut: 5000
		})
	<?php } ?>

	<?php if (isset($_GET['berr'])) { ?>
		toastr.error('<?php echo 'Error in selecting New Bed Space!'; ?>', 'Error', {
			timeOut: 5000
		})
	<?php } ?>

	<?php if (isset($upload_err)) { ?>
		toastr.error('<?php echo $upload_err; ?>', 'Error', {
			timeOut: 5000
		})
	<?php } ?>

	<?php if (isset($_GET['POsv'])) { ?>
		toastr.success('<?php echo 'Successfully Added <br> Check Purchase Order (PO) List'; ?>', 'Success', {
			timeOut: 5000
		})
	<?php } ?>

	<?php if (isset($_GET['main_err'])) { ?>
		toastr.error('<?php echo 'Requested Stock already Exist or Unable to pick Supplier'; ?>', 'Error', {
			timeOut: 5000
		})
	<?php } ?>

	<?php if (isset($_GET['sv_1'])) {
		$svv = $_GET['sv_1'];
	?>
		toastr.success('<?php echo 'Records Saved without following stock(s): ' . $svv; ?>', 'Error', {
			timeOut: 5000
		})
	<?php } ?>



	<?php if (isset($_GET['re_post'])) {
		$re_post = $_GET['re_post'];
	?>
		REPOST('<?php echo $re_post; ?>');

		function REPOST(reposit_details) {

			///	alert(reposit_details);


			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					patient_refer_id: reposit_details
				},
				success: function(data) {

					///alert(data)

					$('.modal-title').text('Refer Patient to another doctor');

					$('#refer_modal2').modal('show');
					$('#refer_body2').html(data);
				}
			});

		}

	<?php } ?>

	window.onload = blinkOn;
	function blinkOn() {
		var el = document.getElementById("blink");
		if (el) {
			el.style.color = "#ff0000";
			setTimeout("blinkOff()", 500);
		}
	}

	function blinkOff() {
		var el = document.getElementById("blink");
		if (el) {
			el.style.color = "";
			setTimeout("blinkOn()", 500);
		}
	}

	function UpdateCost() {

		var p_list = document.getElementById("p_list").value;
		var grand_total = 0;

		for (let i = 1; i <= p_list; i++) {

			var qty = document.getElementById("order_qty_" + i).value;
			var buying_cost = document.getElementById("buying_cost_" + i).value;

			var buy_cost = parseFloat(buying_cost);
			var qty = parseFloat(qty);

			var total = parseFloat(qty * buy_cost);
			formatedNumber = new Intl.NumberFormat().format(total)

			document.getElementById("total_" + i).value = formatedNumber;
			document.getElementById("total2_" + i).value = total;

			grand_total = grand_total + total;
		}
		formatedNumber = new Intl.NumberFormat().format(grand_total)
		document.getElementById("grand_total").innerHTML = formatedNumber;
	}


	<?php if ($recep_msg != '') { ?>
		$(document).ready(function() {
			$("#myModal").modal('show');
		});
	<?php } ?>

	<?php if (isset($_GET['app2'])) { ?>
		$('#appointment_modal').modal('show');
	<?php } elseif (isset($_GET['more_data'])) { ?>

		var hospital_number_edit = document.getElementById("hospital_number_edit").value;
		let x = myFunction(hospital_number_edit);

		$('#edit_patient_data_modal').modal('show');
	<?php } elseif ($msg_status == 'show' and !isset($_GET['re_post'])) { ?>
		$(document).ready(function() {
			$("#missing_data_modal").modal('show');
		});
	<?php  } elseif (isset($_GET['app'])) { ?>
		$('#appointment_modal').modal('show');
	<?php  } elseif (isset($_GET['assign_patient_insur'])) { ?>

		$('#new_patient_page2_modal').modal('hide');
		$('.modal-title').text('Convert Insurance Status');
		$('#convert_modal').modal('show');
	<?php } ?>





	<?php if ($dischargetable != '' or $cr_table != '' or $display_status == 1) { ?>
		$(document).ready(function() {
			$("#discharge_booking_modal").modal('show');
		});
	<?php  } ?>

	<?php if ($acct_b_table != '') { ?>
		///alert();
		$(document).ready(function() {
			$("#discharge_booking_modal").modal('show');
		});
	<?php  } ?>

	<?php if ($auth_table != '') { ?>
		$(document).ready(function() {
			$("#auth_reminder_modal").modal('show');
		});
	<?php  } ?>

	function merge_hosp_no_preview() {

		var delete_hospital_number = document.getElementById("delete_hospital_number").value;
		var correct_number = document.getElementById("correct_number").value;
		var remark_for_marger = document.getElementById("remark_for_marger").value.trim();

		if (remark_for_marger.length < 50) {
			alert("Remarks must be at least 50 characters long.");
			document.getElementById("remark_for_marger").focus();
			return false; // Prevent form submission
		}

		$('#merge_hosp_no_preview_btn').text('Loading, please wait...');
		$('#merge_hosp_no_details').hide('slow');
		$.ajax({
			url: "patient_record_merger.php",
			method: "POST",
			data: {
				merge_hosp_no_preview: true,
				delete_hospital_number: delete_hospital_number,
				remark_for_marger: remark_for_marger,
				correct_number: correct_number
			},
			success: function(data) {
				console.log(data)
				$('#merge_hosp_no_details').html(data);
				$('#merge_hosp_no_details').show('slow');
				$('#merge_hosp_no_preview_btn_wrap').hide('slow');
				$('#merge_hosp_no_submit_btn_wrap').show('slow');

			}
		});


	}

	function cancel_merge_hosp_no_preview() {

		$('#merge_hosp_no_preview_btn').text('Verify Records');
		$('#merge_hosp_no_preview_btn_wrap').show('slow');
		$('#merge_hosp_no_submit_btn_wrap').hide('fast');
		$('#merge_hosp_no_details').hide('fast');

	}

	function merge_hosp_no() {

		var delete_hospital_number = document.getElementById("delete_hospital_number").value;
		var correct_number = document.getElementById("correct_number").value;

		$('#marge_hosp_no_btn').text('Merging, please wait...');
		$.ajax({
			url: "patient_record_merger.php",
			method: "POST",
			data: {
				merge_hosp_no: true,
				delete_hospital_number: delete_hospital_number,
				correct_number: correct_number
			},
			success: function(data) {
				console.log(data)
				if (data.status == 200) {
					toastr.success('Merged successfully...', 'Success');
					$('#merge_patient_number_modal').modal('hide');
				} else {
					toastr.success(data.message, 'Success');
					$('#merge_patient_number_modal').modal('hide');
				}
				$('#marge_hosp_no_btn').text('Submit');

			}
		});


	}


	$(document).on('click', '.merge_patient_number', function() {
		$('.modal-title').text('Merge Duplicate Hospital Number');
		$('#merge_patient_number_modal').modal('show');
	});


	function myFunction(edit_patient_id) {
		// var edit_patient_id = $(this).attr("id");  
		$.ajax({
			url: "fetch.php",
			method: "POST",
			data: {
				edit_patient_id: edit_patient_id
			},
			dataType: "json",
			success: function(data) {
				$('#surname').val(data.surname);
				$('#fname').val(data.fname);
				$('#oname').val(data.oname);
				$('#genderr').val(data.gender);
				$('#phone').val(data.phone);
				$('#dob').val(data.dob);
				$('#age').val(data.age);
				$('#occup').val(data.occupation);
				$('#marital').val(data.marital_status);
				$('#blood_group').val(data.blood_g);
				$('#gtype').val(data.geno_type);
				$('#email').val(data.email);
				$('#religion').val(data.religion);
				$('#state_lga').val(data.state_lga);
				$('#tribe').val(data.tribe);
				$('#nationality').val(data.nationality);
				$('#addr').val(data.addr);
				$('#hosp_no').val(data.hospital_no);

				$('#dependant').val(data.nhis_no_ext);
				$('#nhis_membership_no').val(data.nhis_no);
				$('#primary_provider').val(data.primary_provider);
				$('#member').val(data.member);
				$('#datenhis').val(data.nhis_registration_date);
				$('#expiry_date').val(data.expiry_date);
				$('#reviewer').val(data.patient_review);
				$('#police_ranks').val(data.rank);
				$('#command_formation').val(data.command_formation);

				//$('.modal-title').text('Edit Patient Data');
				//$('#edit_patient_data_modal').modal('show');
			}
		});
	}


	$(document).on('click', '.add_patient_dashboard', function() {
		$('.modal-title').text('Add New Patient');
		$('#new_patient_dash_modal').modal('show');
	});

	$(document).on('click', '.merge_patient_number', function() {
		$('.modal-title').text('Merge Duplicate Hospital Number');
		$('#merge_patient_number_modal').modal('show');
	});

	$(document).on('click', '.ext_modal_link', function() {
		$('.modal-title').text('External Patient');
		$('#ext_modal').modal('show');
	});

	$(document).on('click', '.emr_id_front', function() {
		$('.modal-title').text('ID CARD');
		$('#emr_front_modal').modal('show');
	});

	$(document).on('click', '.emr_id_back', function() {
		$('.modal-title').text('ID CARD');
		$('#emr_back_modal').modal('show');
	});


	$(document).on('click', '.report_dates', function() {
		$('.modal-title').text('Report Dates');
		$('#report_dates_modal').modal('show');
	});

	$(document).on('click', '.add_patient', function() {
		$('.modal-title').text('Add Patient Data');
		$('#edit_patient_data_modal').modal('show');
	});



	$(document).on('click', '.income_earning', function() {

		$('#services_earnin_modal').modal('show');
	});

	$(document).on('click', '.add_earning', function() {


		$('#add_earning_modal').modal('show');
	});

	$(document).on('click', '.add_deduct', function() {

		//	$('.modal-title').text('WebMedic App Users'); 
		$('#add_deduct_modal').modal('show');
	});


	$(document).on('click', '.add_guardian', function() {

		$('.modal-title').text('Add Next of Kin Data');
		$('#add_guardian_modal').modal('show');
	});

	$(document).on('click', '.convert_insurance', function() {

		$('#new_patient_page2_modal').modal('hide');

		$('.modal-title').text('Convert Insurance Status');
		$('#convert_modal').modal('show');
	});

	$(document).on('click', '.change_ppt', function() {
		//	$('#passport_form')[0].reset(); 
		$('#passport_modal').modal('show');

	});


	$(document).on('click', '.financial_rpt', function() {
		//	$('#passport_form')[0].reset(); 
		$('#financial_modal').modal('show');

	});

	$(document).on('click', '.add_new_bed', function() {

		$('#add_new_bed_modal').modal('show');
	});

	$(document).on('click', '.add_new_bed_cat', function() {
		$('#add_new_cat_modal').modal('show');
	});

	$(document).on('click', '.claims', function() {
		$('#claims_modal').modal('show');
	});

	$(document).on('click', '.auth_code', function() {
		$('.modal-title').text('Update Authorization Code');
		$('#auth_modal').modal('show');
	});


	$(document).on('click', '.appointment_link', function() {
		$('.modal-title').text('Book Appointment');
		$('#appointment_modal').modal('show');
	});

	$(document).on('click', '.view_medical_rpt', function() {
		$('.modal-title').text('Patient Consultation Notes');
		$('#medical_report_modal').modal('show');
	});




	$(document).on('click', '.add_stock', function() {
		//	$('.modal-title').text('Patient Medical Report'); 
		$('#add_stock_modal').modal('show');
	});

	// new code
	$(document).on('click', '#upload_stock_btn', function() {
		//	$('.modal-title').text('Patient Medical Report'); 
		$('#upload_stock_modal').modal('show');
	});
	$(document).on('click', '.disengage_staff_btn', function() {
		$('#emp_id_field').val($('.disengage_staff_btn').attr('id'));
		$('#emp_engage_modal').modal('show');
	});
	$(document).on('click', '.engagement_history_btn', function() {
		$('#emp_engagement_history_modal').modal('show');
	});
	$(document).ready(function(e) {
		$("#stock_upload_form").on('submit', (function(e) {
			e.preventDefault();
			$('#upload_the_stock_btn').prop("disabled", true);
			$('#upload_the_stock_spinner').show();
			$.ajax({
				url: "excel_stock_download.php",
				type: "POST",
				data: new FormData(this),
				contentType: false,
				cache: false,
				processData: false,
				beforeSend: function() {
					//$("#preview").fadeOut();
					$("#err_stock_upload").fadeOut();
				},
				success: function(data) {
					if (data == 'invalid') {
						// invalid file format.
						$("#err_stock_upload").html("Invalid File !").fadeIn();
					} else {
						//console.log('done');
						data = JSON.parse(data);
						if (data.msg) {
							toastr.success(data.msg, 'Successfully', {
								timeOut: 5000
							});
						} else if (data.err) {
							toastr.warning(data.err, 'Error', {
								timeOut: 10000
							});
						}
						$('#upload_the_stock_btn').prop("disabled", false);
						$('#upload_the_stock_spinner').hide();
						$('#upload_stock_modal').modal('hide');
					}
				},
				error: function(e) {
					$("#err_stock_upload").html(e).fadeIn();
				}
			});
		}));
	});

	$(document).on('click', '#download_stock_btn', function() {
		//	$('.modal-title').text('Patient Medical Report'); 
		$('#download_stock_modal').modal('show');
	});



	$(document).on('click', '.staff_passort', function() {
		//	$('.modal-title').text('Patient Medical Report'); 
		$('#staff_port_modal').modal('show');
	});

	// new code
	$(document).on('click', '.staff_other_documents', function() {
		//	$('.modal-title').text('Patient Medical Report'); 
		$('#uploadStaffDocumentModal').modal('show');
	});



	$(document).on('click', '.edit_view_stock', function() {
		var edit_stock_id = $(this).attr("id");
		if (edit_stock_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					edit_stock_id: edit_stock_id
				},
				success: function(data) {
					$('.modal-title').text('Stock Details');
					$('#edit_stock_body').html(data);
					$('#edit_stock_modal').modal('show');
				}
			});
		}
	});




	$(document).on('click', '.webmedic_users', function() {
		var webmedic_users_id = $(this).attr("id");
		if (webmedic_users_id != '') {
			$.ajax({
				url: "fetch_set2.php",
				method: "POST",
				data: {
					webmedic_users_id: webmedic_users_id
				},
				success: function(data) {
					$('.modal-title').text('Assign User to WebMedic');
					$('#webmedic_users_body').html(data);
					$('#webmedic_user_modal').modal('show');
				}
			});
		}
	});







	// new code
	$(document).on('click', '.edit_hmo_price', function() {
		///			  $('#edit_stock_modal').modal('show');  

		var edit_hmo_stock_id = $(this).attr("id");
		if (edit_hmo_stock_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					edit_hmo_stock_id: edit_hmo_stock_id
				},
				success: function(data) {

					$('.modal-title').text('HMO/Corporate prices');
					$('#edit_hmo_prices_body').html(data);
					$('#edit_stock_modal').modal('hide');
					$('#edit_hmo_prices_modal').modal('show');

				}
			});
		}

	});


	$(document).on('click', '.add_special_pck', function() {

		var add_special_pck = $(this).attr("id");
		if (add_special_pck != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					add_special_pck: add_special_pck
				},
				success: function(data) {

					$('.modal-title').text('Add Service Item');
					$('#add_service_id_package_body').html(data);
					$('#add_service_id_package_modal').modal('show');

				}
			});
		}

	});


	$(document).on('click', '.add_service_id_package', function() {

		var add_service_id_package_id = $(this).attr("id");
		if (add_service_id_package_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					add_service_id_package_id: add_service_id_package_id
				},
				success: function(data) {

					$('.modal-title').text('Add Service Item');
					$('#add_service_id_package_body').html(data);
					$('#add_service_id_package_modal').modal('show');

				}
			});
		}

	});



	$(document).on('click', '.edit_price_tariff', function() {

		var edit_price_tariff_id = $(this).attr("id");
		if (edit_price_tariff_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					edit_price_tariff_id: edit_price_tariff_id
				},
				success: function(data) {

					$('.modal-title').text('HMO/Corporate prices');
					$('#edit_price_tariff_body').html(data);
					$('#edit_price_tariff_modal').modal('show');

				}
			});
		}

	});

	$(document).on('click', '.edit_investigation_tariff', function() {

		var edit_investigation_tariff_id = $(this).attr("id");
		if (edit_investigation_tariff_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					edit_investigation_tariff_id: edit_investigation_tariff_id
				},
				success: function(data) {

					$('.modal-title').text('HMO/Corporate prices');
					$('#edit_price_tariff_body').html(data);
					$('#edit_price_tariff_modal').modal('show');

				}
			});
		}

	});


	$(document).on('click', '.add_specialist', function() {


		var add_specialist_id = $(this).attr("id");
		if (add_specialist_id != '') {
			$.ajax({
				url: "fetch_specialist.php",
				method: "POST",
				data: {
					add_specialist_id: add_specialist_id
				},
				success: function(data) {
					$('.modal-title').text('Add Specialist / Assign Service to Specialty');
					$('#add_specialist_body').html(data);
					$('#add_specialist_modal').modal('show');

				}
			});
		}

	});



	$(document).on('click', '.edit_view_stock', function() {
		///			  $('#edit_stock_modal').modal('show');  

		var edit_stock_id = $(this).attr("id");
		if (edit_stock_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					edit_stock_id: edit_stock_id
				},
				success: function(data) {

					$('.modal-title').text('Stock Details');
					$('#edit_stock_body').html(data);
					$('#edit_stock_modal').modal('show');

				}
			});
		}

	});


	$(document).on('click', '.auth_code', function() {
		var auth_code_id = $(this).attr("id");
		if (auth_code_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					auth_code_id: auth_code_id
				},
				success: function(data) {

					$('.modal-title').text('Secondary Care Authorization Code');

					$('#auth_code_modal').modal('show');
					$('#auth_code_body').html(data);
				}
			});
		}
	});


	$(document).on('click', '.change_item', function() {
		var change_item_id = $(this).attr("id");
		if (change_item_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					change_requisition_item: change_item_id
				},
				success: function(data) {
					$('.modal-title').text('Change Requisition Item');
					$('#change_item_modal').modal('show');
					$('#change_item_body').html(data);
				}
			});
		}
	});


	function load_stock(target) {
		var departmentId = document.getElementById('dept_id_stock').value;
		var stock__item = document.getElementById('stock__item').value;
		$('#dept_stock_items').html('<b style="color:red;">Wait Please ...</b> ');

		if (target == 'departments') {
			document.getElementById('dept_id_stock').value = '';
		} else {
			document.getElementById('stock__item').value = '';
		}
		$.ajax({
			url: "fetch_set.php",
			method: "POST",
			data: {
				departmentId_stock_: departmentId,
				stock__item: stock__item,
				target: target
			},
			success: function(data) {
				$('#dept_stock_items').html(data);
			}
		});
	}

	function add_order_trxnf(sn) {

		var which_one_task = document.getElementById("which_one_task").value;

		if (which_one_task == 'list_departments') {
			var stock_sn_ = document.getElementById("stock_sn_" + sn).value;
			var dept_to_trnxfer_ = document.getElementById("dept_to_trnxfer_" + sn).value;
			var Dept_from_ = document.getElementById("Dept_from_" + sn).value;
			if (Dept_from_ == dept_to_trnxfer_) {
				alert('Invalid Department Same Department Found');
				exit;
			}
			var qty_ = document.getElementById("qty_" + sn).value;
		} else {
			var stock_sn_ = document.getElementById("stock_sn2_" + sn).value;
			var dept_to_trnxfer_ = document.getElementById("dept_to_trnxfer2_" + sn).value;
			var Dept_from_ = document.getElementById("dept_id_stock").value;
			var qty_ = document.getElementById("qty2_" + sn).value;
		}

		if (Dept_from_ != '' && dept_to_trnxfer_ != '') {

			if (qty_ <= 0) {
				alert('Invalid Quantity');
				exit;
			}

			var confirmation = confirm("Are you sure you want to proceed with the transfer?");
			if (confirmation) {
				$.ajax({
					url: "fetch_set.php",
					method: "POST",
					data: {
						stock_sn_: stock_sn_,
						qty_: qty_,
						dept_to_trnxfer_: dept_to_trnxfer_,
						Dept_from_: Dept_from_
					},
					success: function(data) {
						var json = JSON.parse(data);
						if (json["status"] == 0) {
							toastr.success(json["message"], 'Attention', {
								timeOut: 5000
							})
							$('#dept_stock_items').html('');
						} else {
							toastr.error(json["message"], 'Attention', {
								timeOut: 5000
							})
						}

					}
				});
			} else {
				// User clicked "Cancel", do nothing
				return;
			}


		} else {
			toastr.error('Invalid Departmemt IDs', 'Attention', {
				timeOut: 5000
			})
		}

	}

	$(document).on('click', '.stock_transfer', function() {
		$('.modal-title').text('Stock Transfer Between Departments/Units');
		$('#stock_transfer_modal').modal('show');
		$('#stock_transfer_body').html(data);

	});
	$(document).on('click', '.create_stock_combo', function() {
		var create_stock_combo_id = $(this).attr("id");
		if (create_stock_combo_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					create_stock_combo_id: create_stock_combo_id
				},
				success: function(data) {

					$('.modal-title').text('Create Stock Combo');

					$('#create_stock_combo_modal').modal('show');
					$('#create_stock_combo_body').html(data);
				}
			});
		}
	});

	$(document).on('click', '.add_company', function() {
		var company_id = $(this).attr("id");
		if (company_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					company_id: company_id
				},
				success: function(data) {

					$('.modal-title').text('Add Company Details');

					$('#add_company_modal').modal('show');
					$('#add_company_body').html(data);
				}
			});
		}
	});


	$(document).on('click', '.add_category', function() {

		var stock_cat_id = $(this).attr("id");
		if (stock_cat_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					stock_cat_id: stock_cat_id
				},
				success: function(data) {

					$('.modal-title').text('Add Category');

					$('#add_category_modal').modal('show');
					$('#add_category_body').html(data);
				}
			});
		}
	});


	$(document).on('click', '.edit_company', function() {
		var edit_company_id = $(this).attr("id");
		if (edit_company_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					edit_company_id: edit_company_id
				},
				success: function(data) {

					$('.modal-title').text('Edit Company');

					$('#edit_company_modal').modal('show');
					$('#edit_company_body').html(data);
				}
			});
		}
	});

	$(document).on('click', '.edit_category', function() {

		$('#add_category_modal').modal('hide');

		var edit_category_id = $(this).attr("id");
		if (edit_category_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					edit_category_id: edit_category_id
				},
				success: function(data) {

					$('.modal-title').text('Edit Category');

					$('#edit_category_modal').modal('show');
					$('#edit_category_body').html(data);
				}
			});
		}
	});





	$(document).on('click', '.edit_view_stock', function() {
		///			  $('#edit_stock_modal').modal('show');  

		var edit_stock_id = $(this).attr("id");
		if (edit_stock_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					edit_stock_id: edit_stock_id
				},
				success: function(data) {

					$('.modal-title').text('Stock Details');
					$('#edit_stock_body').html(data);
					$('#edit_stock_modal').modal('show');

				}
			});
		}

	});

	// new code
	$(document).on('click', '.edit_hmo_price', function() {
		///			  $('#edit_stock_modal').modal('show');  

		var edit_hmo_stock_id = $(this).attr("id");
		if (edit_hmo_stock_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					edit_hmo_stock_id: edit_hmo_stock_id
				},
				success: function(data) {

					$('.modal-title').text('HMO/Corporate prices');
					$('#edit_hmo_prices_body').html(data);
					$('#edit_stock_modal').modal('hide');
					$('#edit_hmo_prices_modal').modal('show');

				}
			});
		}

	});



	$(document).on('click', '.edit_bed_tariff', function() {
		///			  $('#edit_stock_modal').modal('show');  

		var edit_bed_tariff_id = $(this).attr("id");
		if (edit_bed_tariff_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					edit_bed_tariff_id: edit_bed_tariff_id
				},
				success: function(data) {

					$('.modal-title').text('HMO/Corporate prices');
					$('#edit_bed_tariff_body').html(data);
					$('#add_new_bed_modal').modal('hide');
					$('#edit_bed_tariff_modal').modal('show');

				}
			});
		}

	});



	$(document).on('click', '.confirm_payslip_delete', function() {

		var payslip_delete_id = $(this).attr("id");
		if (payslip_delete_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					payslip_delete_id: payslip_delete_id
				},
				success: function(data) {

					$('.modal-title').text('Payslip Delete');

					$('#confirm_del_payslip_modal').modal('show');
					$('#confirm_del_payslip_body').html(data);
				}
			});
		}
	});


	$(document).on('click', '.confirm_delete_bed', function() {

		var delete_bed_id = $(this).attr("id");
		if (delete_bed_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					delete_bed_id: delete_bed_id
				},
				success: function(data) {

					$('.modal-title').text('Delete Bed');

					$('#delete_bed_modal').modal('show');
					$('#delete_bed_body').html(data);
				}
			});
		}
	});


	$(document).on('click', '.appt_link2', function() {
		$('#appointment_modal').modal('hide');

		var appt_patient_id = $(this).attr("id");
		if (appt_patient_id != '') {


			///			alert(appt_patient_id);


			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					appt_patient_id: appt_patient_id
				},
				success: function(data) {

					$('.modal-title').text('Appointment Booking');

					$('#appointment_page2_modal').modal('show');
					$('#appointment_page2_body').html(data);
				}
			});
		}
	});





	$(document).on('click', '.confirm_item_del', function() {

		var confirm_item_del_id = $(this).attr("id");
		if (confirm_item_del_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					confirm_item_del_id: confirm_item_del_id
				},
				success: function(data) {

					$('.modal-title').text('Confirm to delete');

					$('#confirm_item_serv_modal').modal('show');
					$('#confirm_item_serv_form').html(data);
				}
			});
		}
	});


	$(document).on('click', '.income_add_services', function() {

		var add_services_id_income = $(this).attr("id");
		if (add_services_id_income != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					add_services_id_income: add_services_id_income
				},
				success: function(data) {

					$('.modal-title').text('Income Services Generated List');

					$('#add_income_services_modal').modal('show');
					$('#add_income_services_body').html(data);
				}
			});
		}
	});



	$(document).on('click', '.add_services_income_del', function() {
		var service_item_income_id = $(this).attr("id");

		var res = service_item_income_id.split("__");

		if (service_item_income_id != '') {
			$.ajax({
				url: "delete.php",
				method: "POST",
				data: {
					service_item_income_id: res[0]
				},
				success: function(data) {

					var service_item_income_id = res[1] + '__' + res[2] + '__' + res[3];

					$.ajax({
						url: "fetch_set.php",
						method: "POST",
						data: {
							add_services_id_income: service_item_income_id
						},
						success: function(data) {

							$('.modal-title').text('Income Services Generated List');

							$('#add_income_services_modal').modal('show');
							$('#add_income_services_body').html(data);
						}
					});

					// $('#view_lab_modal').modal('show');  
					//$('#view_lab_body').html(data); 
				}
			});
		}
	});


	$(document).on('click', '.confirm_payslip', function() {

		var confirm_payslip_id = $(this).attr("id");
		if (confirm_payslip_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					confirm_payslip_id: confirm_payslip_id
				},
				success: function(data) {

					$('.modal-title').text('Confirm Payslip');

					$('#confirm_payslip_modal').modal('show');
					$('#confirm_payslip_body').html(data);
				}
			});
		}
	});




	$(document).on('click', '.edit_bed', function() {

		var edit_bed_id = $(this).attr("id");
		$.ajax({
			url: "fetch.php",
			method: "POST",
			data: {
				edit_bed_id: edit_bed_id
			},
			dataType: "json",
			success: function(data) {

				$('#sn').val(data.sn);
				$('#room').val(data.room_name);
				$('#title').val(data.tips);
				$('#bed_number').val(data.bed_no);
				$('#nhis_price').val(data.nhis_price);
				$('#hosp_price').val(data.hosp_price);
				$('#exit_pricee').val(data.ext_price);
				$('#Department').val(data.dept_id);

				$('.modal-title').text('Edit Bed and Price');
				$('#add_new_bed_modal').modal('show');
			}
		});
	});

	$(document).on('click', '.edit_room_btn', function() {

		var edit_room_id = $(this).data("room_name");
		$.ajax({
			url: "fetch.php",
			method: "POST",
			data: {
				edit_room_id: edit_room_id
			},
			dataType: "json",
			success: function(data) {
				// console.log(data);
				$('#room_id_').val(data.sn);
				$('#Department').val(data.dept_id);
				$('#create_room').val(data.rooms);
				$('#add_room').text('update');

				var modalBody = $('.modal-body');
				modalBody.animate({
					scrollTop: 0
				}, 'slow');
			}
		});
	});





	$(document).on('click', '.add_guardian_edit', function() {

		var gd_edit = $(this).attr("id");
		$.ajax({
			url: "fetch.php",
			method: "POST",
			data: {
				gd_edit: gd_edit
			},
			dataType: "json",
			success: function(data) {

				$('#guard_id').val(data.guardian_id);
				$('#g_name').val(data.guardian_Name);
				$('#gender').val(data.guardian_gender);
				$('#g_addr').val(data.guardian_address);
				$('#g_phone').val(data.guardian_phone);
				$('#occupation').val(data.guardian_occupation);
				$('#g_relation').val(data.guardian_relationship);

				$('.modal-title').text('Edit Guardian Data');
				$('#add_guardian_modal').modal('show');
			}
		});
	});


	$(document).on('click', '.see_occupant', function() {

		var see_occupant_id = $(this).attr("id");
		if (see_occupant_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					see_occupant_id: see_occupant_id
				},
				success: function(data) {

					$('.modal-title').text('Occupant Confirmation');

					$('#see_occupant_modal').modal('show');
					$('#see_occupant_body').html(data);
				}
			});
		}
	});


	$(document).on('click', '.add_new_item', function() {

		var add_new_item_id = $(this).attr("id");
		var res = add_new_item_id.split("__");

		if (add_new_item_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					add_new_item_id: add_new_item_id
				},
				success: function(data) {

					$('.modal-title').text('Add New/Edit ' + res[0]);

					$('#add_new_item_modal').modal('show');
					$('#add_new_item_form').html(data);
				}
			});
		}
	});


	$(document).on('click', '.add_new_category', function() {

		var add_new_category_id = $(this).attr("id");
		var res = add_new_category_id.split("__");
		if (add_new_category_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					add_new_category_id: add_new_category_id
				},
				success: function(data) {

					$('.modal-title').text('Add New ' + res[0] + ' Category');

					$('#add_new_category_modal').modal('show');
					$('#add_new_category_form').html(data);
				}
			});
		}
	});



	$(document).ready(function() {

		$(".edit-btn").click(function() {

			var id = $(this).data("edit_id");
			var name = $(this).data("catetory_name");

			// Put value inside textbox
			$("#catetory_name").val(name);

			// Store ID in hidden field
			$("#edit_id").val(id);

			// Change button text
			$("#saveBtn").text("Update");

			// Optional: scroll to form
			$('html, body').animate({
				scrollTop: $("#catetory_name").offset().top
			}, 500);
		});

	});


	$(document).on('click', '.manage_registration_price', function() {

		var reg_price = $(this).attr("id");

		$.ajax({
			url: "fetch.php",
			method: "POST",
			data: {
				manage_reg_price: reg_price
			},
			// dataType:"json",  
			success: function(data) {

				/// $('.modal-title').text('Manage Prices);
				$('#manage_reg_price_modal').modal('show');
				$('#manage_reg_price_form').html(data);
			}
		});
	});




	$(document).on('click', '.manage_price', function() {
		var manage_price_id = $(this).attr("id");
		var res = manage_price_id.split("__");

		$.ajax({
			url: "fetch.php",
			method: "POST",
			data: {
				manage_price_id: res[0]
			},
			dataType: "json",
			success: function(data) {

				$('#item_sn').val(data.sn);
				$('#item_service_name').val(data.item_service);
				$('#coverage').val(data.coverage);
				$('#insurance_type').val(data.insurance_type);
				$('#hosp_price').val(data.hosp_price);
				$('#external_price').val(data.ext_price);
				$('#NHIS_price').val(data.nhis_price);
				$('#Department3').val(data.dept);
				$('#table_type').val(data.price_table);
				$('#duration').val(data.duration);
				$('#file_amt').val(data.file_amt);

				$('.modal-title').text('Manage Prices for ' + res[1]);
				$('#manage_price_modal').modal('show');
			}
		});
	});


	$(document).on('click', '.manage_price_stock', function() {
		var manage_price_stock_id = $(this).attr("id");
		var res = manage_price_stock_id.split("__");

		$.ajax({
			url: "fetch.php",
			method: "POST",
			data: {
				manage_price_stock_id: res[0]
			},
			dataType: "json",
			success: function(data) {
				$('#item_sn').val(data.sn);
				$('#item_service_name').val(data.product_name);
				$('#coverage').val(data.coverage);
				$('#insurance_type').val(data.insurance_type);
				$('#hosp_price').val(data.hosp_price);
				$('#external_price').val(data.ext_price);
				$('#NHIS_price').val(data.nhis_price);
				$('#Department3').val(data.dept);
				$('#table_type').val(data.stock_table);

				$('.modal-title').text('Manage Prices for ' + res[1]);
				$('#manage_price_modal').modal('show');
			}
		});
	});





	$(document).on('click', '.manage_price_invest', function() {
		var manage_price_invest_id = $(this).attr("id");
		var res = manage_price_invest_id.split("__");

		$.ajax({
			url: "fetch.php",
			method: "POST",
			data: {
				manage_price_invest_id: res[0]
			},
			dataType: "json",
			success: function(data) {
				$('#item_sn').val(data.sn);
				$('#item_service_name').val(data.test);
				$('#coverage').val(data.coverage);
				$('#insurance_type').val(data.insurance_type);
				$('#hosp_price').val(data.hosp_price);
				$('#external_price').val(data.ext_price);
				$('#NHIS_price').val(data.nhis_price);
				$('#Department3').val(data.dept);
				$('#table_type').val('invest');

				$('.modal-title').text('Manage Prices for ' + res[1]);
				$('#manage_price_modal').modal('show');
			}
		});
	});


	$(document).on('click', '.confirm_gd_delete', function() {

		var delete_gd_id = $(this).attr("id");
		if (delete_gd_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					delete_gd_id: delete_gd_id
				},
				success: function(data) {

					$('.modal-title').text('Delete');

					$('#confirm_gd_delete_modal').modal('show');
					$('#confirm_gd_delete_body').html(data);
				}
			});
		}
	});

	$(document).on('click', '.edit_order', function() {

		var edit_order_id = $(this).attr("id");
		if (edit_order_id != '') {
			$.ajax({
				url: "fetch_set2.php",
				method: "POST",
				data: {
					edit_order_id: edit_order_id
				},
				success: function(data) {

					$('.modal-title').text('Purchase Order');

					$('#edit_order_modal').modal('show');
					$('#edit_order_body').html(data);
				}
			});
		}
	});


	$(document).on('click', '.po_reorder', function() {

		var po_reorder_id = $(this).attr("id");
		if (po_reorder_id != '') {
			$.ajax({
				url: "fetch_set2.php",
				method: "POST",
				data: {
					po_reorder_id: po_reorder_id
				},
				success: function(data) {

					$('.modal-title').text('Purchase Order');

					$('#po_reorder_modal').modal('show');
					$('#po_reorder_body').html(data);
				}
			});
		}
	});






	$(document).on('click', '.print_order', function() {
		var print_order_id = $(this).attr("id");
		if (print_order_id != '') {
			$.ajax({
				url: "fetch_set2.php",
				method: "POST",
				data: {
					print_order_id: print_order_id
				},
				success: function(data) {
					/// alert(data);

					$('.modal-title').text('Print Purchase Order');
					$('#print_ordered_body').html(data);
					$('#print_ordered_modal').modal('show');

				}
			});
		}
	});





	$(document).on('click', '.edit_patient', function() {

		var edit_patient_id = $(this).attr("id");
		$.ajax({
			url: "fetch.php",
			method: "POST",
			data: {
				edit_patient_id: edit_patient_id
			},
			dataType: "json",
			success: function(data) {

				$('#surname').val(data.surname);
				$('#fname').val(data.fname);
				$('#oname').val(data.oname);
				$('#genderr').val(data.gender);
				$('#phone').val(data.phone);
				$('#dob').val(data.dob);
				$('#dob2').val(data.dob);
				$('#age').val(data.age);
				$('#occup').val(data.occupation);
				$('#marital').val(data.marital_status);
				$('#blood_group').val(data.blood_g);
				$('#gtype').val(data.geno_type);
				$('#email').val(data.email);
				$('#religion').val(data.religion);
				$('#state_lga').val(data.state_lga);
				$('#tribe').val(data.tribe);
				$('#nationality').val(data.nationality);
				$('#addr').val(data.addr);
				$('#hosp_no').val(data.hospital_no);

				$('#dependant').val(data.nhis_no_ext);
				$('#nhis_membership_no').val(data.nhis_no);
				$('#primary_provider').val(data.primary_provider);
				$('#member').val(data.member);
				$('#datenhis').val(data.nhis_registration_date);
				$('#expiry_date').val(data.expiry_date);
				$('#reviewer').val(data.patient_review);
				$('#police_ranks').val(data.rank);
				$('#command_formation').val(data.command_formation);



				$('.modal-title').text('Edit Patient Data');
				$('#edit_patient_data_modal').modal('show');
			}
		});
	});



	$(document).ready(function() {
		$("#NHIS_S").hide();
		$("#PHIS_S").hide();
		$("#Corporate").hide();
		$("#Family").hide();
		$("#Private").hide();
		//("#Private").hide();

		$('#Insurance_Type').on('change', function() {
			if (this.value == 'NHIS') {
				$("#NHIS_S").show();
				$("#PHIS_S").hide();
				$("#Corporate").hide();
				$("#Family").hide();
				$("#family_display").hide();
				$("#Private").hide();
				$("#close").hide();
			}
			if (this.value == 'PHIS') {
				$("#NHIS_S").hide();
				$("#PHIS_S").show();
				$("#Corporate").hide();
				$("#Family").hide();
				$("#family_display").hide();
				$("#Private").hide();
				$("#close").hide();

			}
			if (this.value == 'Corporate') {
				$("#NHIS_S").hide();
				$("#PHIS_S").hide();
				$("#Corporate").show();
				$("#Family").hide();
				$("#family_display").hide();
				$("#Private").hide();
				$("#close").hide();

			}

			if (this.value == 'Family') {
				$("#NHIS_S").hide();
				$("#PHIS_S").hide();
				$("#Corporate").hide();
				$("#Private").hide();
				$("#close").hide();

				$("#Family").show();
				$("#family_display").show();
			}

			if (this.value == 'Private') {
				$("#NHIS_S").hide();
				$("#PHIS_S").hide();
				$("#Corporate").hide();
				$("#Private").show();
				$("#Family").hide();
				$("#family_display").hide();
				$("#close").hide();

			}



			if (this.value == '') {
				$("#NHIS_S").hide();
				$("#PHIS_S").hide();
				$("#Corporate").hide();
				$("#Family").hide();
				$("#family_display").hide();
				$("#Private").hide();
				$("#close").show();

			}
		});
	});



	$('#insurance_form').on("submit", function(event) {
		event.preventDefault();


		// <option selected="selected" value="">Select...</option>
		//  <option value="NHIS">NHIS</option>
		//  <option value="PHIS">PHIS(Private HMO)</option>
		//  <option value="Corporate">Corporate</option>
		//  <option value="Family">Family</option>

		var payment_remarks = document.getElementById("payment_remarks").value;
		var total_rows = document.getElementById("total_rows").value;

		var characterLength = payment_remarks.length;

		if (total_rows >= 2 && characterLength < 50) {
			toastr.error('Enter at least 50 characters or more for conversion', 'Error', {
				timeOut: 5000
			});
			exit;
		}

		var hmo_no = $("#insurance_list").val();
		var hmo_no2 = $("#insurance_list2").val();
		var hmo_no3 = $("#insurance_list33").val();
		var hmo_no4 = $("#insurance_list4").val();

		var Insurance_Type = $("#Insurance_Type").val();
		var nhis_no = $("#nhis_no").val();
		var hos_no = $("#hos_no").val();
		var mem_no = $("#mem_no").val();
		var g_relation2 = $("#g_relation2").val();

		//alert(Insurance_Type);
		if (Insurance_Type == 'NHIS') {
			var option_text = Insurance_Type + '__' + hmo_no + '__' + hos_no + '__' + nhis_no
		}
		if (Insurance_Type == 'PHIS') {
			var option_text = Insurance_Type + '__' + hmo_no2 + '__' + hos_no + '__' + ''
		}

		if (Insurance_Type == 'Corporate') {
			var option_text = Insurance_Type + '__' + hmo_no3 + '__' + hos_no + '__' + ''
		}

		if (Insurance_Type == 'Family') {
			var option_text = Insurance_Type + '__' + hmo_no4 + '__' + hos_no + '__' + g_relation2
		}

		if (Insurance_Type == 'Private') {
			var option_text = Insurance_Type + '__' + '' + '__' + hos_no + '__' + ''
		}

		$('#convert_modal').modal('hide');
		// $('#view_insurance_convert_modal').modal('show');

		///alert(option_text);
		$.ajax({
			url: "fetch_set.php",
			method: "POST",
			data: {
				insur_convert_id: option_text,
				payment_remarks: payment_remarks,
				total_rows: total_rows
			},
			success: function(data) {

				//  $('.modal-title').text('Field Names for ' + res[1]);
				$('#view_insurance_convert_modal').modal('show');
				$('#view_insurance_convert_body').html(data);
			}
		});

	});

	$(document).on('click', '.add_insurance', function() {

		var add_insurance_id = $(this).attr("id");
		if (add_insurance_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					add_insurance_id: add_insurance_id
				},
				success: function(data) {

					$('.modal-title').text('Add/Edit Insurances/Folders Data');

					$('#insurance_modal').modal('show');
					$('#insurance_body').html(data);
				}
			});
		}
	});



	$(document).on('click', '.claim_amount', function() {

		var claim_income_id = $(this).attr("id");
		if (claim_income_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					claim_income_id: claim_income_id
				},
				success: function(data) {

					$('.modal-title').text('Claim Income Reports');

					$('#claim_amount_modal').modal('show');
					$('#claim_amount_body').html(data);
				}
			});
		}
	});


	$('#new_patient_dash_body').on("submit", function(event) {
		event.preventDefault();
		$.ajax({
			url: "insert.php",
			method: "POST",
			data: $('#new_patient_dash_body').serialize(),
			beforeSend: function() {},
			success: function(data) {
				var msg = data;
				///	alert(msg);

				$('#new_patient_dash_modal').modal('hide');

				///==================================

				var surname = $("#surname").val();
				var fname = $("#fname").val();
				var oname = $("#oname").val();
				var gender = $("#gender").val();
				var dob = $("#dob").val();
				var phoneno = $("#phoneno").val();
				var fields_new_id = surname + '__' + fname + '__' + oname + '__' + gender + '__' + dob + '__' + phoneno;
				$('#new_patient_dash_body')[0].reset();

				$.ajax({
					url: "fetch_set.php",
					method: "POST",
					data: {
						fields_new_id: fields_new_id,
						msg: msg
					},
					success: function(data) {

						$('#new_patient_page2_modal').modal('show');
						$('#new_patient_page2_body').html(data);
					}
				});


				///==========================	

				$('#test_fields').html(data);
			},
			complete: function() {
				$('#Save_patient').val("Saved");
			},
			error: function(data) {

				alert("Oops...", "Something went wrong :(", "error");
				swal({
					title: 'Oops...!',
					text: 'Something went wrong ',
					type: 'error',
					timer: 500
				})
			}
		});

		//	   }
	});


	$('#data_1 .input-group.date').datepicker({
		todayBtn: "linked",
		keyboardNavigation: false,
		forceParse: false,
		calendarWeeks: true,
		autoclose: true
	});




	$(document).on('click', '.patient_refer', function() {

		var patient_refer_id = $(this).attr("id");
		if (patient_refer_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					patient_refer_id: patient_refer_id
				},
				success: function(data) {

					////alert(data)
					$('.modal-title').text('Refer Patient to another doctor');

					$('#refer_modal').modal('show');
					$('#refer_body').html(data);
				}
			});
		}
	});



	$(document).on('click', '.edit_stock', function() {
		$('#edit_stock_modal').modal('hide');

		var edit_stock_id = $(this).attr("id");
		$.ajax({
			url: "fetch.php",
			method: "POST",
			data: {
				edit_stock_id: edit_stock_id
			},
			dataType: "json",
			success: function(data) {

				$('#stock_id').val(data.sn);
				$('#stockname').val(data.product_name);
				$('#category').val(data.category);
				$('#category2').val(data.category);
				$('#GenericName').val(data.generic_name);
				$('#dosage').val(data.dosage);
				$('#presentation').val(data.presentation);
				$('#strength').val(data.strength);
				$('#purchase_cost').val(data.buying_cost);
				$('#expired').val(data.expire_date);
				$('#reorder').val(data.reorder_level);
				$('#qty').val(data.qty);
				$('#packagetype').val(data.package_type);
				$('#units').val(data.stock_total_unit);
				$('#hosp_price').val(data.hosp_price);
				$('#cash_price').val(data.cash_price);
				$('#NHIS_price').val(data.nhis_price);
				$('#insurance_type').val(data.insurance_type);
				$('#stock_request').val(data.entry_mode);
				$('#percentage_markup').val(data.price_markup);

				$('.modal-title').text('Edit Stock Details');
				$('#add_stock_modal').modal('show');
			}
		});
	});


	function fetch_inventory_report() {

		var inventory_start = document.getElementById("inventory_start").value;
		var inventory_end = document.getElementById("inventory_end").value;
		var inventory_dept = document.getElementById("inventory_dept").value;
		var inventory_stock_snn = document.getElementById("inventory_stock_snn").value;
		var inventory_stock_total_unit = document.getElementById("inventory_stock_total_unit").value;


		var inventroy_pack_status = document.getElementById("inventroy_pack_status");
		if (inventroy_pack_status.checked) {
			var _pack_status = 1; //alert("View As Pack is enabled");
		} else {
			var _pack_status = 0;
		}


		$.ajax({
			url: "fetch_stock.php",
			method: "POST",
			data: {
				mgt_stock_id_report: inventory_stock_snn,
				inventory_end: inventory_end,
				inventory_start: inventory_start,
				inventory_stock_total_unit: inventory_stock_total_unit,
				inventroy_pack_status: _pack_status,
				inventory_dept: inventory_dept
			},
			success: function(data) {

				$('#inventory_body_report').html(data);

			}
		});



	}
	$(document).on('click', '.inven_report', function() {
		var mgt_stock_id_report = $(this).attr("id");
		$.ajax({
			url: "fetch_stock.php",
			method: "POST",
			data: {
				mgt_stock_id_report_form: mgt_stock_id_report
			},
			success: function(data) {
				$('.modal-title').text('Stocks Inventory Report')
				$('#inventory_body').html(data);
				$('#inventory_modal').modal('show');
			}
		});
	});

	$(document).on('click', '.mgt_inven', function() {
		var mgt_stock_id = $(this).attr("id");
		/// var res = mgt_stock_id.split("__");	 

		$.ajax({
			url: "fetch_stock.php",
			method: "POST",
			data: {
				mgt_stock_id: mgt_stock_id,
				stock_table_to_post: "<?php echo $_GET['stock'] ?>"
			},
			success: function(data) {
				/*
				 	$('#mgt_old_qty').val(data.qty);
					$('#mgt_main_qty').val(data.main_qty);
					$('#mgt_stock_id').val(data.sn);  
					$('#mgt_unit').val(data.stock_total_unit); 
					$('#mgt_expired').val(data.expire_date);
					$('#stockName').val(res[1]);  
					 */
				$('.modal-title').text('Stocks Inventory and Purchase Order (PO)')
				$('#inventory_body2').html(data);
				$('#inventory_modal2').modal('show');
				//	 
			}
		});
	});


	$(document).on('click', '.mgt_expire_date', function() {
		var mgt_expire_date_id = $(this).attr("id");
		$.ajax({
			url: "fetch_stock.php",
			method: "POST",
			data: {
				mgt_expire_date_id: mgt_expire_date_id
			},
			success: function(data) {
				$('.modal-title').text('Manage Procurement(s) Expiring Dates ')
				$('#mgt_expire_date_body').html(data);
				$('#mgt_expire_date_modal').modal('show');
				//	 
			}
		});
	});


	$(document).on('click', '.dsp_oncredit', function() {
		var dsp_oncredit_id = $(this).attr("id");

		if (dsp_oncredit_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					dsp_oncredit_id: dsp_oncredit_id
				},
				success: function(data) {

					$('.modal-title').text('Dispense On/Credit');

					$('#dsp_oncredit_body').html(data);
					$('#dsp_oncredit_modal').modal('show');
				}
			});
		}
	});


	$(document).on('click', '.edit_price_entry', function() {

		var editprice_id = $(this).attr("id");

		if (editprice_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				// data:{edit_price_id:res[0]+'__'+res[1]+'__'+res[2]}, 
				data: {
					editprice_id: editprice_id
				},

				success: function(data) {

					$('.modal-title').text('Edit Price');

					$('#edit_price_body').html(data);
					$('#edit_price_modal').modal('show');
				}
			});
		}
	});


	$(document).on('click', '.edit_claim_date', function() {
		var edit_claim_date_id = $(this).attr("id");

		if (edit_claim_date_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				// data:{edit_price_id:res[0]+'__'+res[1]+'__'+res[2]}, 
				data: {
					edit_claim_date_id: edit_claim_date_id
				},

				success: function(data) {

					$('.modal-title').text('Edit Date Entered');

					$('#edit_claim_date_body').html(data);
					$('#edit_claim_date_modal').modal('show');
				}
			});
		}
	});



	$(document).on('click', '.approve_request', function() {
		var approve_request_id = $(this).attr("id");
		///  alert(approve_request_id);

		var rslt = approve_request_id.split("__");

		if (approve_request_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					approve_request_id: rslt[0],
					stock: rslt[1]
				},
				success: function(data) {
					$('.modal-title').text('Approve Request');
					$('#approve_request_body').html(data);
					$('#approve_request_modal').modal('show');
				}
			});
		}
	});

	$(document).on('click', '.approve_procurement', function() {
		var approve_procurement_id = $(this).attr("id");
		var rslt = approve_procurement_id.split("__");

		if (approve_procurement_id != '') {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					approve_procurement_id: rslt[0],
					stock: rslt[1]
				},
				success: function(data) {
					$('.modal-title').text('Approve Procurement');
					$('#approve_procurement_body').html(data);
					$('#approve_procurement_modal').modal('show');
				}
			});
		}
	});







	$(document).ready(function() {
		///alert();

		$('#requisition_popup_modal').modal('show');
	});


	function seen_popup(sn) {
		var sn;
		$.ajax({
			url: "fetch.php",
			method: "POST",
			data: {
				seen_request: sn
			},
			success: function(data) {
				toastr.success(data, 'Success', {
					timeOut: 5000
				})
			}
		});
	}

	function finish_drug(sn) {
		var sn;
		/// alert(sn);

		$.ajax({
			url: "fetch.php",
			method: "POST",
			data: {
				finish_status: sn
			},
			success: function(data) {
				toastr.success(data, 'Success', {
					timeOut: 5000
				})
			}
		});
	}



	function update_expire_date(sn, stock_sn) {
		var sn;
		var stock_sn;
		var new_expire_date = document.getElementById("mgt_date_" + sn).value;
		$.ajax({
			url: "fetch.php",
			method: "POST",
			data: {
				edit_exp_date: sn,
				new_expire_date: new_expire_date,
				stock_sn: stock_sn
			},
			success: function(data) {
				toastr.success(data, 'Success', {
					timeOut: 5000
				})
			}
		});
	}

	$('.dataTables-example').dataTable({
		pageLength: 200,
		responsive: true,
		"dom": 'T<"clear">lfrtip',
		"tableTools": {
			"sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
		}
	});

	$('#data_5 .input-daterange').datepicker({
		keyboardNavigation: false,
		forceParse: false,
		autoclose: true
	});

	$(document).ready(function() {
		$("#nhis_list").hide();
		$("#phis_list").hide();
		$("#cop_list").hide();

		$('#insurance_type2').on('change', function() {
			if (this.value == 'nhis') {
				$("#nhis_list").show();
				$("#phis_list").hide();
				$("#cop_list").hide();
			}

			if (this.value == 'phis') {
				$("#phis_list").show();
				$("#nhis_list").hide();
				$("#cop_list").hide();
			}

			if (this.value == 'corporate') {
				$("#nhis_list").hide();
				$("#phis_list").hide();
				$("#cop_list").show();
			}

		});
	});

	$(document).ready(function() {
		$("#req").hide();
		$("#pro").hide();
		$("#pro_b").hide();
		$("#req_b").hide();

		$('#type_of_report').on('change', function() {
			if (this.value == 'pro') {
				$("#pro").show();
				$("#req").hide();
				$("#pro_b").show();
				$("#req_b").hide();
			}
			if (this.value == 'req') {
				$("#pro").hide();
				$("#req").show();
				$("#req_b").show();
				$("#pro_b").hide();
			}
		});

	});

	$("#stock_table_rpt").change(function() {
		var stock_table_type_id = $(this).val();
		if (stock_table_type_id != "") {
			$.ajax({
				url: "get_stock.php",
				data: {
					stock_table_type_id: stock_table_type_id
				},
				type: 'POST',
				success: function(response) {
					////alert(response);
					var resp = $.trim(response);
					$("#list_stock").html(resp);
				}
			});
		} else {
			$("#list_stock").html("<option value=''>------- Select --------</option>");
		}
	});


	$(document).ready(function() {

		function blink(selector) {
			$(selector).fadeOut('slow', function() {
				$(this).fadeIn('slow', function() {
					blink(this);
				});
			});
		}

		get_dashboard_updates();
		auto_discharge_code();
		_get_outstanding_billing();
		bill_account_status();


		function _get_outstanding_billing() {

			$.ajax({
				url: "fetch_dash.php",
				data: {
					admitted_patient_outstanding: true
				},
				type: 'POST',
				success: function(response) {

					var json = JSON.parse(response);

					$("#All_patient_cr").html(json["All_patient_cr"]);
					if (json["All_patient_cr2"] > 0) {
						document.getElementById('view_admitted_patient_bal1').click();
					}

				}
			});
		}



		function auto_discharge_code() {

			$.ajax({
				url: "auto_discharge_code.php",
				data: {
					system_discharge: 'system_discharge'
				},
				type: 'POST',
				success: function(response) {

					////alert(response);
				}
			});
		}


		function bill_account_status() {

			<?php if ($bill_account_status == 1) { ?>
				$.ajax({
					url: "_dashboard_counts.php",
					data: {
						bill_account_status: '<?php echo $ECode_logged; ?>'
					},
					type: 'POST',
					success: function(response) {

						var json = JSON.parse(response);
						$("#bill_account_amount").html(json["bill_account_amount"]);


					}

				});

			<?php } ?>
		}




		function get_dashboard_updates() {



			$.ajax({
				url: "_count_special_request.php",
				data: {
					get_dashboard_counts: 'get_dashboard_counts'
				},
				type: 'POST',
				success: function(response) {
					var json = JSON.parse(response);
					$("#see_specialist_count").html(json["see_specialist_count"]);
					$("#dob_count").html(json["dob_count"]);

					// Blink & turn red if > 0
					if (parseInt(json["see_specialist_count"]) > 0) {
						$("#see_specialist_count").css({
							"color": "white",
							"font-weight": "bold",
							"font-size": "26px",
							"animation": "blink_2 1s infinite"
						});
					} else {
						$("#see_specialist_count").css({
							"color": "",
							"font-weight": "",
							"animation": ""
						});
					}

					if (json["apptm_fellowup"] > 0) {
						shoot_doctor_appointment_pop();
					}
				}
			});



			function shoot_doctor_appointment_pop() {
				var view_specialist_request_id = true;
				$.ajax({
					url: "fetch_dash.php",
					method: "POST",
					data: {
						view_specialist_request_id: view_specialist_request_id
					},
					success: function(data) {
						$('.modal-title').text('Specialist Request Booking');

						$('#specialist_body').html(data);
						$('#specialist_modal').modal('show');
					}
				});
			}




		}

	})


	$(document).on('click', '.view_speccialist_request', function() {

		var view_specialist_request_id = $(this).attr("id");
		$.ajax({
			url: "fetch_dash.php",
			method: "POST",
			data: {
				view_specialist_request_id: view_specialist_request_id
			},
			success: function(data) {
				$('.modal-title').text('Specialist Request Booking');

				$('#specialist_body').html(data);
				$('#specialist_modal').modal('show');
			}
		});
	});



	$(document).on('click', '.view_birthday_request', function() {

		var view_birthday_request_id = $(this).attr("id");
		$.ajax({
			url: "fetch_dash.php",
			method: "POST",
			data: {
				view_birthday_request_id: view_birthday_request_id
			},
			success: function(data) {
				$('.modal-title').text('Birthday Notiification');

				$('#specialist_body').html(data);
				$('#specialist_modal').modal('show');
			}
		});
	});


	$(document).on('click', '.add_consults_room', function() {
		$('.modal-title').text('Add Consults Room');
		$('#add_consults_room_modal').modal('show');
	});



	function setCookie(name, value, days) {
		let expires = "";
		if (days) {
			let date = new Date();
			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
			expires = "; expires=" + date.toUTCString();
		}
		document.cookie = name + "=" + (value || "") + expires + "; path=/";
	}

	function getCookie(name) {
		let nameEQ = name + "=";
		let ca = document.cookie.split(';');
		for (let i = 0; i < ca.length; i++) {
			let c = ca[i].trim();
			if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
		}
		return null;
	}



	function view_admitted_patient_bal_open() {
		var checkbox = document.getElementById('view_admitted_patient_visble');
		if (checkbox.checked) {
			if (getCookie('hideAdmittedPatientBalModal') === 'true') {
				setCookie('hideAdmittedPatientBalModal', '', -1); // delete cookie
			}
			document.getElementById('view_admitted_patient_bal1').click();
		}
	}






	$(document).on('click', '.view_admitted_patient_bal', function() {
		if (getCookie('hideAdmittedPatientBalModal') === 'true') {
			return;
		} else {
			document.getElementById("notification_label").style.display = "none";

		}

		var admitted_patient_outstanding = 1; // or $(this).attr('id') if needed

		$.ajax({
			url: "fetch_dash.php",
			method: "POST",
			data: {
				admitted_patient_outstanding: admitted_patient_outstanding
			},
			dataType: 'json',
			success: function(data) {
				$('.modal-title').text('Admitted Patients With Outstanding Bills');

				let tableHTML = `
        <table class="table table-striped table-bordered">
          <thead>
            <tr>
              <th>Hospital #</th>
              <th>Patient Name</th>
              <th>Current Balance (₦)</th>
              <th>Outstanding (₦)</th>
              <th>Deposit Required Now (₦)</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            ${data.cr_table}
          </tbody>
        </table>
   <strong>Summary:</strong><br>
<strong>Total Admitted Patient Deposit Requests: ${data.all_patient_cr_count}</strong><br>
<strong>Total Deposit Amount Requested: ₦${data.All_patient_cr}</strong>


        <div class="form-check mt-3">
          <input type="checkbox" class="form-check-input" id="dontShowAgainCheckbox">
          <label class="form-check-label" for="dontShowAgainCheckbox">Don't show this again</label>
        </div>
      `;

				$('#view_admitted_patient_bal_body').html(tableHTML);
				$('#view_admitted_patient_bal_modal').modal('show');

				if (getCookie('hideAdmittedPatientBalModal') === 'true') {
					$('#dontShowAgainCheckbox').prop('checked', true);
				}
			},
			error: function() {
				$('#view_admitted_patient_bal_body').html('<p class="text-danger">Error loading data.</p>');
				$('#view_admitted_patient_bal_modal').modal('show');
			}
		});
	});

	$(document).on('change', '#dontShowAgainCheckbox', function() {
		if ($(this).is(':checked')) {
			// Ask for confirmation before hiding modal
			//if (confirm("Are you sure you want to hide this Notification?")) {
			setCookie('hideAdmittedPatientBalModal', 'true', 1); // hide for 1 day
			//	} else {
			// User canceled, uncheck the checkbox
			//	$(this).prop('checked', false);
			//}
		} else {
			setCookie('hideAdmittedPatientBalModal', '', -1); // delete cookie
		}
	});





	function setCookie(name, value, days) {
		let expires = "";
		if (days) {
			let date = new Date();
			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
			expires = "; expires=" + date.toUTCString();
		}
		document.cookie = name + "=" + (value || "") + expires + "; path=/";
	}

	function getCookie(name) {
		let nameEQ = name + "=";
		let ca = document.cookie.split(';');
		for (let i = 0; i < ca.length; i++) {
			let c = ca[i];
			while (c.charAt(0) == ' ') c = c.substring(1, c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
		}
		return null;
	}

	$(document).on('change', '#dontShowAgainCheckbox', function() {
		if ($(this).is(':checked')) {
			setCookie('hideAdmittedPatientBalModal', 'true', 30); // hide for 30 days
		} else {
			setCookie('hideAdmittedPatientBalModal', '', -1); // delete cookie if unchecked
		}
	});



	$(document).on('click', '.bill_account_bene', function() {

		var bill_account_bene_id = $(this).attr("id");
		$.ajax({
			url: "fetch_dash.php",
			method: "POST",
			data: {
				bill_account_bene_id: bill_account_bene_id
			},
			success: function(data) {
				$('.modal-title').text('Billed to Account Beneficiary');

				$('#specialist_body').html(data);
				$('#specialist_modal').modal('show');
			}
		});
	});


	$(document).on('click', '.admin_settings', function() {

		var admin_settings_id = $(this).attr("id");
		$.ajax({
			url: "fetch_dash.php",
			method: "POST",
			data: {
				admin_settings_id: admin_settings_id
			},
			success: function(data) {
				$('.modal-title').text('Admin Settings');

				$('#admin_settings_body').html(data);
				$('#admin_settings_modal').modal('show');
			}
		});
	});


	function accept_req(sn, fullname) {

		//alert(fullname);
		//exit;

		$.ajax({
			url: "fetch_dash.php",
			method: "POST",
			data: {
				sn_accept: sn,
				fullname: fullname
			},
			success: function(data) {
				toastr.info(data, 'Attention', {
					timeOut: 5000
				})
				///get_dashboard_updates()
				$('#specialist_modal').modal('hide'); //hide
			}
		});
	}


	$(document).on('click', '.special_pake_list', function() {

		var special_pake_list = $(this).attr("id");
		var del = 100;
		$.ajax({
			url: "../inc/special_package_settings.php",
			method: "POST",
			data: {
				target_table: special_pake_list,
				del: del
			},
			success: function(data) {
				$('.modal-title').text('Special Package Items');

				$('#sp_body').html(data);
				$('#sp_modal').modal('show');
			}
		});
	});



	function Clickheretoprint() {
		var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
		disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
		var content_vlue = document.getElementById("content").innerHTML;

		var docprint = window.open("", "", disp_setting);
		docprint.document.open();
		docprint.document.write('</head><body onLoad="self.print()" style="width: 800px; font-size: 13px; font-family: arial;">');
		docprint.document.write(content_vlue);
		docprint.document.close();
		docprint.focus();
	}
</script>


<script>
	$(document).ready(function() {
		// Load data when page loads
		loadRooms();

		// Form submission handler
		$('#room-form').submit(function(e) {
			e.preventDefault();

			const id = $('#id').val();
			const room = $('#room').val();
			const room_2 = $('#room_2').val();
			const doctor = $('#doctor').val();

			const data = {
				room: room,
				room_2: room_2,
				doctor: doctor,
				action: id ? 'update' : 'create'
			};

			if (id) {
				data.id = id;
			}

			$.ajax({
				url: 'crud_room.php',
				type: 'POST',
				data: data,
				success: function(response) {
					response = JSON.parse(response);
					if (response.success) {
						loadRooms();
						resetForm();
					} else {
						alert('Error: ' + response.message);
					}
				},
				error: function() {
					alert('An error occurred while processing your request.');
				}
			});
		});

		// Cancel button handler
		$('#cancel-btn').click(function() {
			resetForm();
		});

		// Function to load rooms data
		function loadRooms() {
			$.ajax({
				url: 'crud_room.php',
				type: 'POST',
				data: {
					action: 'read'
				},
				success: function(response) {
					response = JSON.parse(response);
					if (response.success) {
						let html = '';
						$.each(response.data, function(index, room) {
							html += `
                            <tr>
                              
                                <td>${room.Consult_Room}</td>
                                <td>${room.fullname}</td>
                                <td class="action-btns">
                                    <button class="btn btn-sm btn-warning edit-btn" data-id="${room.id}">Edit</button>
                                </td>
                            </tr>
                        `;
						});
						$('#rooms-table').html(html);

						// Attach event handlers to the new buttons
						$('.edit-btn').click(editRoom);
						$('.delete-btn').click(deleteRoom);
					} else {
						$('#rooms-table').html('<tr><td colspan="4">No rooms found</td></tr>');
					}
				},
				error: function() {
					///alert('An error occurred while loading data.');
				}
			});
		}

		// Function to edit a room
		function editRoom() {
			const id = $(this).data('id');

			$.ajax({
				url: 'crud_room.php',
				type: 'POST',
				data: {
					action: 'get',
					id: id
				},
				success: function(response) {
					response = JSON.parse(response);
					if (response.success) {
						$('#id').val(response.data.id);
						$('#room_2').val(response.data.Consult_Room);
						$('#room').val(response.data.Consult_Room);
						$('#form-title').text('Edit Room');
						$('#cancel-btn').show();
					} else {
						alert('Error: ' + response.message);
					}
				},
				error: function() {
					alert('An error occurred while fetching room data.');
				}
			});
		}

		// Function to delete a room
		function deleteRoom() {
			if (confirm('Are you sure you want to delete this room?')) {
				const id = $(this).data('id');

				$.ajax({
					url: 'crud_room.php',
					type: 'POST',
					data: {
						action: 'delete',
						id: id
					},
					success: function(response) {
						response = JSON.parse(response);
						if (response.success) {
							loadRooms();
						} else {
							alert('Error: ' + response.message);
						}
					},
					error: function() {
						alert('An error occurred while deleting the room.');
					}
				});
			}
		}

		// Function to reset the form
		function resetForm() {
			$('#id').val('');
			$('#room').val('');
			$('#doctor').val('');
			$('#form-title').text('Add New Room');
			$('#cancel-btn').hide();
		}
	});
</script>