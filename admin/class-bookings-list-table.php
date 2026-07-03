<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Chauffeur_Bookings_List_Table extends WP_List_Table {
    private $repository;
    private $notices = [];

    public function __construct(Booking_Repository_Interface $repository) {
        parent::__construct([
            'singular' => 'booking',
            'plural'   => 'bookings',
            'ajax'     => false
        ]);
        
        $this->repository = $repository;
    }

    /**
     * Prepare items with pagination and sorting
     */
    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = [$columns, $hidden, $sortable];
        
        // Process bulk actions
        $this->process_bulk_action();
        
        // Sorting parameters
        $orderby = $_REQUEST['orderby'] ?? 'pickup_date';
        $order = $_REQUEST['order'] ?? 'desc';
        
        // Get filtered data
        $args = [
            'limit'      => $per_page,
            'offset'     => $offset,
            'orderby'    => sanitize_text_field($orderby),
            'order'      => sanitize_text_field($order),
            'status'     => $_REQUEST['status'] ?? '',
            'start_date' => $_REQUEST['start_date'] ?? '',
            'end_date'   => $_REQUEST['end_date'] ?? ''
        ];
        
        $this->items = $this->repository->get_all($args);
    }

    public function render(): void {
        $this->prepare_items();
        include CHAUFFEUR_BOOKING_PATH . 'admin/views/bookings/list.php';
    }

    /**
     * Define columns
     */
    public function get_columns() {
        return [
            'cb'            => '<input type="checkbox" />',
            'id'            => __('ID', 'chauffeur-booking'),
            'name'          => __('Customer', 'chauffeur-booking'),
            'vehicle'       => __('Vehicle', 'chauffeur-booking'),
            'pickup_date'   => __('Pickup Date', 'chauffeur-booking'),
            'pickup_location' => __('Pickup Location', 'chauffeur-booking'),
            'destination'   => __('Destination', 'chauffeur-booking'),
            'price'         => __('Price', 'chauffeur-booking'),
            'status'        => __('Status', 'chauffeur-booking')
        ];
    }

    /**
     * Define sortable columns
     */
    public function get_sortable_columns() {
        return [
            'id'            => ['id', true],
            'name'          => ['name', true],
            'pickup_date'   => ['pickup_date', true],
            'price'         => ['price', true],
            'status'        => ['status', true]
        ];
    }

    /**
     * Render checkbox column
     */
    protected function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="ids[]" value="%s" />',
            $item->id
        );
    }

    /**
     * Default column rendering
     */
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'pickup_date':
                return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item->pickup_date));
            case 'price':
                return number_format($item->price, 2);
            case 'status':
                return $this->get_status_badge($item->status);
            default:
                return $item->$column_name ?? '';
        }
    }

    /**
     * Render customer name column with actions
     */
    public function column_name($item) {
        $actions = [
            'edit' => sprintf(
                '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>',
                'chauffeur-bookings',
                'edit',
                $item->id,
                wp_create_nonce('edit_booking_' . $item->id),
                __('Edit', 'chauffeur-booking')
            ),
            'delete' => sprintf(
                '<a href="%s" onclick="return confirm(\'%s\')">%s</a>',
                wp_nonce_url(
                    admin_url('admin.php?page=chauffeur-bookings&action=delete&id=' . $item->id),
                    'delete_booking_' . $item->id
                ),
                __('Are you sure you want to delete this booking?', 'chauffeur-booking'),
                __('Delete', 'chauffeur-booking')
            ),
            'view' => sprintf(
                '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>',
                'chauffeur-bookings',
                'view',
                $item->id,
                wp_create_nonce('view_booking_' . $item->id),
                __('View', 'chauffeur-booking')
            )
        ];
        
        return sprintf(
            '<strong><a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a></strong>%s',
            'chauffeur-bookings',
            'edit',
            $item->id,
            wp_create_nonce('edit_booking_' . $item->id),
            esc_html($item->name),
            $this->row_actions($actions)
        );
    }

    /**
     * Define bulk actions
     */
    public function get_bulk_actions() {
        return [
            'delete'        => __('Delete', 'chauffeur-booking'),
            'export'        => __('Export', 'chauffeur-booking'),
            'mark_confirmed' => __('Mark as Confirmed', 'chauffeur-booking'),
            'mark_completed' => __('Mark as Completed', 'chauffeur-booking'),
            'mark_cancelled' => __('Mark as Cancelled', 'chauffeur-booking')
        ];
    }

    /**
     * Process bulk actions with nonce verification
     */
    public function process_bulk_action() {
        if (!$this->current_action()) {
            return;
        }

        // Verify nonce
        check_admin_referer('bulk-bookings', '_wpnonce');

        $ids = isset($_REQUEST['ids']) ? (array)$_REQUEST['ids'] : [];
        if (empty($ids)) {
            $this->add_notice(__('No bookings selected.', 'chauffeur-booking'), 'error');
            return;
        }

        try {
            switch ($this->current_action()) {
                case 'delete':
                    $deleted = 0;
                    foreach ($ids as $id) {
                        if ($this->repository->delete(absint($id))) {
                            $deleted++;
                        }
                    }
                    $this->add_notice(
                        sprintf(_n('%d booking deleted.', '%d bookings deleted.', $deleted, 'chauffeur-booking'), $deleted)
                    );
                    break;

                case 'export':
                    do_action('chauffeur_export_bookings', $ids);
                    break;

                case 'mark_confirmed':
                case 'mark_completed':
                case 'mark_cancelled':
                    $status = str_replace('mark_', '', $this->current_action());
                    $updated = 0;
                    foreach ($ids as $id) {
                        if ($this->repository->update_status(absint($id), $status)) {
                            $updated++;
                        }
                    }
                    $this->add_notice(
                        sprintf(__('%d bookings marked as %s.', 'chauffeur-booking'), $updated, $status)
                    );
                    break;
            }
        } catch (Exception $e) {
            $this->add_notice($e->getMessage(), 'error');
        }
    }

    /**
     * Add status filter dropdown
     */
    protected function extra_tablenav($which) {
        if ($which === 'top') {
            $statuses = [
                ''           => __('All Statuses', 'chauffeur-booking'),
                'pending'    => __('Pending', 'chauffeur-booking'),
                'confirmed'  => __('Confirmed', 'chauffeur-booking'),
                'completed'  => __('Completed', 'chauffeur-booking'),
                'cancelled'  => __('Cancelled', 'chauffeur-booking')
            ];
            
            $current_status = $_REQUEST['status'] ?? '';
            ?>
            <div class="alignleft actions">
                <select name="status">
                    <?php foreach ($statuses as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current_status, $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" name="start_date" class="datepicker" 
                       placeholder="<?php esc_attr_e('Start Date', 'chauffeur-booking'); ?>"
                       value="<?php echo esc_attr($_REQUEST['start_date'] ?? ''); ?>">
                
                <input type="text" name="end_date" class="datepicker" 
                       placeholder="<?php esc_attr_e('End Date', 'chauffeur-booking'); ?>"
                       value="<?php echo esc_attr($_REQUEST['end_date'] ?? ''); ?>">
                
                <?php submit_button(__('Filter', 'chauffeur-booking'), 'button', 'filter_action', false); ?>
            </div>
            <?php
        }
    }

    /**
     * Display admin notices
     */
    public function display_notices() {
        foreach ($this->notices as $notice) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr($notice['type']),
                esc_html($notice['message'])
            );
        }
    }

    /**
     * Add admin notice
     */
    private function add_notice($message, $type = 'success') {
        $this->notices[] = [
            'message' => $message,
            'type'    => $type
        ];
    }

    /**
     * Get styled status badge
     */
    private function get_status_badge($status) {
        $status_labels = [
            'pending'    => __('Pending', 'chauffeur-booking'),
            'confirmed'  => __('Confirmed', 'chauffeur-booking'),
            'completed'  => __('Completed', 'chauffeur-booking'),
            'cancelled'  => __('Cancelled', 'chauffeur-booking')
        ];
        
        $status_colors = [
            'pending'    => '#ff9800',
            'confirmed'  => '#4caf50',
            'completed'  => '#2196f3',
            'cancelled'  => '#f44336'
        ];
        
        return sprintf(
            '<span class="status-badge" style="background-color:%s;color:#fff;padding:2px 8px;border-radius:3px;font-size:12px;">%s</span>',
            $status_colors[$status] ?? '#777',
            $status_labels[$status] ?? ucfirst($status)
        );
    }
}


