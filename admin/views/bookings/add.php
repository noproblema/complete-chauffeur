<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$routes = $this->get_routes();
$vehicles = $this->get_vehicles();
?>
<div class="wrap">
    <h1><?php esc_html_e('Add New Booking', 'complete-chauffeur'); ?></h1>

    <?php $this->display_notices(); ?>

    <form method="post" class="chauffeur-admin-form">
        <?php wp_nonce_field('booking_action', 'complete_chauffeur_booking_nonce'); ?>

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="name"><?php esc_html_e('Customer Name', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="name" id="name" class="regular-text" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="email"><?php esc_html_e('Email', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="email" name="email" id="email" class="regular-text" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="phone"><?php esc_html_e('Phone', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="tel" name="phone" id="phone" class="regular-text" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="vehicle"><?php esc_html_e('Vehicle', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <select name="vehicle" id="vehicle" required>
                            <option value=""><?php esc_html_e('-- Select a Vehicle --', 'complete-chauffeur'); ?></option>
                            <?php foreach ($vehicles as $vehicle) : ?>
                                <option value="<?php echo esc_attr($vehicle->class); ?>">
                                    <?php echo esc_html($vehicle->class); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="route_id"><?php esc_html_e('Route', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <select name="route_id" id="route_id" required>
                            <option value=""><?php esc_html_e('-- Select a Route --', 'complete-chauffeur'); ?></option>
                            <?php foreach ($routes as $route) : ?>
                                <option value="<?php echo esc_attr($route->id); ?>">
                                    <?php echo esc_html($route->route_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="pickup_date"><?php esc_html_e('Pickup Date & Time', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="datetime-local" name="pickup_date" id="pickup_date" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="pickup_location"><?php esc_html_e('Pickup Location', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="pickup_location" id="pickup_location" class="regular-text" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="destination"><?php esc_html_e('Destination', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="destination" id="destination" class="regular-text" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="distance"><?php esc_html_e('Distance (km)', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="distance" id="distance" step="0.01" min="0" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="price"><?php esc_html_e('Price', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="price" id="price" step="0.01" min="0" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="status"><?php esc_html_e('Status', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <select name="status" id="status">
                            <option value="pending"><?php esc_html_e('Pending', 'complete-chauffeur'); ?></option>
                            <option value="confirmed"><?php esc_html_e('Confirmed', 'complete-chauffeur'); ?></option>
                            <option value="completed"><?php esc_html_e('Completed', 'complete-chauffeur'); ?></option>
                            <option value="cancelled"><?php esc_html_e('Cancelled', 'complete-chauffeur'); ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="notes"><?php esc_html_e('Notes', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <textarea name="notes" id="notes" rows="5" class="large-text"></textarea>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e('Roundtrip', 'complete-chauffeur'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="roundtrip" value="1">
                            <?php esc_html_e('Yes, this is a roundtrip', 'complete-chauffeur'); ?>
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button(__('Add Booking', 'complete-chauffeur')); ?>
    </form>
</div>
