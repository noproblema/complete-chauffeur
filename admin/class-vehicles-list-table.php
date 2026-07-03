<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Custom WP_List_Table implementation for Vehicles
 */
class Chauffeur_Vehicles_List_Table extends WP_List_Table {
    private $repository;

    public function __construct(Vehicle_Repository_Interface $repository) {
        parent::__construct([
            'singular' => 'vehicle',
            'plural'   => 'vehicles',
            'ajax'     => false
        ]);
        
        $this->repository = $repository;
    }

    public function prepare_items() {
        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns()
        ];
        
        $this->process_bulk_action();
        
        $args = [
            'limit'   => $per_page,
            'offset'  => $offset,
            'order'   => $_REQUEST['order'] ?? 'ASC'
        ];
        
        $this->items = $this->repository->get_all($args);
    }

    public function render(): void {
        $this->prepare_items();
        include CHAUFFEUR_BOOKING_PATH . 'admin/views/vehicles/list.php';
    }

    public function get_columns() {
        return [
            'cb'        => '<input type="checkbox" />',
            'class'     => __('Class', 'chauffeur-booking'),
            'capacity'  => __('Capacity', 'chauffeur-booking'),
            'price_rate' => __('Price Rate', 'chauffeur-booking'),
            'is_available' => __('Available', 'chauffeur-booking')
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
                'chauffeur-vehicles',
                'edit',
                $item->id,
                __('Edit', 'chauffeur-booking')
            ),
            'delete' => sprintf(
                '<a href="%s" onclick="return confirm(\'%s\')">%s</a>',
                wp_nonce_url(
                    add_query_arg([
                        'page'   => 'chauffeur-vehicles',
                        'action' => 'delete',
                        'id'     => $item->id
                    ], admin_url('admin.php')),
                    'delete_vehicle_' . $item->id
                ),
                __('Are you sure?', 'chauffeur-booking'),
                __('Delete', 'chauffeur-booking')
            ),
            'toggle' => sprintf(
                '<a href="%s">%s</a>',
                wp_nonce_url(
                    add_query_arg([
                        'page'   => 'chauffeur-vehicles',
                        'action' => 'toggle',
                        'id'     => $item->id,
                        'available' => $item->is_available ? 0 : 1
                    ], admin_url('admin.php')),
                    'toggle_availability_' . $item->id
                ),
                $item->is_available 
                    ? __('Mark Unavailable', 'chauffeur-booking')
                    : __('Mark Available', 'chauffeur-booking')
            )
        ];
        
        return sprintf(
            '<strong><a href="?page=%s&action=%s&id=%s">%s</a></strong>%s',
            'chauffeur-vehicles',
            'edit',
            $item->id,
            $item->class,
            $this->row_actions($actions)
        );
    }

    public function get_bulk_actions() {
        return [
            'delete' => __('Delete', 'chauffeur-booking'),
            'export' => __('Export', 'chauffeur-booking'),
            'make_available' => __('Mark Available', 'chauffeur-booking'),
            'make_unavailable' => __('Mark Unavailable', 'chauffeur-booking')
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
                do_action('chauffeur_export_vehicles', $ids);
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


