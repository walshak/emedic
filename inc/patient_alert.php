			<div class="modal fade" id="patientAlertModal" tabindex="-1" aria-labelledby="patientAlertLabel" aria-hidden="true">
				<div class="modal-dialog modal-lg modal-dialog-scrollable">
					<div class="modal-content">
						<div class="modal-header bg-warning" style="background-color: #dc3545;">
							<h2 class="modal-title" id="patientAlertLabel" style="color:#f8f9fa; text-align: center; ">⚠️ Patient Alerts ⚠️</h2>
						</div>
						<div class="modal-body" id="patient-alert-modal-body">
							<!-- Alerts will be injected here -->


						</div>
					</div>
				</div>
			</div>

			<script>
				document.addEventListener("DOMContentLoaded", function() {
					display_alert_('<?php echo $hospital_no; ?>');
				});


				function display_alert_(hosp_no) {
					$.ajax({
						url: "../inc/set_alert_patients.php",
						method: "POST",
						data: {
							check_alert: hosp_no
						},
						success: function(data) {

							if (data.trim() !== '') {
								$('#patient-alert-modal-body').html(data);
								$('#patientAlertModal').modal('show');
							}
						}
					});
				}
			</script>