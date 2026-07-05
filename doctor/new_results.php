   <div id="resultsAlertBox" class="results-available-alert" style="display:none; cursor:pointer;">
   	<div>
   		<b> 👉 View New Results</b>
   		<strong id="doctor_results_count">0</strong>
   	</div>
   </div>
   <div id="doctorQueueAlert" class="queue-alert" style="display:none; cursor:pointer; background:#fff3cd; padding:10px; border-radius:5px;">
   	<b>🧑‍⚕️ View Patients Waiting:</b>
   	<strong id="doctor_queue_count">0</strong>
   </div>

   <div class="modal fade" id="doctorQueueModal">
   	<div class="modal-dialog modal-xl">
   		<div class="modal-content">
   			<div class="modal-header">
   				<h4>Patients Waiting</h4>
   			</div>

   			<div class="modal-body" style="max-height:400px; overflow:auto;">
   				<table class="table table-bordered">
   					<thead>
   						<tr>
   							<th>#</th>
   							<th>Hospital No</th>
   							<th>Name</th>
   							<th>Service</th>
   							<th>Time</th>
   							<th>Action</th>
   						</tr>
   					</thead>
   					<tbody id="doctor_queue_body"></tbody>
   				</table>
   			</div>

   			<div class="modal-footer">
   				<button class="btn btn-secondary" data-dismiss="modal">Close</button>
   			</div>
   		</div>
   	</div>
   </div>

   <div class="modal fade" id="doctorResultsModal">
   	<div class="modal-dialog modal-lg">
   		<div class="modal-content">
   			<div class="modal-header">
   				<h4>My Patients - New Test Results</h4>
   			</div>

   			<div class="modal-body" style="max-height:400px; overflow-y:auto;">
   				<table class="table table-bordered table-striped">
   					<thead>
   						<tr>
   							<th>#</th>
   							<th>Patient</th>
   							<th>Test</th>
   							<th>Date</th>
   							<th>Action</th>
   						</tr>
   					</thead>
   					<tbody id="doctor_results_body"></tbody>
   				</table>
   			</div>

   			<div class="modal-footer">
   				<button class="btn btn-secondary" data-dismiss="modal">Close</button>
   			</div>
   		</div>
   	</div>
   </div>

   <script>
   	var previousDoctorCount = 0;
   	var seconds = 0;

   	// Timer
   	setInterval(function() {
   		seconds++;
   		let hrs = String(Math.floor(seconds / 3600)).padStart(2, '0');
   		let mins = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
   		let secs = String(seconds % 60).padStart(2, '0');
   		$('#resultsTimer').text(hrs + ":" + mins + ":" + secs);
   	}, 1000);


   	// Check new results
   	function checkDoctorResults() {

   		$.ajax({
   			url: 'fetch_doctor_results_count.php',
   			method: 'GET',
   			dataType: 'json',
   			success: function(data) {

   				let newCount = parseInt(data.new_count) || 0;

   				if (newCount > 0) {

   					$('#resultsAlertBox').fadeIn();
   					$('#doctor_results_count').text(newCount);

   					// If NEW results arrived
   					if (newCount > previousDoctorCount) {

   						seconds = 0;

   						// 🔊 Play sound
   						let sound = new Audio('../sounds/notification.wav');
   						sound.play().catch(() => {});

   						// 🔴 FLASH EFFECT
   						$('#resultsAlertBox')
   							.stop(true, true)
   							.css('background-color', '#ffcccc') // light red
   							.animate({
   								opacity: 0.5
   							}, 200)
   							.animate({
   								opacity: 1
   							}, 200)
   							.animate({
   								opacity: 0.5
   							}, 200)
   							.animate({
   								opacity: 1
   							}, 200);

   						// Return to normal after 3 seconds
   						setTimeout(function() {
   							$('#resultsAlertBox').css('background-color', '');
   						}, 3000);
   					}

   				} else {
   					$('#resultsAlertBox').fadeOut();
   				}

   				previousDoctorCount = newCount;
   			}
   		});
   	}


   	// Click alert → open modal
   	$('#resultsAlertBox').on('click', function() {

   		$('#doctorResultsModal').modal('show');

   		$.ajax({
   			url: 'fetch_doctor_results_list.php',
   			method: 'GET',
   			success: function(data) {
   				$('#doctor_results_body').html(data);
   			}
   		});

   	});


   	// Run every 10 seconds
   	setInterval(checkDoctorResults, 10000);
   	checkDoctorResults();
   </script>



   <script>
   	var previousQueueCount = localStorage.getItem('doctor_queue_count') ?
   		parseInt(localStorage.getItem('doctor_queue_count')) : 0;


   	// =========================
   	// CHECK DOCTOR QUEUE
   	// =========================
   	function checkDoctorQueue() {

   		$.ajax({
   			url: 'fetch_doctor_queue_count.php',
   			method: 'GET',
   			dataType: 'json',
   			success: function(data) {

   				let count = parseInt(data.total) || 0;

   				if (count > 0) {

   					$('#doctorQueueAlert').fadeIn();
   					$('#doctor_queue_count').text(count);

   					// NEW ARRIVAL
   					if (count > previousQueueCount) {

   						// 🔊 SOUND
   						let sound = new Audio('../sounds/notification.wav');
   						sound.play().catch(() => {});

   						// 🔴 FLASH
   						$('#doctorQueueAlert')
   							.css('background', '#f8d7da')
   							.fadeOut(200).fadeIn(200)
   							.fadeOut(200).fadeIn(200);

   						setTimeout(() => {
   							$('#doctorQueueAlert').css('background', '#fff3cd');
   						}, 3000);
   					}

   				} else {
   					$('#doctorQueueAlert').fadeOut();
   				}

   				previousQueueCount = count;
   				localStorage.setItem('doctor_queue_count', count);
   			}
   		});
   	}


   	// =========================
   	// CLICK → LOAD MODAL
   	// =========================
   	$('#doctorQueueAlert').on('click', function() {

   		$('#doctorQueueModal').modal('show');

   		$.ajax({
   			url: 'fetch_doctor_queue_list.php',
   			success: function(data) {

   				////alert(data);
   				$('#doctor_queue_body').html(data);
   			}
   		});

   	});


   	// =========================
   	// RUN GLOBAL
   	// =========================
   	setInterval(checkDoctorQueue, 10000);
   	checkDoctorQueue();
   </script>