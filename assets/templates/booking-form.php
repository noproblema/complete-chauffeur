<?php

/**
 * Booking Form Template
 * 
 * @package ChauffeurBooking
 * @var Complete_Chauffeur_Form_Handler $this
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="chauffeur-booking-form">
    <h2><?php echo esc_html__('🌴 Book a Chauffeur Service 🌴', 'complete-chauffeur'); ?></h2>
    <form id="chauffeur-booking-form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
        <!-- Nonce field for security -->
        <?php wp_nonce_field('complete_chauffeur_booking_form', 'complete_chauffeur_booking_nonce'); ?>

        <!-- Name field -->
        <label for="chbo_name"><?php echo esc_html__('Name:', 'complete-chauffeur'); ?></label>
        <input type="text" name="chbo_name" id="chbo_name" required>

        <!-- Email field -->
        <label for="chbo_email"><?php echo esc_html__('Email:', 'complete-chauffeur'); ?></label>
        <input type="email" name="chbo_email" id="chbo_email" required>

        <!-- Phone field -->
        <label for="chbo_phone"><?php echo esc_html__('Phone:', 'complete-chauffeur'); ?></label>
        <input type="tel" name="chbo_phone" id="chbo_phone" required>

        <!-- Vehicle selection -->
        <label for="chbo_vehicle"><?php echo esc_html__('Vehicle Type:', 'complete-chauffeur'); ?></label>
        <select name="chbo_vehicle" id="chbo_vehicle" required>
            <option value=""><?php echo esc_html__('-- Select a Vehicle --', 'complete-chauffeur'); ?></option>
            <?php foreach ($vehicles as $vehicle) : ?>
                <option value="<?php echo esc_attr($vehicle->class); ?>"
                        data-price-rate="<?php echo esc_attr($vehicle->price_rate); ?>">
                    <?php echo esc_html($vehicle->class); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Route selection and Reverse Route button -->
        <div class="route-selection">
            <label for="chbo_route"><?php echo esc_html__('Select Route:', 'complete-chauffeur'); ?></label>
            <div class="route-select-wrapper">
                <select name="chbo_route" id="chbo_route" required>
                    <option value=""><?php echo esc_html__('-- Select a Route --', 'complete-chauffeur'); ?></option>
                    <?php foreach ($routes as $route) : ?>
                        <option value="<?php echo esc_attr($route->id); ?>"
                                data-start="<?php echo esc_attr($route->start_location); ?>"
                                data-end="<?php echo esc_attr($route->end_location); ?>"
                                data-distance="<?php echo esc_attr($route->distance); ?>"
                                data-price="<?php echo esc_attr($route->price); ?>"
                                data-roundtrip-price="<?php echo esc_attr($route->roundtrip_price); ?>">
                            <?php echo esc_html($route->route_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="reverse-route-button" class="button" title="<?php echo esc_attr__('Reverse Route', 'complete-chauffeur'); ?>">
                    <i class="fas fa-exchange-alt"></i> <!-- FontAwesome reverse icon -->
                </button>
            </div>
        </div>

        <!-- Pickup date and time -->
        <div class="flatpickr input-group" data-wrap="true">
            <label for="chbo_pickup_date"><?php echo esc_html__('Pickup Date & Time:', 'complete-chauffeur'); ?></label>
            <input type="text" name="chbo_pickup_date" id="chbo_pickup_date" data-input>

            <a class="input-btn" title="toggle" data-toggle>
                <i class="icon-calendar"></i>
            </a>

            <a class="input-btn" title="clear" data-clear>
                <i class="icon-close"></i>
            </a>
        </div>

        <!-- Pickup location -->
        <label for="chbo_pickup_location"><?php echo esc_html__('Pickup Location:', 'complete-chauffeur'); ?></label>
        <input type="text" name="chbo_pickup_location" id="chbo_pickup_location" required>

        <!-- Destination -->
        <label for="chbo_destination"><?php echo esc_html__('Destination:', 'complete-chauffeur'); ?></label>
        <input type="text" name="chbo_destination" id="chbo_destination" required>

        <!-- Distance and price fields (read-only) -->
        <div class="inline-fields">
            <div class="field">
                <label for="chbo_distance"><?php echo esc_html__('Distance (km):', 'complete-chauffeur'); ?></label>
                <input type="text" name="chbo_distance" id="chbo_distance" readonly>
            </div>
            <div class="field">
                <label for="chbo_price"><?php echo esc_html__('Price:', 'complete-chauffeur'); ?></label>
                <input type="text" name="chbo_price" id="chbo_price" readonly>
            </div>
        </div>

        <!-- Roundtrip option -->
        <div class="roundtrip-field">
            <input type="checkbox" name="chbo_roundtrip" id="chbo_roundtrip">
            <label for="chbo_roundtrip"><?php echo esc_html__('Roundtrip', 'complete-chauffeur'); ?></label>
        </div>

        <!-- Submit button -->
        <div class="submit-button">
            <?php if (current_user_can('administrator')) : ?>
                <!-- Test data button for admins -->
                <button type="button" id="test-button"><?php echo esc_html__('Fill Test Data', 'complete-chauffeur'); ?></button>
            <?php endif; ?>
            <input type="submit" name="chbo_submit" value="<?php echo esc_attr__('Book Now', 'complete-chauffeur'); ?>">
        </div>

        <!-- Loading spinner -->
        <div id="loading-spinner" style="display: none;">
            <div class="spinner"></div>
            <p><?php echo esc_html__('Submitting your booking...', 'complete-chauffeur'); ?></p>
        </div>
    </form>
</div>
</div>




