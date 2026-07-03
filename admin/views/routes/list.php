<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php esc_html_e('Routes', 'chauffeur-booking'); ?>
    </h1>
    
    <a href="<?php echo esc_url(admin_url('admin.php?page=chauffeur-routes&action=add')); ?>" 
       class="page-title-action">
        <?php esc_html_e('Add New', 'chauffeur-booking'); ?>
    </a>
    
    <?php $this->display_notices(); ?>
    
    <form method="post" id="bookings-filter">
        <?php 
        // Add nonce field for bulk actions
        wp_nonce_field('bulk-' . $this->_args['plural'], '_wpnonce', false); 
        
        // Add search box if needed
        $this->search_box(__('Search Bookings', 'chauffeur-booking'), 'booking_search');
        
        // Display the table
        $this->display(); 
        ?>
</div>


