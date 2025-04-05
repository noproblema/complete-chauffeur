<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
    <h1><?php esc_html_e('Bookings Calendar', 'chauffeur-booking'); ?></h1>
    <div id="chauffeur-calendar" style="height: 800px;"></div>
</div>

<script>
jQuery(document).ready(function($) {
    const calendar = new tui.Calendar('#chauffeur-calendar', {
        defaultView: 'month',
        useCreationPopup: true,
        useDetailPopup: true
    });
    
    calendar.createEvents(<?php echo json_encode($bookings); ?>);
});
</script>
