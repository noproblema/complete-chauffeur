<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enqueue the separate assets
wp_enqueue_style('chauffeur-admin-calendar', CHAUFFEUR_BOOKING_URL . 'admin/assets/css/admin-calendar.css', [], CHAUFFEUR_BOOKING_VERSION);
wp_enqueue_script('chauffeur-admin-calendar', CHAUFFEUR_BOOKING_URL . 'admin/assets/js/admin-calendar.js', ['jquery'], CHAUFFEUR_BOOKING_VERSION, true);

// Localize script data
wp_localize_script('chauffeur-admin-calendar', 'chauffeurBookingVars', [
    'bookings' => array_map(function($booking) {
        return [
            'id' => $booking->id,
            'name' => $booking->name,
            'vehicle' => $booking->vehicle,
            'pickup_date' => $booking->pickup_date,
            'status' => $booking->status
        ];
    }, $bookings),
    'startDate' => $start_date,
    'adminUrl' => admin_url(),
    'locale' => get_locale(),
    'i18n' => [
        'sun' => __('Sun', 'chauffeur-booking'),
        'mon' => __('Mon', 'chauffeur-booking'),
        'tue' => __('Tue', 'chauffeur-booking'),
        'wed' => __('Wed', 'chauffeur-booking'),
        'thu' => __('Thu', 'chauffeur-booking'),
        'fri' => __('Fri', 'chauffeur-booking'),
        'sat' => __('Sat', 'chauffeur-booking'),
        'time' => __('Time', 'chauffeur-booking'),
        'vehicle' => __('Vehicle', 'chauffeur-booking'),
        'customer' => __('Customer', 'chauffeur-booking')
    ]
]);
?>
<div class="wrap">
    <h1><?php esc_html_e('Bookings Calendar', 'chauffeur-booking'); ?></h1>
    
    <?php $this->display_notices(); ?>
    
    <div class="chauffeur-calendar-nav">
        <div class="calendar-nav-left">
            <button id="prev-button" class="button">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
            </button>
            <button id="today-button" class="button">
                <?php esc_html_e('Today', 'chauffeur-booking'); ?>
            </button>
            <button id="next-button" class="button">
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </button>
            
            <div id="current-selection">
                <?php echo esc_html(date_i18n('F Y', strtotime($start_date))); ?>
            </div>
        </div>
        
        <div class="calendar-nav-right">
            <input type="text" id="datepicker-input" 
                   value="<?php echo esc_attr(date('d/m/Y', strtotime($start_date))); ?>"
                   placeholder="<?php esc_attr_e('Select date...', 'chauffeur-booking'); ?>">
            
            <button id="month-view" class="button active calendar-view-button">
                <?php esc_html_e('Month', 'chauffeur-booking'); ?>
            </button>
            <button id="day-view" class="button calendar-view-button">
                <?php esc_html_e('Day', 'chauffeur-booking'); ?>
            </button>
        </div>
    </div>
    
    <div id="chauffeur-calendar"></div>
    <div id="datepicker-container"></div>
</div>


