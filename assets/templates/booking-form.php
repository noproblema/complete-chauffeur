<?php
// /templates/booking-form.php

/**
 * Booking Form Template
 * 
 * @package ChauffeurBooking
 * @var Chauffeur_Form_Handler $form_handler
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Get available routes and vehicles from the form handler
$routes = $form_handler->get_routes();
$vehicles = $form_handler->get_vehicles();
?>

<div class="chauffeur-booking-form-wrapper">
    <form id="chauffeur-booking-form" method="post" class="needs-validation" novalidate>
        <?php wp_nonce_field('chauffeur_booking_form', 'chauffeur_booking_nonce'); ?>
        
        <div class="form-section personal-details">
            <h3><?php esc_html_e('Personal Details', 'chauffeur-booking'); ?></h3>
            
            <div class="form-group">
                <label for="chbo_name"><?php esc_html_e('Full Name', 'chauffeur-booking'); ?> *</label>
                <input type="text" name="chbo_name" id="chbo_name" class="form-control" required 
                       value="<?php echo esc_attr($form_handler->get_field_value('name')); ?>">
                <div class="invalid-feedback">
                    <?php esc_html_e('Please provide your name', 'chauffeur-booking'); ?>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="chbo_email"><?php esc_html_e('Email', 'chauffeur-booking'); ?> *</label>
                        <input type="email" name="chbo_email" id="chbo_email" class="form-control" required
                               value="<?php echo esc_attr($form_handler->get_field_value('email')); ?>">
                        <div class="invalid-feedback">
                            <?php esc_html_e('Please provide a valid email', 'chauffeur-booking'); ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="chbo_phone"><?php esc_html_e('Phone', 'chauffeur-booking'); ?> *</label>
                        <input type="tel" name="chbo_phone" id="chbo_phone" class="form-control" required
                               value="<?php echo esc_attr($form_handler->get_field_value('phone')); ?>">
                        <div class="invalid-feedback">
                            <?php esc_html_e('Please provide your phone number', 'chauffeur-booking'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-section booking-details">
            <h3><?php esc_html_e('Booking Details', 'chauffeur-booking'); ?></h3>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="chbo_vehicle"><?php esc_html_e('Vehicle Type', 'chauffeur-booking'); ?> *</label>
                        <select name="chbo_vehicle" id="chbo_vehicle" class="form-control" required>
                            <option value=""><?php esc_html_e('-- Select Vehicle --', 'chauffeur-booking'); ?></option>
                            <?php foreach ($vehicles as $vehicle) : ?>
                                <option value="<?php echo esc_attr($vehicle->class); ?>" 
                                    <?php selected($form_handler->get_field_value('vehicle'), $vehicle->class); ?>
                                    data-price-rate="<?php echo esc_attr($vehicle->price_rate); ?>">
                                    <?php echo esc_html($vehicle->class); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="chbo_pickup_date"><?php esc_html_e('Pickup Date & Time', 'chauffeur-booking'); ?> *</label>
                        <input type="datetime-local" name="chbo_pickup_date" id="chbo_pickup_date" 
                               class="form-control flatpickr" required 
                               value="<?php echo esc_attr($form_handler->get_field_value('pickup_date')); ?>">
                    </div>
                </div>
            </div>
            
            <div class="form-group route-selection">
                <label for="chbo_route"><?php esc_html_e('Select Route', 'chauffeur-booking'); ?> *</label>
                <div class="input-group">
                    <select name="chbo_route" id="chbo_route" class="form-control" required>
                        <option value=""><?php esc_html_e('-- Select Route --', 'chauffeur-booking'); ?></option>
                        <?php foreach ($routes as $route) : ?>
                            <option value="<?php echo esc_attr($route->id); ?>"
                                data-start="<?php echo esc_attr($route->start_location); ?>"
                                data-end="<?php echo esc_attr($route->end_location); ?>"
                                data-distance="<?php echo esc_attr($route->distance); ?>"
                                data-price="<?php echo esc_attr($route->price); ?>"
                                data-roundtrip-price="<?php echo esc_attr($route->roundtrip_price); ?>"
                                <?php selected($form_handler->get_field_value('route_id'), $route->id); ?>>
                                <?php echo esc_html($route->route_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-append">
                        <button type="button" id="reverse-route" class="btn btn-outline-secondary">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="chbo_pickup_location"><?php esc_html_e('Pickup Location', 'chauffeur-booking'); ?> *</label>
                        <input type="text" name="chbo_pickup_location" id="chbo_pickup_location" 
                               class="form-control" required readonly
                               value="<?php echo esc_attr($form_handler->get_field_value('pickup_location')); ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="chbo_destination"><?php esc_html_e('Destination', 'chauffeur-booking'); ?> *</label>
                        <input type="text" name="chbo_destination" id="chbo_destination" 
                               class="form-control" required readonly
                               value="<?php echo esc_attr($form_handler->get_field_value('destination')); ?>">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="chbo_distance"><?php esc_html_e('Distance (km)', 'chauffeur-booking'); ?></label>
                        <input type="text" name="chbo_distance" id="chbo_distance" class="form-control" readonly
                               value="<?php echo esc_attr($form_handler->get_field_value('distance')); ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="chbo_price"><?php esc_html_e('Price', 'chauffeur-booking'); ?></label>
                        <input type="text" name="chbo_price" id="chbo_price" class="form-control" readonly
                               value="<?php echo esc_attr($form_handler->get_field_value('price')); ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group form-check">
                        <input type="checkbox" name="chbo_roundtrip" id="chbo_roundtrip" 
                               class="form-check-input" <?php checked($form_handler->get_field_value('roundtrip'), 1); ?>>
                        <label class="form-check-label" for="chbo_roundtrip">
                            <?php esc_html_e('Round Trip?', 'chauffeur-booking'); ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="chbo_notes"><?php esc_html_e('Special Instructions', 'chauffeur-booking'); ?></label>
            <textarea name="chbo_notes" id="chbo_notes" class="form-control" rows="3"><?php 
                echo esc_textarea($form_handler->get_field_value('notes')); 
            ?></textarea>
        </div>
        
        <div class="form-submit">
            <button type="submit" name="chbo_submit" class="btn btn-primary">
                <?php esc_html_e('Confirm Booking', 'chauffeur-booking'); ?>
            </button>
            
            <?php if (current_user_can('manage_options')) : ?>
                <button type="button" id="fill-test-data" class="btn btn-outline-secondary ml-2">
                    <?php esc_html_e('Fill Test Data', 'chauffeur-booking'); ?>
                </button>
            <?php endif; ?>
        </div>
        
        <div id="form-feedback" class="mt-3"></div>
    </form>
</div>

<!-- Loading Indicator -->
<div id="chauffeur-loading" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="sr-only"><?php esc_html_e('Loading...', 'chauffeur-booking'); ?></span>
    </div>
</div>
