<?php
////////////// UTILITIES
function INV()
{
	return $INV = date('m') . sprintf('%006d', mt_rand(00000, 99999));
}

function patient_discount($db, $staff_discount, $emr, $patient_type, $discount_set, $referral_sn, $insurance_no, &$pf, &$pf_value, &$dsc_chr, &$dura, &$service_type, &$dsc_chr_set, &$post_type, &$count_bal, &$mySearch, &$grp_idv, &$grp_idv_no)
{

	if ($staff_discount == 1) {
		$staff = "staff";
		/// STAFF DISCOUNT
		$stmt_dsc_chr = $db->prepare("SELECT * FROM patient_discount WHERE individual_group_no = :emr AND status='On-going'");
		$stmt_dsc_chr->bindParam(':emr', $staff, PDO::PARAM_STR);
		$stmt_dsc_chr->execute();

		if ($stmt_dsc_chr->rowCount() > 0 && $discount_set == 1) {
			$grp_idv = "idv";
			$grp_idv_no = $emr;
			$mySearch = "individual_group_no='$emr'";
			$dsc_chr_set = 1;
			$rowx = $stmt_dsc_chr->fetch(PDO::FETCH_ASSOC);
			$service_type = $rowx['apply_to_services'];
			$pf = $rowx['percentage_flat'];
			$pf_value = $rowx['percentage_flat_value'];
			$dsc_chr = $rowx['discount_charge'];
			$dura = $rowx['duration'];
			$count_bal = $rowx['count_bal'];
			$post_type = 'one';
		}
	} else {


		$stmt_dsc_chr = $db->prepare("SELECT * FROM patient_discount WHERE individual_group_no = :emr AND status='On-going'");
		$stmt_dsc_chr->bindParam(':emr', $emr, PDO::PARAM_STR);
		$stmt_dsc_chr->execute();

		if ($stmt_dsc_chr->rowCount() > 0 && $discount_set == 1) {
			$grp_idv = "idv";
			$grp_idv_no = $emr;
			$mySearch = "individual_group_no='$emr'";
			$dsc_chr_set = 1;
			$rowx = $stmt_dsc_chr->fetch(PDO::FETCH_ASSOC);
			$service_type = $rowx['apply_to_services'];
			$pf = $rowx['percentage_flat'];
			$pf_value = $rowx['percentage_flat_value'];
			$dsc_chr = $rowx['discount_charge'];
			$dura = $rowx['duration'];
			$count_bal = $rowx['count_bal'];
			$post_type = 'one';

			///}

		} else {
			$dsc_chr_set = 0;

			//---------------  FAMILY/COPERATE/STAFF  ------------------
			if ($patient_type == 'Family' or $patient_type == 'Corporate') {

				$stmt_dsc_chr = $db->prepare("SELECT * FROM patient_discount WHERE individual_group_no = :insurance_no AND status = 'On-going'");
				$stmt_dsc_chr->bindParam(':insurance_no', $insurance_no, PDO::PARAM_STR);
				$stmt_dsc_chr->execute();

				if ($stmt_dsc_chr->rowCount() > 0 && $discount_set == 1) {
					$mySearch = "individual_group_no='$insurance_no'";
					$grp_idv = "grp";
					$grp_idv_no = $insurance_no;
					$dsc_chr_set = 1;
					$rowx = $stmt_dsc_chr->fetch(PDO::FETCH_ASSOC);
					$service_type = $rowx['apply_to_services'];

					$pf = $rowx['percentage_flat'];
					$pf_value = $rowx['percentage_flat_value'];
					$dsc_chr = $rowx['discount_charge'];
					$dura = $rowx['duration'];
					$count_bal = $rowx['count_bal'];
					$post_type = 'all';
				} else {
					$dsc_chr_set = 0;
				}
			}
			//--------------- END OF FAMILY / COPERATE


			//--------------- REFERRAL  ------------------
			if ($patient_type == 'Referral') {
				// Get Referral number
				$stmt_ref = $db->prepare("SELECT sn FROM referrals WHERE sn = :referral_sn");
				$stmt_ref->bindParam(':referral_sn', $referral_sn, PDO::PARAM_STR);
				$stmt_ref->execute();

				if ($stmt_ref->rowCount() > 0) {
					$rx = $stmt_ref->fetch(PDO::FETCH_ASSOC);
					$referralNo = $rx['sn'];

					// Check for discount
					$stmt_dsc_chr = $db->prepare("SELECT * FROM patient_discount WHERE individual_group_no = :referralNo AND status = 'On-going'");
					$stmt_dsc_chr->bindParam(':referralNo', $referralNo, PDO::PARAM_STR);
					$stmt_dsc_chr->execute();

					if ($stmt_dsc_chr->rowCount() > 0 && $discount_set == 1) {
						$mySearch = "individual_group_no='$referralNo'";
						$grp_idv = "grp";   // individual or group
						$grp_idv_no = $referralNo;
						$dsc_chr_set = 1;
						$rowx = $stmt_dsc_chr->fetch(PDO::FETCH_ASSOC);
						$service_type = $rowx['apply_to_services'];
						$pf = $rowx['percentage_flat'];
						$pf_value = $rowx['percentage_flat_value'];
						$dsc_chr = $rowx['discount_charge'];
						$dura = $rowx['duration'];
						$count_bal = $rowx['count_bal'];
						$post_type = 'all';
					} else {
						$dsc_chr_set = 0;
					}
				}
			}
			//--------------- END OF REFERRAL
		}
	}


	//chk($db,$emr,&$dsc_chr_set,&$pf,&$pf_value,&$dsc_chr,&$dura,&$count_bal,&$post_type,&$dsc_chr_set);
}


function selected_items($db, $emr, $item_services, $mySearch, &$pf, &$pf_value, &$dsc_chr, &$dura, &$post_type, &$count_bal, &$dsc_chr_set, &$sn_service)
{

	$stmt_dsc_chr = $db->query("SELECT * FROM patient_discount_services WHERE $mySearch and service_item='$item_services' and status='0'");
	if ($stmt_dsc_chr->rowCount() > 0) {
		$rowx = $stmt_dsc_chr->fetch(PDO::FETCH_ASSOC);
		$pf = $rowx['percentage_flat'];
		$pf_value = $rowx['percentage_flat_value'];
		$dsc_chr = $rowx['discount_charge'];
		$dura = $rowx['duration'];
		$count_bal = $rowx['count_bal'];
		$sn_service = $rowx['sn'];
		$post_type = 'one';
		$dsc_chr_set = 1;
	} else {
		$pf = '';
		$pf_value = '0';
		$dsc_chr = '';
		$dura = '';
		$dsc_chr_set = 0;
		$sn_service = '';
	}
}



function dsc_chr_calc($pf, $dsc_chr_set, $dsc_chr, $cat_type, $service_type, &$pay, $pf_value, &$discount, &$charge, &$total_chr, &$total_dsc, &$post_type, &$count_bal)
{

	if ($pf == 'Flat' and $dsc_chr_set == 1) {
		/// ---------------- flat ---------------------
		if ($dsc_chr == 'Discount') {
			if ($pay == $pf_value) {
				$discount = $pf_value;
				$charge = 0;
			} elseif ($pay > $pf_value) {
				$pay = $pay - $pf_value;
				$discount = $pf_value;
				$charge = 0;
			}
		}
		if ($dsc_chr == 'Charge') {
			$pay = $pay + $pf_value;
			$charge = $pf_value;
			$discount = 0;
		}
		$total_chr = $total_chr + $charge;
		$total_dsc = $total_dsc + $discount;
	} elseif ($pf == 'Percentage' and $dsc_chr_set == 1) {
		///------------------ percentage -----------------
		$cent = $pf_value / 100;

		if ($dsc_chr == 'Discount') {

			if ($pf_value == 100) {
				$discount = $pay;
				$pay = 0;
				$charge = 0;
			} else {
				$cent_calc = $pay * $cent;
				if ($pay > $cent_calc) {
					$pay = $pay - $cent_calc;
					$discount = $cent_calc;
					$charge = 0;
				}
			}
		}
		if ($dsc_chr == 'Charge') {
			$cent_calc = $pay * $cent;
			$pay = $pay + $cent_calc;
			$charge = $cent_calc;
			$discount = 0;
		}
		$total_chr = $total_chr + $charge;
		$total_dsc = $total_dsc + $discount;
	}
	//////////////---------------------- SELECCTED SERVICES /ITEMS ------------------------------------------------------					

}



function chk($db, $emr, &$dsc_chr_set, &$pf, &$pf_value, &$dsc_chr, &$dura, &$count_bal, &$post_type)
{

	$stmt_dsc_chr = $db->prepare("SELECT * FROM patient_discount WHERE individual_group_no = :emr AND status = 'On-going'");
	$stmt_dsc_chr->bindParam(':emr', $emr, PDO::PARAM_STR);
	$stmt_dsc_chr->execute();

	if ($stmt_dsc_chr->rowCount() > 0) {
		$dsc_chr_set = 1;
		$rowx = $stmt_dsc_chr->fetch(PDO::FETCH_ASSOC);
		$service_type = $rowx['apply_to_services'];

		if ($service_type != 'specify') {
			$pf = $rowx['percentage_flat'];
			$pf_value = $rowx['percentage_flat_value'];
			$dsc_chr = $rowx['discount_charge'];
			$dura = $rowx['duration'];
			$count_bal = $rowx['count_bal'];
			$post_type = 'all';
		}
	} else {
		$dsc_chr_set = 0;
	}
}
