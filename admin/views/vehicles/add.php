<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<div class="wrap">
    <h1><?php esc_html_e('Add New Vehicle', 'complete-chauffeur'); ?></h1>
    
    <?php $this->display_notices(); ?>
    
    <form method="post" class="chauffeur-admin-form" enctype="multipart/form-data">
        <?php wp_nonce_field('vehicle_action', 'complete_chauffeur_vehicle_nonce'); ?>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="class"><?php esc_html_e('Vehicle Class', 'complete-chauffeur'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="class" id="class" class="regular-text" required>
                        <p class="description"><?php esc_html_e('Unique identifier (e.g., sedan, suv)', 'complete-chauffeur'); ?></p>
                    </td>
                </tr>
                
                <!-- Other fields same as edit.php but without values -->
                
            </tbody>
        </table>
        
        <?php submit_button(__('Add Vehicle', 'complete-chauffeur')); ?>
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




