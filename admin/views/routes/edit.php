<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Get the current route from the admin controller
$route = $this->get_current_route();
?>
<div class="wrap">
    <h1><?php echo $route ? __('Edit Route', 'complete-chauffeur') : __('Add New Route', 'complete-chauffeur'); ?></h1>
    
    <?php $this->display_notices(); ?>
    
    <form method="post" class="chauffeur-admin-form">
        <input type="hidden" name="route_id" value="<?php echo $route ? esc_attr($route->id) : ''; ?>">
        
        <?php wp_nonce_field('route_action', 'complete_chauffeur_route_nonce'); ?>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="route_name"><?php esc_html_e('Route Name', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="route_name" id="route_name" class="regular-text" 
                               value="<?php echo $route ? esc_attr($route->route_name) : ''; ?>" required>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="start_location"><?php esc_html_e('Start Location', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="start_location" id="start_location" class="regular-text" 
                               value="<?php echo $route ? esc_attr($route->start_location) : ''; ?>" required>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="end_location"><?php esc_html_e('End Location', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="end_location" id="end_location" class="regular-text" 
                               value="<?php echo $route ? esc_attr($route->end_location) : ''; ?>" required>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="distance"><?php esc_html_e('Distance (km)', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="distance" id="distance" step="0.01" min="0" 
                               value="<?php echo $route ? esc_attr($route->distance) : ''; ?>" required>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="price"><?php esc_html_e('One-Way Price', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="price" id="price" step="0.01" min="0" 
                               value="<?php echo $route ? esc_attr($route->price) : ''; ?>" required>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="roundtrip_price"><?php esc_html_e('Roundtrip Price', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="roundtrip_price" id="roundtrip_price" step="0.01" min="0" 
                               value="<?php echo $route ? esc_attr($route->roundtrip_price) : ''; ?>" required>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="is_active"><?php esc_html_e('Active Route', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="is_active" id="is_active" value="1" 
                            <?php checked($route ? $route->is_active : 1, 1); ?>>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <?php submit_button(__('Save Route', 'complete-chauffeur')); ?>
    </form>
</div>




