<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php esc_html_e('Vehicles', 'chauffeur-booking'); ?>
    </h1>
    
    <a href="<?php echo esc_url(admin_url('admin.php?page=chauffeur-vehicles&action=add')); ?>" 
       class="page-title-action">
        <?php esc_html_e('Add New', 'chauffeur-booking'); ?>
    </a>
    
    <?php $this->display_notices(); ?>
    
    <form method="post">
        <?php 
        wp_nonce_field('bulk-' . $this->_args['plural'], '_wpnonce', false); 
        $this->search_box(__('Search Vehicles', 'chauffeur-booking'), 'vehicle_search');
        $this->display(); 
        ?>
    </form>
</div>


