<?php

if ($hosp_price > 0) {


    /// required::::: $payment_mode,$interest,$hosp_price,$insurance
    /// $service_access,$nhis_price,$_tariff_table,$hos_no,target_sn
    /// $insurance_no, $ext_price,$db

    $percent = (float)$interest / 100;
    $int_charge = (float)$hosp_price * (float)$percent;
    $ccop_int_charge = (float)$hosp_price * (float)$percent;

    $insurance = strtolower(trim($insurance));

    // NHIS

    if ($insurance == 'nhis' && $NHIS_DRUG_CONSUMBL_STATE == 1) {

        if ($nhis_price > 0) {
            $percent = $interest / 100;
            $amt_paying = $nhis_price * $percent;
            $claim_amt = $nhis_price - $amt_paying;
            $pay_mode = 'cash';
            $ccop_int_charge = $interest;
            $item_amt = $nhis_price;
        } else {

            $item_amt = $hosp_price;
            $amt_paying = ($ext_price > 0) ? $ext_price : $hosp_price;
            $claim_amt = 0;
            $pay_mode = 'cash';
            $ccop_int_charge = 0;
        }
    } elseif ($insurance === 'nhis') {
        $service_access = $service_access ?: '1';
        $stmt = $db->prepare("
						SELECT ap_type, appt_no 
						FROM apptm 
						WHERE hospital_no = :hos_no 
						ORDER BY sn DESC 
						LIMIT 1
					");
        $stmt->bindParam(':hos_no', $hos_no, PDO::PARAM_STR);
        $stmt->execute();

        $rwx = $stmt->fetch(PDO::FETCH_ASSOC);
        $patient_nhis_access = isset($rwx['ap_type']) ? $rwx['ap_type'] : 1;   // default = primary care
        $appt_no             = isset($rwx['appt_no']) ? $rwx['appt_no'] : null;

        // Decide payment mode
        ///if ($service_access <= $patient_nhis_access && $nhis_price > 0) {
        if ($nhis_price > 0) {
            // Covered by NHIS
            $claim_amt      = $nhis_price;
            $amt_paying     = 0;
            $pay_mode       = 'claim';
            $item_amt       = $hosp_price;
            $coverageError  = "0";
            $ccop_int_charge = 0;
        } else {
            // Patient pays cash
            $claim_amt      = 0;
            $amt_paying     = $hosp_price;
            $pay_mode       = 'cash';
            $item_amt       = $hosp_price;
            $coverageError  = "1";
            $nhis_pay       = "1";
            $ccop_int_charge = 0;
        }

        // Common field
        $pay_insured = "pay/insured";

        // PHIS or Corporate
    } elseif (in_array($insurance, ['phis', 'corporate', 'family'])) {
        $seen_status = 0;
        $stmt = $db->prepare("SELECT price FROM $_tariff_table 
				WHERE stock_sn = :target_sn AND hmo = :insurance_no AND price > 0 LIMIT 1");
        $stmt->bindValue(':target_sn', $target_sn, PDO::PARAM_STR);
        $stmt->bindValue(':insurance_no', $insurance_no, PDO::PARAM_STR);
        $stmt->execute();
        if ($row_dx = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $_amt = $row_dx['price'];
            $ccop_int_charge = 0;
            $_amt = ($_amt > 0) ? (float)$_amt : (float)$hosp_price;
            $seen_status = 1;
        } else {
            $_amt = ($add_minus === 'N') ? ($hosp_price - $ccop_int_charge) : ($hosp_price + $ccop_int_charge);
            $seen_status = 2;
        }

        if ($payment_mode == 0) {
            $actual_price = ($ext_price > 0) ? $ext_price : $hosp_price;
            $amt_paying  = $actual_price;
            $claim_amt   = 0;
            $pay_mode    = 'cash';
            $pay_insured = 'pay';
        } else {
            $amt_paying  = 0;
            $claim_amt   = $_amt;
            $pay_mode    = 'claim';
            $pay_insured = 'pay/insured';
        }
        $item_amt = $hosp_price;
        // Private/Cash
    } else {
        $actual_price = ($ext_price > 0) ? $ext_price : $hosp_price;
        $amt_paying     = $actual_price;
        $item_amt       = $actual_price;
        $ccop_int_charge = 0;
        $claim_amt      = 0;
        $pay_mode       = 'cash';
        $pay_insured    = "pay";
    }
} else {
    /// error ////
    $amt_paying   = $item_amt  = 10000000;
    $ccop_int_charge = 0;
    $claim_amt      = 0;
    $pay_mode       = 'cash';
}
