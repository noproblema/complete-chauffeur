<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Custom WP_List_Table implementation for Vehicles
 */
class Complete_Chauffeur_Vehicles_List_Table extends WP_List_Table {
    private $repository;

    public function __construct(Vehicle_Repository_Interface $repository) {
        parent::__construct([
            'singular' => 'vehicle',
            'plural'   => 'vehicles',
            'ajax'     => false
        ]);
        
        $this->repository = $repository;
    }

    /**
     * Prepare items with pagination and sorting
     */
    public function prepare_items() {
        // Define column headers
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = [$columns, $hidden, $sortable];
        
        // Process bulk actions
        $this->process_bulk_action();
        
        $per_page = 10; // Or get from user meta/options
        $paged    = $this->get_pagenum();
        $offset   = ( $paged - 1 ) * $per_page;
        
        $args = [
            'limit'   => $per_page,
            'offset'  => $offset,
            'order'   => $_REQUEST['order'] ?? 'ASC'
        ];
        
        $this->items = $this->repository->get_all($args);
    }

    public function render(): void {
        $this->prepare_items();
        include COMPLETE_CHAUFFEUR_PATH . 'admin/views/vehicles/list.php';
    }

    /**
     * Define columns
     */
    public function get_columns() {
        return [
            'cb'        => '<input type="checkbox" />',
            'class'     => __('Class', 'complete-chauffeur'),
            'capacity'  => __('Capacity', 'complete-chauffeur'),
            'price_rate' => __('Price Rate', 'complete-chauffeur'),
            'is_available' => __('Available', 'complete-chauffeur')
        ];
    }

    public function get_sortable_columns() {
        return [
            'class'     => ['class', true],
            'capacity'  => ['capacity', true],
            'price_rate' => ['price_rate', true],
            'is_available' => ['is_available', false]
        ];
    }

    protected function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="ids[]" value="%s" />',
            $item->id
        );
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'is_available':
                return $item->is_available 
                    ? '<span class="dashicons dashicons-yes" style="color: #46b450;"></span>'
                    : '<span class="dashicons dashicons-no" style="color: #dc3232;"></span>';
            case 'price_rate':
                return number_format($item->price_rate, 2) . '%';
            default:
                return $item->$column_name ?? '';
        }
    }

    public function column_class($item) {
        $actions = [
            'edit'   => sprintf(
                '<a href="?page=%s&action=%s&id=%s">%s</a>',
                'complete-chauffeur-vehicles',
                'edit',
                $item->id,
                __('Edit', 'complete-chauffeur')
            ),
            'delete' => sprintf(
                '<a href="%s" onclick="return confirm(\'%s\')">%s</a>',
                wp_nonce_url(
                    add_query_arg([
                        'page'   => 'complete-chauffeur-vehicles',
                        'action' => 'delete',
                        'id'     => $item->id
                    ], admin_url('admin.php')),
                    'delete_vehicle_' . $item->id
                ),
                __('Are you sure?', 'complete-chauffeur'),
                __('Delete', 'complete-chauffeur')
            ),
            'toggle' => sprintf(
                '<a href="%s">%s</a>',
                wp_nonce_url(
                    add_query_arg([
                        'page'   => 'complete-chauffeur-vehicles',
                        'action' => 'toggle',
                        'id'     => $item->id,
                        'available' => $item->is_available ? 0 : 1
                    ], admin_url('admin.php')),
                    'toggle_availability_' . $item->id
                ),
                $item->is_available 
                    ? __('Mark Unavailable', 'complete-chauffeur')
                    : __('Mark Available', 'complete-chauffeur')
            )
        ];
        
        return sprintf(
            '<strong><a href="?page=%s&action=%s&id=%s">%s</a></strong>%s',
            'complete-chauffeur-vehicles',
            'edit',
            $item->id,
            $item->class,
            $this->row_actions($actions)
        );
    }

    public function get_bulk_actions() {
        return [
            'delete' => __('Delete', 'complete-chauffeur'),
            'export' => __('Export', 'complete-chauffeur'),
            'make_available' => __('Mark Available', 'complete-chauffeur'),
            'make_unavailable' => __('Mark Unavailable', 'complete-chauffeur')
        ];
    }

    public function process_bulk_action() {
        if (!$this->current_action()) return;
        
        $ids = $_REQUEST['ids'] ?? [];
        if (empty($ids)) return;
        
        check_admin_referer('bulk-' . $this->_args['plural']);
        
        switch ($this->current_action()) {
            case 'delete':
                foreach ($ids as $id) {
                    $this->repository->delete(absint($id));
                }
                break;
            case 'export':
                do_action('complete_chauffeur_export_vehicles', $ids);
                break;
            case 'make_available':
                foreach ($ids as $id) {
                    $this->repository->update(absint($id), ['is_available' => 1]);
                }
                break;
            case 'make_unavailable':
                foreach ($ids as $id) {
                    $this->repository->update(absint($id), ['is_available' => 0]);
                }
                break;
        }
    }
}




