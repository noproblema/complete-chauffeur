<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
    <h1 class="wp-heading-inline"><?php esc_html_e('Bookings', 'chauffeur-booking'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=chauffeur-bookings&action=add')); ?>" class="page-title-action">
        <?php esc_html_e('Add New', 'chauffeur-booking'); ?>
    </a>
    
    <?php $this->display_notices(); ?>
    
    <form method="post">
        <?php $bookings_table->display(); ?>
    </form>
</div>
