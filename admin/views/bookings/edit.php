<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$booking = $this->get_current_booking();
$routes = $this->get_routes();
$vehicles = $this->get_vehicles();
?>
<div class="wrap">
    <h1>
        <?php echo $booking
            ? esc_html__('Edit Booking', 'complete-chauffeur')
            : esc_html__('Add New Booking', 'complete-chauffeur'); ?>
    </h1>

    <?php $this->display_notices(); ?>

    <form method="post" class="chauffeur-admin-form">
        <input type="hidden" name="booking_id" value="<?php echo $booking ? esc_attr($booking->id) : ''; ?>">

        <?php wp_nonce_field('booking_action', 'complete_chauffeur_booking_nonce'); ?>

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="name"><?php esc_html_e('Customer Name', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="name" id="name" class="regular-text"
                               value="<?php echo $booking ? esc_attr($booking->name) : ''; ?>" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="email"><?php esc_html_e('Email', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="email" name="email" id="email" class="regular-text"
                               value="<?php echo $booking ? esc_attr($booking->email) : ''; ?>" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="phone"><?php esc_html_e('Phone', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="tel" name="phone" id="phone" class="regular-text"
                               value="<?php echo $booking ? esc_attr($booking->phone) : ''; ?>" required>
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
                                <?php $selected = $booking && $booking->vehicle === $vehicle->class; ?>
                                <option value="<?php echo esc_attr($vehicle->class); ?>" <?php selected($selected); ?>>
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
                                <?php $selected = $booking && (int) $booking->route_id === (int) $route->id; ?>
                                <option value="<?php echo esc_attr($route->id); ?>" <?php selected($selected); ?>>
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
                        <input type="datetime-local" name="pickup_date" id="pickup_date"
                               value="<?php echo $booking ? esc_attr($booking->pickup_date) : ''; ?>" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="pickup_location"><?php esc_html_e('Pickup Location', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="pickup_location" id="pickup_location" class="regular-text"
                               value="<?php echo $booking ? esc_attr($booking->pickup_location) : ''; ?>" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="destination"><?php esc_html_e('Destination', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="destination" id="destination" class="regular-text"
                               value="<?php echo $booking ? esc_attr($booking->destination) : ''; ?>" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="distance"><?php esc_html_e('Distance (km)', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="distance" id="distance" step="0.01" min="0"
                               value="<?php echo $booking ? esc_attr($booking->distance) : ''; ?>" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="price"><?php esc_html_e('Price', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="price" id="price" step="0.01" min="0"
                               value="<?php echo $booking ? esc_attr($booking->price) : ''; ?>" required>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="status"><?php esc_html_e('Status', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <?php $current_status = $booking ? $booking->status : 'pending'; ?>
                        <select name="status" id="status">
                            <option value="pending" <?php selected($current_status, 'pending'); ?>><?php esc_html_e('Pending', 'complete-chauffeur'); ?></option>
                            <option value="confirmed" <?php selected($current_status, 'confirmed'); ?>><?php esc_html_e('Confirmed', 'complete-chauffeur'); ?></option>
                            <option value="completed" <?php selected($current_status, 'completed'); ?>><?php esc_html_e('Completed', 'complete-chauffeur'); ?></option>
                            <option value="cancelled" <?php selected($current_status, 'cancelled'); ?>><?php esc_html_e('Cancelled', 'complete-chauffeur'); ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="notes"><?php esc_html_e('Notes', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <textarea name="notes" id="notes" rows="5" class="large-text"><?php
                            echo $booking ? esc_textarea($booking->notes) : '';
                        ?></textarea>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e('Roundtrip', 'complete-chauffeur'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="roundtrip" value="1" <?php checked($booking ? $booking->roundtrip : 0, 1); ?>>
                            <?php esc_html_e('Yes, this is a roundtrip', 'complete-chauffeur'); ?>
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button($booking ? __('Save Booking', 'complete-chauffeur') : __('Add Booking', 'complete-chauffeur')); ?>
    </form>
</div>
