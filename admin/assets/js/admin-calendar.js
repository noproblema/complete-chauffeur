jQuery(document).ready(function($) {
    // Initialize the calendar with default settings
    const calendar = initCalendar();
    
    // Set up calendar controls and event handlers
    setupCalendarControls(calendar);
    
    // Load initial bookings data
    loadBookingsData(calendar);
});

/**
 * Initialize the TUI Calendar instance
 */
function initCalendar() {
    return new tui.Calendar('#chauffeur-calendar', {
        defaultView: 'month',
        useCreationPopup: true,
        useDetailPopup: true,
        taskView: false,
        scheduleView: ['time'],
        month: {
            daynames: getTranslatedDayNames(),
            startDayOfWeek: 1, // Monday
            narrowWeekend: false,
            visibleWeeksCount: 6,
            isAlways6Week: false
        },
        week: {
            taskView: false,
            eventView: ['time']
        },
        template: getCalendarTemplates()
    });
}

/**
 * Get translated day names for calendar
 */
function getTranslatedDayNames() {
    return [
        chauffeurBookingVars.i18n.sun,
        chauffeurBookingVars.i18n.mon,
        chauffeurBookingVars.i18n.tue,
        chauffeurBookingVars.i18n.wed,
        chauffeurBookingVars.i18n.thu,
        chauffeurBookingVars.i18n.fri,
        chauffeurBookingVars.i18n.sat
    ];
}

/**
 * Get calendar templates for rendering
 */
function getCalendarTemplates() {
    return {
        monthDayname: function(dayname) {
            return '<span class="calendar-week-dayname-name">' + dayname.label + '</span>';
        },
        monthSchedule: function(schedule) {
            return schedule.title;
        },
        popupDetailTitle: function(schedule) {
            return schedule.title;
        },
        popupDetailBody: function(schedule) {
            return [
                '<div class="calendar-detail-item">',
                '  <span class="calendar-detail-item-label">' + chauffeurBookingVars.i18n.time + ':</span>',
                '  <span class="calendar-detail-item-content">' + 
                    tui.util.formatDate(schedule.start, 'HH:mm') + ' - ' + 
                    tui.util.formatDate(schedule.end, 'HH:mm') + 
                '</span>',
                '</div>',
                '<div class="calendar-detail-item">',
                '  <span class="calendar-detail-item-label">' + chauffeurBookingVars.i18n.vehicle + ':</span>',
                '  <span class="calendar-detail-item-content">' + (schedule.raw.vehicle || '') + '</span>',
                '</div>',
                '<div class="calendar-detail-item">',
                '  <span class="calendar-detail-item-label">' + chauffeurBookingVars.i18n.customer + ':</span>',
                '  <span class="calendar-detail-item-content">' + (schedule.raw.customer || '') + '</span>',
                '</div>'
            ].join('');
        }
    };
}

/**
 * Set up calendar navigation controls
 */
function setupCalendarControls(calendar) {
    // Navigation buttons
    $('#prev-button').click(() => navigateCalendar(calendar, 'prev'));
    $('#next-button').click(() => navigateCalendar(calendar, 'next'));
    $('#today-button').click(() => navigateCalendar(calendar, 'today'));
    
    // View switchers
    $('#month-view').click(() => switchCalendarView(calendar, 'month'));
    $('#day-view').click(() => switchCalendarView(calendar, 'day'));
    
    // Datepicker initialization
    const datepicker = new tui.DatePicker('#datepicker-container', {
        date: new Date(chauffeurBookingVars.startDate),
        input: {
            element: '#datepicker-input',
            format: 'dd/MM/yyyy'
        },
        language: 'en',
        showAlways: false
    });
    
    datepicker.on('change', () => {
        calendar.setDate(datepicker.getDate());
        updateCurrentSelection(calendar);
    });
    
    // Calendar event handlers
    calendar.on('clickDayname', (event) => {
        calendar.setDate(event.date);
        updateCurrentSelection(calendar);
    });
    
    calendar.on('clickSchedule', handleBookingClick);
}

/**
 * Handle calendar navigation
 */
function navigateCalendar(calendar, direction) {
    switch (direction) {
        case 'prev':
            calendar.prev();
            break;
        case 'next':
            calendar.next();
            break;
        case 'today':
            calendar.today();
            break;
    }
    updateCurrentSelection(calendar);
}

/**
 * Switch calendar view mode
 */
function switchCalendarView(calendar, view) {
    calendar.changeView(view);
    $('.calendar-view-button').removeClass('active');
    $(`#${view}-view`).addClass('active');
    updateCurrentSelection(calendar);
}

/**
 * Update the current selection display
 */
function updateCurrentSelection(calendar) {
    const currentDate = calendar.getDate();
    const viewName = calendar.getViewName();
    
    $('#current-selection').text(formatDateDisplay(currentDate, viewName));
    $('#datepicker-input').val(formatDateForPicker(currentDate));
}

/**
 * Format date for display based on view
 */
function formatDateDisplay(date, viewName) {
    const options = {
        month: 'long', 
        year: 'numeric'
    };
    
    if (viewName === 'day') {
        options.weekday = 'long';
        options.day = 'numeric';
        options.month = 'short';
    }
    
    return date.toLocaleDateString(chauffeurBookingVars.locale, options);
}

/**
 * Format date for datepicker input
 */
function formatDateForPicker(date) {
    return [
        date.getDate().toString().padStart(2, '0'),
        (date.getMonth() + 1).toString().padStart(2, '0'),
        date.getFullYear()
    ].join('/');
}

/**
 * Handle booking click events
 */
function handleBookingClick(event) {
    // Implement booking click behavior
    console.log('Booking clicked:', event.schedule);
    
    // Example: Open edit screen for this booking
    // window.location.href = `${chauffeurBookingVars.adminUrl}?page=complete-chauffeurs&action=edit&id=${event.schedule.id}`;
}

/**
 * Load bookings data into calendar
 */
function loadBookingsData(calendar) {
    // Clear existing events
    calendar.clear();
    
    // Create events from bookings data
    const events = chauffeurBookingVars.bookings.map(booking => ({
        id: booking.id,
        calendarId: 'bookings',
        title: `${booking.name} - ${booking.vehicle}`,
        category: 'time',
        dueDateClass: '',
        start: booking.pickup_date,
        end: calculateEndTime(booking.pickup_date),
        isReadOnly: true,
        raw: {
            customer: booking.name,
            vehicle: booking.vehicle,
            status: booking.status
        },
        color: getStatusColor(booking.status),
        bgColor: getStatusColor(booking.status),
        borderColor: getStatusColor(booking.status)
    }));
    
    calendar.createEvents(events);
}

/**
 * Calculate end time for booking events
 */
function calculateEndTime(startTime) {
    const endTime = new Date(startTime);
    endTime.setHours(endTime.getHours() + 1); // 1 hour duration
    return endTime.toISOString();
}

/**
 * Get color for booking status
 */
function getStatusColor(status) {
    const colors = {
        'pending': '#ff9800',
        'confirmed': '#4caf50',
        'completed': '#2196f3',
        'cancelled': '#f44336'
    };
    return colors[status] || '#9e9e9e';
}




