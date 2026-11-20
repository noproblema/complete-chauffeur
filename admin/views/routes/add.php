<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<div class="wrap">
    <h1><?php esc_html_e('Add New Route', 'complete-chauffeur'); ?></h1>
    
    <?php $this->display_notices(); ?>
    
    <form method="post" class="chauffeur-admin-form">
        <?php wp_nonce_field('route_action', 'complete_chauffeur_route_nonce'); ?>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="route_name"><?php esc_html_e('Route Name', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="route_name" id="route_name" class="regular-text" required>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="start_location"><?php esc_html_e('Start Location', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="start_location" id="start_location" class="regular-text" required>
                    </td>
                </tr>
                
                <!-- Other fields same as edit.php but without values -->
                
            </tbody>
        </table>
        
        <?php submit_button(__('Add Route', 'complete-chauffeur')); ?>
    </form>
</div>




