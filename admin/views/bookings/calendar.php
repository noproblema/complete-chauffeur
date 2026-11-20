<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Localize script data
wp_localize_script('admin-calendar', 'chauffeurBookingVars', [
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
        'sun' => __('Sun', 'complete-chauffeur'),
        'mon' => __('Mon', 'complete-chauffeur'),
        'tue' => __('Tue', 'complete-chauffeur'),
        'wed' => __('Wed', 'complete-chauffeur'),
        'thu' => __('Thu', 'complete-chauffeur'),
        'fri' => __('Fri', 'complete-chauffeur'),
        'sat' => __('Sat', 'complete-chauffeur'),
        'time' => __('Time', 'complete-chauffeur'),
        'vehicle' => __('Vehicle', 'complete-chauffeur'),
        'customer' => __('Customer', 'complete-chauffeur')
    ]
]);
?>
<div class="wrap">
    <h1><?php esc_html_e('Bookings Calendar', 'complete-chauffeur'); ?></h1>
    
    <?php $this->display_notices(); ?>
    
    <div class="chauffeur-calendar-nav">
        <div class="calendar-nav-left">
            <button id="prev-button" class="button">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
            </button>
            <button id="today-button" class="button">
                <?php esc_html_e('Today', 'complete-chauffeur'); ?>
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
                   placeholder="<?php esc_attr_e('Select date...', 'complete-chauffeur'); ?>">
            
            <button id="month-view" class="button active calendar-view-button">
                <?php esc_html_e('Month', 'complete-chauffeur'); ?>
            </button>
            <button id="day-view" class="button calendar-view-button">
                <?php esc_html_e('Day', 'complete-chauffeur'); ?>
            </button>
        </div>
    </div>
    
    <div id="chauffeur-calendar"></div>
    <div id="datepicker-container"></div>
</div>




