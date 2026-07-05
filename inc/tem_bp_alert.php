<?php

$vb_date = date('Y-m-d');

// --- Get BP ---
$systolic = $diastolic = null;
$bp_stmt = $db->prepare("
    SELECT bp 
    FROM vital_sign 
    WHERE hospital_no = ? 
      AND DATE(date_ap) = ? 
      AND status = '1' 
      AND bp IS NOT NULL 
    ORDER BY sn DESC 
    LIMIT 1
");
$bp_stmt->execute([$hospital_no, $vb_date]);

if ($row = $bp_stmt->fetch(PDO::FETCH_ASSOC)) {
	$vpp = $row['bp'];
	list($systolic, $diastolic) = explode('/', $vpp);
}

// --- Get Temperature ---
$temps_value = null;
$temp_stmt = $db->prepare("
    SELECT temp 
    FROM vital_sign 
    WHERE hospital_no = ? 
      AND DATE(date_ap) = ? 
      AND status = '1' 
      AND temp IS NOT NULL 
    ORDER BY sn DESC 
    LIMIT 1
");
$temp_stmt->execute([$hospital_no, $vb_date]);

if ($row = $temp_stmt->fetch(PDO::FETCH_ASSOC)) {
	$temps_value = $row['temp'];
}


?>


<script>
	$(document).ready(function() {

		var sys = parseFloat('<?php echo $systolic; ?>') || 0;
		var dia = parseFloat('<?php echo $diastolic; ?>') || 0;
		var temp = parseFloat('<?php echo $temps_value; ?>') || 0;

		var alerts = [];

		/* ================= BP CHECK ================= */
		if (sys > 0 && dia > 0) {

			if (sys >= 180 || dia >= 120) {
				alerts.push("🚨 CRISIS BP: " + sys + "/" + dia);

			} else if (sys >= 140 || dia >= 90) {
				alerts.push("High BP: " + sys + "/" + dia);

			} else if ((sys >= 130 && sys <= 139) || (dia > 80 && dia <= 89)) {
				alerts.push("Elevated BP: " + sys + "/" + dia);
			}
		}

		/* ================= TEMPERATURE CHECK ================= */
		if (temp > 0) {

			if (temp >= 39.0) {
				alerts.push("🚨 High Fever: " + temp + "°C");

			} else if (temp >= 38.0) {
				alerts.push("Fever: " + temp + "°C");

			} else if (temp >= 37.6) {
				alerts.push("Mild Fever: " + temp + "°C");
			}
		}

		/* ================= BLINK FUNCTION ================= */
		function blink(selector) {
			$(selector).fadeOut(600).fadeIn(600, function() {
				blink(this);
			});
		}

		/* ================= SHOW ALERT ================= */
		if (alerts.length > 0) {

			var message = alerts.join("  ||  ");

			$('#msg').html(
				'<div style="display:inline-block;color:white;background:#FE2E2E;padding:4px 8px;font-weight:bold;line-height:1.2;font-size:13px;border-radius:4px;">' +
				'⚠️ ALERT: ' + message +
				'</div>'
			);

			blink('#msg');
		}

	});
</script>