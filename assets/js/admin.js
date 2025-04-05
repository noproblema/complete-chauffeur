jQuery(document).ready(function ($) {
    // Define the ChauffeurCalendar class
    class ChauffeurCalendar extends tui.Calendar {
        constructor(container, options) {
            // Call the parent class constructor
            super(container, options);

            // Initialize the calendar
            this.initialize();
        }

        /**
         * Initialize the calendar with event listeners and default settings.
         */
        initialize() {
            // Add event listener for date/time selection
            this.on('selectDateTime', (event) => this.handleDateTimeSelect(event));

            // Fetch bookings and add them to the calendar
            this.fetchBookings();

            // Add navigation event handlers
            this.addNavigationHandlers();

            // Add view switch event handlers (month/day)
            this.addViewSwitchHandlers();

            // Initialize the Toast UI DatePicker
            this.initializeDatePicker();
        }

        /**
         * Handle date/time selection.
         * @param {Object} event - The event object containing the selected date/time.
         */
        handleDateTimeSelect(event) {
            const clickedDate = event.start; // Get the clicked date
            this.setDate(clickedDate); // Set calendar date to the clicked date
            this.updateCurrentSelection(); // Update the current selection display
            this.updateDatePicker(); // Update the datepicker
        }

        /**
         * Fetch bookings and add them to the calendar.
         */
        fetchBookings() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fetch_bookings',
                    nonce: chauffeur_admin_ajax.nonce
                },
                success: (response) => {
                    if (response.success) {
                        // Add bookings to the calendar
                        this.createEvents(response.data);
                    } else {
                        console.error('Failed to fetch bookings:', response.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX error:', error);
                }
            });
        }

        /**
         * Add navigation event handlers for Previous, Next, and Today buttons.
         */
        addNavigationHandlers() {
            // Previous button
            $('#prev-button').on('click', () => {
                this.prev();
                this.updateCurrentSelection();
                this.updateDatePicker(); // Update the datepicker
            });

            // Next button
            $('#next-button').on('click', () => {
                this.next();
                this.updateCurrentSelection();
                this.updateDatePicker(); // Update the datepicker
            });

            // Today button
            $('#today-button').on('click', () => {
                this.today();
                this.updateCurrentSelection();
                this.updateDatePicker(); // Update the datepicker
            });
        }

        /**
         * Add event handlers for switching between month and day views.
         */
        addViewSwitchHandlers() {
            // Month view button
            $('#month-view').on('click', () => {
                this.changeView('month');
                this.updateCurrentSelection();
                this.updateButtonActiveState('month');
                this.updateDatePicker(); // Update the datepicker
            });

            // Day view button
            $('#day-view').on('click', () => {
                this.changeView('day');
                this.updateCurrentSelection();
                this.updateButtonActiveState('day');
                this.updateDatePicker(); // Update the datepicker
            });
        }

        /**
         * Initialize the Toast UI DatePicker.
         */
        initializeDatePicker() {
            const $datepickerInput = $('#datepicker-input');
            const $datepickerContainer = $('#datepicker-container');

            // Initialize the Toast UI DatePicker
            this.datepicker = new tui.DatePicker($datepickerContainer[0], {
                date: this.getDate().toDate(), // Set initial date
                input: {
                    element: $datepickerInput[0], // Input element for the datepicker
                    format: 'd/M/yyyy' // Format: dd/MM/yyyy
                },
                language: 'en', // Set language
                showAlways: false, // Do not show the datepicker by default
                selectableRanges: [
                    // Define selectable date range (optional)
                    [new Date(2000, 0, 1), new Date(2100, 11, 31)]
                ]
            });

            // Handle date selection
            this.datepicker.on('change', () => {
                const selectedDate = this.datepicker.getDate(); // Get the selected date from the datepicker object
                console.log(`Selected date: ${selectedDate}`); // Log the selected date (for debugging)
                this.setDate(selectedDate); // Set the calendar date
                this.updateCurrentSelection(); // Update the current selection display
            });
        }

        /**
         * Update the current selection display.
         */
        updateCurrentSelection() {
            const currentDate = this.getDate().toDate(); // Convert tzDate to native Date
            const viewName = this.getViewName();

            let selectionText = '';

            if (viewName === 'month') {
                // Format: Month Year (e.g., January 2023)
                selectionText = currentDate.toLocaleString('default', { month: 'long', year: 'numeric' });
            } else if (viewName === 'day') {
                // Format: Weekday, d MMM yyyy (e.g., Monday, 2 Jan 2023)
                selectionText = currentDate.toLocaleString('default', { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' });
            }

            $('#current-selection').text(selectionText);
        }

        /**
         * Update the Toast UI DatePicker to reflect the current calendar date.
         */
        updateDatePicker() {
            const currentDate = this.getDate().toDate(); // Convert tzDate to native Date

            if (this.datepicker) {
                // Set the datepicker value
                this.datepicker.setDate(currentDate);
            }
        }

        /**
         * Update the active state of the view buttons (month/day).
         * @param {string} activeView - The currently active view ('month' or 'day').
         */
        updateButtonActiveState(activeView) {
            $('#month-view, #day-view').removeClass('active');
            $(`#${activeView}-view`).addClass('active');
        }
    }

    // Check if the current page is the calendar page
    if ($('body').hasClass('toplevel_page_chauffeur-bookings')) {
        // Initialize the ChauffeurCalendar
        const calendar = new ChauffeurCalendar('#calendar', {
            defaultView: 'month', // Default to month view
            useCreationPopup: true,
            useDetailPopup: true,
            useNav: true, // Enable navigation tools
            month: {
                daynames: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                startDayOfWeek: 1, // Start the week on Monday
                narrowWeekend: false, // Show full-width weekends
                isAlways6Week: false // Always show 6 weeks
            },
            week: {
                taskView: false, // Disable task view in week/day view
                eventView: ['time'] // Only show time-based events (bookings)
            }
        });
    }

    /**
     * Function to initialize the bookings list page.
     */
    function initializeBookingsListPage() {
        // Handle "Load More" button click
        $('#load-more-bookings').on('click', function () {
            var button = $(this);
            var offset = button.data('offset');

            // Disable the button to prevent multiple clicks
            button.prop('disabled', true);

            // Send AJAX request to load more bookings
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fetch_bookings',
                    nonce: chauffeur_admin_ajax.nonce,
                    offset: offset,
                    count: 10
                },
                success: function (response) {
                    if (response.success) {
                        // Append the new rows to the table
                        $('#bookings-body').append(response.data.html);

                        // Update the offset for the next request
                        button.data('offset', response.data.offset);

                        // Hide the button if there are no more bookings to load
                        if (!response.data.has_more) {
                            button.hide();
                        }
                    } else {
                        console.error('Failed to load more bookings:', response.data);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', error);
                },
                complete: function () {
                    // Re-enable the button
                    button.prop('disabled', false);
                }
            });
        });
    }

    // Check if the current page is the bookings list page
    if ($('body').hasClass('chauffeur-bookings_page_chauffeur-bookings-table')) {
        initializeBookingsListPage();
    }
});
