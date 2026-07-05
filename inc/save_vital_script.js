
$(document).ready(function() {
    $('#vitals-form').on('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        // Gather form data
        var formData = $(this).serialize();

        // Send AJAX request

   
        $.ajax({
            url: '../inc/save_vitals.php', // Change this to your PHP script that processes the form
            type: 'POST',
            data: formData,
            success: function(response) {
                // Handle success response
                alert(response); // You can customize this to show a success message
                load_more_vital(1);
                $('#vitals-form')[0].reset();
            },
            error: function(xhr, status, error) {
                // Handle error response
                alert('An error occurred: ' + error);
            }
        });
    });
});
