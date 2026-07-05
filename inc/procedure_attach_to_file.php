<div class="modal fade" id="procedureModal" tabindex="-1" role="dialog" aria-labelledby="procedureModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header" align="right">
                <button type="button" class="btn btn-danger btn-xs" data-dismiss="modal" onclick="acknowled_procedure()">
                    Close & Dont Show Again
                </button>
            </div>
            <div class="modal-header">
                <h2 class="modal-title" id="procedureModalLabel" style="color: blue;">Upcoming Procedures</h2>

                <!-- Move button to the right -->

            </div>

            <div class="modal-body" id="modal-body">
                <!-- Procedure details will be populated here -->
            </div>
        </div>
    </div>
</div>


<script>
    function acknowled_procedure() {
        var acknowled_procedure = "Some Value"; // Replace "Some Value" with actual data you want to send

        $.ajax({
            url: '../inc/acknowledge_reminder.php',
            method: 'POST',
            data: {
                acknowled_procedure: acknowled_procedure
            },
            success: function(data) {
                /// alert(data);
            },
            error: function(xhr, status, error) {
                // Handle errors here
                console.error("Error occurred: ", status, error);
                alert("An error occurred: " + error);
            }
        });
    }


    check_for_procedures('NO');

    function check_for_procedures_front_deck() {
        $.ajax({
            url: "../inc/fetch_procedures_reminder.php",
            method: "POST",
            data: {
                delete_acknowledge_for_frontdesk: true
            },
            success: function(data) {}
        })
        check_for_procedures('YES');
    }

    function check_for_procedures(WHO) {
        /// alert(WHO);

        $.ajax({
            url: '../inc/fetch_procedures_reminder.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log(data); // Log the response for debugging

                // Check if there are any procedures
                if (data.rowCount > 0) {
                    let modalBody = '';
                    const now = new Date(); // Get the current date and time

                    // Iterate over the procedures array
                    data.procedures.forEach(function(procedure) {
                        const procedureDate = new Date(procedure.sDate);
                        const diffTime = procedureDate - now;

                        // Calculate the remaining days and hours
                        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24)); // Days
                        const diffHours = Math.floor((diffTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)); // Hours

                        // Format the remaining time
                        let timeRemaining = '';
                        if (diffDays >= 0) {
                            timeRemaining = `${diffDays} day(s) and ${diffHours} hour(s) remaining`;
                        } else {
                            timeRemaining = 'Procedure has already passed';
                        }

                        // Format the date as "d m Year"
                        const options = {
                            day: '2-digit',
                            month: 'long',
                            year: 'numeric'
                        };
                        const formattedDate = procedureDate.toLocaleDateString('en-US', options);

                        // Create modal content
                        modalBody +=
                            `<p><strong>Hospital No/Name:</strong> ${procedure.hospital_no}  ${procedure.name}</p>
                     <p><strong>Procedure:</strong> ${procedure.procedures}</p>
                     <p><strong>Consultant Name:</strong> ${procedure.consultant_name}</p>
                     <p><strong>Resource Persons:</strong> ${procedure.resource_persons || 'N/A'}</p>
                                         <p><strong>Date:</strong> ${formattedDate}
                    <strong>Time Remaining:</strong> ${timeRemaining}</p>
                     <hr>`;
                    });

                    // Inject the modal content and show the modal
                    $('#modal-body').html(modalBody);
                    $('.modal-title').text('Upcoming Procedures');
                    $('#procedureModal').modal('show');

                } else {
                    // Handle case where no procedures are found
                    if (WHO == 'YES') {
                        <?php if ($_SESSION['rights'] == 'RE' or $_SESSION['rights'] == 'MD') { ?>
                            $('#modal-body').html('<p>No procedures found.</p>');
                            $('.modal-title').text('No Upcoming Procedures');
                            $('#procedureModal').modal('show');
                        <?php } ?>
                    }

                }
            },
            error: function(xhr, status, error) {
                // console.error(xhr);
                // $('#modal-body').html('<p>An error occurred while fetching procedures.</p>');
                // $('#procedureModal').modal('show');
            }
        });


    }
</script>