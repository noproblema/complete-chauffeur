// JavaScript to handle form interactions
jQuery(document).ready(function ($) {
    const form = $('#chauffeur-booking-form');
    const loadingSpinner = $('#loading-spinner');
    const routeSelect = $('#chbo_route');
    const vehicleSelect = $('#chbo_vehicle');
    const roundtripCheckbox = $('#chbo_roundtrip');
    const reverseButton = $('#reverse-route-button');
    const testButton = $('#test-button');
debugger;
    // Function to calculate the final price
    function updatePrice() {
        const selectedRoute = routeSelect.find(':selected');
        const selectedVehicle = vehicleSelect.find(':selected');
        const isRoundtrip = roundtripCheckbox.is(':checked');

        // Get the base price (roundtrip or one-way)
        const basePrice = isRoundtrip ? parseFloat(selectedRoute.data('roundtrip-price')) : parseFloat(selectedRoute.data('price'));

        // Get the vehicle price rate (percentage)
        const vehicleRate = parseFloat(selectedVehicle.data('price-rate')) || 0;

        // Calculate the final price
        const finalPrice = (basePrice * vehicleRate) / 100;

        // Update the price field
        $('#chbo_price').val(finalPrice.toFixed(2));
    }

    // Handle route selection change
    routeSelect.on('change', function () {
        const selectedOption = $(this).find(':selected');
        const startLocation = selectedOption.data('start');
        const endLocation = selectedOption.data('end');
        const distance = selectedOption.data('distance');

        // Update pickup and destination fields
        $('#chbo_pickup_location').val(startLocation);
        $('#chbo_destination').val(endLocation);

        // Update distance field
        $('#chbo_distance').val(distance);

        // Update price
        updatePrice();
    });

    // Handle vehicle selection change
    vehicleSelect.on('change', function () {
        updatePrice();
    });

    // Handle roundtrip checkbox change
    roundtripCheckbox.on('change', function () {
        updatePrice();
    });

    // Handle reverse route button click
    reverseButton.on('click', function () {
       const pickupLocation = $('#chbo_pickup_location').val();
       const destination = $('#chbo_destination').val();

       // Swap pickup and destination values
       $('#chbo_pickup_location').val(destination);
       $('#chbo_destination').val(pickupLocation);
    });

    // Handle test data button click (for admins)
    testButton.on('click', function () {
        // Generate a random date within the next 30 days
        const randomDate = new Date();
        randomDate.setDate(randomDate.getDate() + Math.floor(Math.random() * 30));
        const formattedDate = randomDate.toISOString().slice(0, 16); // Format as YYYY-MM-DDTHH:MM

        // Prefill the form fields with test data
        $('input[name="chbo_name"]').val('John Doe');
        $('input[name="chbo_email"]').val('johndoe@example.com');
        $('input[name="chbo_phone"]').val('123-456-7890');
        $('select[name="chbo_vehicle"]').val('sedan');
        $('select[name="chbo_route"]').val('1'); // Assuming route ID 1 exists
        $('input[name="chbo_pickup_date"]').val(formattedDate);

        // Trigger the change event on the route dropdown to update pickup, destination, distance, and price
        routeSelect.trigger('change');

        // Set roundtrip checkbox
        $('input[name="chbo_roundtrip"]').prop('checked', true);
    });

    // Handle form submission
    form.on('submit', function (e) {
        e.preventDefault();

        // Show the confirmation modal using SweetAlert
        Swal.fire({
            title: 'Confirm Booking',
            text: 'Are you sure you want to submit this booking?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, submit it!',
            cancelButtonText: 'No, cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                // Show the loading spinner
                loadingSpinner.show();

                // Submit the form via AJAX
                const formData = new FormData(this);
                $.ajax({
                    url: chauffeur_frontend_ajax.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-WP-Nonce': chauffeur_frontend_ajax.nonce, // Include the nonce for security
                    },
                    success: function (data) {
                        // Hide the loading spinner
                        loadingSpinner.hide();

                        if (data.success) {
                            // Display a success message
                            Swal.fire('Success!', data.message, 'success');
                            form[0].reset(); // Reset the form
                        } else {
                            // Display an error message
                            Swal.fire('Error!', data.message, 'error');
                        }
                    },
                    error: function (xhr, status, error) {
                        // Hide the loading spinner
                        loadingSpinner.hide();

                        // Display an error message
                        Swal.fire('Error!', 'Failed to submit booking. Please try again.', 'error');
                        console.error('Error:', error);
                    },
                });
            }
        });
    });

    // Initialize Flatpickr for the pickup date and time field
    if ($('#chbo_pickup_date').length) {
        debugger;
        $('#chbo_pickup_date').flatpickr({
            enableTime: true, // Enable time selection
            dateFormat: 'Y-m-d H:i', // Date and time format
            minDate: 'today', // Disable past dates
            time_24hr: true, // Use 24-hour format
            onChange: function (selectedDates, dateStr, instance) {
                console.log('Selected Date & Time:', dateStr);
            }
        });
    }
});


