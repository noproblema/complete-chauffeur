<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$vehicle = $this->get_current_vehicle();
?>
<div class="wrap">
    <h1><?php echo $vehicle ? __('Edit Vehicle', 'complete-chauffeur') : __('Add New Vehicle', 'complete-chauffeur'); ?></h1>
    
    <?php $this->display_notices(); ?>
    
    <form method="post" class="chauffeur-admin-form" enctype="multipart/form-data">
        <input type="hidden" name="vehicle_id" value="<?php echo $vehicle ? esc_attr($vehicle->id) : ''; ?>">
        
        <?php wp_nonce_field('vehicle_action', 'complete_chauffeur_vehicle_nonce'); ?>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="class"><?php esc_html_e('Vehicle Class', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="class" id="class" class="regular-text" 
                               value="<?php echo $vehicle ? esc_attr($vehicle->class) : ''; ?>" required>
                        <p class="description"><?php esc_html_e('Unique identifier (e.g., sedan, suv)', 'complete-chauffeur'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="description"><?php esc_html_e('Description', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <textarea name="description" id="description" rows="5" class="large-text"><?php 
                            echo $vehicle ? esc_textarea($vehicle->description) : ''; 
                        ?></textarea>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="capacity"><?php esc_html_e('Passenger Capacity', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="capacity" id="capacity" min="1" 
                               value="<?php echo $vehicle ? esc_attr($vehicle->capacity) : ''; ?>" required>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="price_rate"><?php esc_html_e('Price Rate (%)', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="price_rate" id="price_rate" step="0.01" min="0" 
                               value="<?php echo $vehicle ? esc_attr($vehicle->price_rate) : ''; ?>" required>
                        <p class="description"><?php esc_html_e('Percentage applied to base route price', 'complete-chauffeur'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="image_url"><?php esc_html_e('Vehicle Image', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <?php if ($vehicle && $vehicle->image_url): ?>
                            <img src="<?php echo esc_url($vehicle->image_url); ?>" style="max-height: 100px; display: block; margin-bottom: 10px;">
                        <?php endif; ?>
                        <input type="text" name="image_url" id="image_url" class="regular-text" 
                               value="<?php echo $vehicle ? esc_attr($vehicle->image_url) : ''; ?>">
                        <button type="button" class="button chauffeur-media-upload" data-target="image_url">
                            <?php esc_html_e('Upload Image', 'complete-chauffeur'); ?>
                        </button>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="is_available"><?php esc_html_e('Available for Bookings', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="is_available" id="is_available" value="1" 
                            <?php checked($vehicle ? $vehicle->is_available : 1, 1); ?>>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <?php submit_button(__('Save Vehicle', 'complete-chauffeur')); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Media uploader
    $('.chauffeur-media-upload').click(function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        var frame = wp.media({
            title: '<?php esc_html_e('Select Vehicle Image', 'complete-chauffeur'); ?>',
            multiple: false
        });
        
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#' + target).val(attachment.url);
        });
        
        frame.open();
    });
});
</script>




