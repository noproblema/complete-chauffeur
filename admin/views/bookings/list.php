<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Bookings', 'complete-chauffeur'); ?></h1>
    
    <a href="<?php echo esc_url(admin_url('admin.php?page=complete-chauffeurs&action=add')); ?>" 
       class="page-title-action">
        <?php esc_html_e('Add New', 'complete-chauffeur'); ?>
    </a>
    
    <?php 
    // Display admin notices
    $this->display_notices();
    ?>
    
    <form method="post" id="bookings-filter">
        <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page'] ?? ''); ?>">
        
        <?php 
        // Add nonce field for bulk actions
        wp_nonce_field('bulk-' . $this->_args['plural'], '_wpnonce', false); 
        
        // Add search box if needed
        $this->search_box(__('Search Bookings', 'complete-chauffeur'), 'booking_search');
        
        // Display the table
        $this->display(); 
        ?>
    </form>
    
    <?php
    // Optional: Add view switcher (calendar/list) if needed
    if (isset($_GET['view'])) {
        $current_view = sanitize_text_field($_GET['view']);
        ?>
        <div class="view-switch">
            <a href="<?php echo esc_url(remove_query_arg('view')); ?>" 
               class="button <?php echo ($current_view !== 'calendar') ? 'button-primary' : ''; ?>">
                <?php esc_html_e('List View', 'complete-chauffeur'); ?>
            </a>
            <a href="<?php echo esc_url(add_query_arg('view', 'calendar')); ?>" 
               class="button <?php echo ($current_view === 'calendar') ? 'button-primary' : ''; ?>">
                <?php esc_html_e('Calendar View', 'complete-chauffeur'); ?>
            </a>
        </div>
        <?php
    }
    ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Confirm before bulk actions
    $('#doaction, #doaction2').click(function(e) {
        if ($('select[name^="action"]').val() === 'delete') {
            if (!confirm('<?php esc_attr_e('Are you sure you want to delete these bookings?', 'complete-chauffeur'); ?>')) {
                e.preventDefault();
            }
        }
    });
    
    // Toggle all checkboxes
    $('#cb-select-all-1, #cb-select-all-2').click(function() {
        $('.column-cb input[type="checkbox"]').prop('checked', $(this).prop('checked'));
    });
});
</script>




