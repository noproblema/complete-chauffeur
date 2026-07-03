<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Custom WP_List_Table implementation for Routes
 */
class Chauffeur_Routes_List_Table extends WP_List_Table {
    private $repository;

    public function __construct(Route_Repository_Interface $repository) {
        parent::__construct([
            'singular' => 'route',  // Singular label
            'plural'   => 'routes', // Plural label
            'ajax'     => false     // No AJAX support
        ]);
        
        $this->repository = $repository;
    }

    /**
     * Prepare items for display
     */
    public function prepare_items() {
        // Define column headers
        $this->_column_headers = [
            $this->get_columns(),     // Columns
            [],                       // Hidden columns
            $this->get_sortable_columns() // Sortable columns
        ];
        
        // Handle bulk actions
        $this->process_bulk_action();
        
        // Get filtered/sorted data from repository
        $args = [
            'limit'   => $per_page,
            'offset'  => $offset,
            'orderby' => $_REQUEST['orderby'] ?? 'route_name',
            'order'   => $_REQUEST['order'] ?? 'ASC'
        ];
        
        // Get the data
        $this->items = $this->repository->get_all($args);
    }

    public function render(): void {
        $this->prepare_items();
        include CHAUFFEUR_BOOKING_PATH . 'admin/views/routes/list.php';
    }
        
    /**
     * Define columns
     */
    public function get_columns() {
        return [
            'cb'           => '<input type="checkbox" />',
            'route_name'   => __('Route Name', 'chauffeur-booking'),
            'start_location' => __('Start Location', 'chauffeur-booking'),
            'end_location' => __('End Location', 'chauffeur-booking'),
            'distance'    => __('Distance (km)', 'chauffeur-booking'),
            'price'       => __('Price', 'chauffeur-booking'),
            'is_active'   => __('Active', 'chauffeur-booking')
        ];
    }

    /**
     * Define sortable columns
     */
    public function get_sortable_columns() {
        return [
            'route_name'   => ['route_name', true],
            'start_location' => ['start_location', false],
            'distance'    => ['distance', true],
            'price'       => ['price', true],
            'is_active'   => ['is_active', false]
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
     * Render default columns
     */
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'is_active':
                return $item->is_active 
                    ? '<span class="dashicons dashicons-yes" style="color: #46b450;"></span>'
                    : '<span class="dashicons dashicons-no" style="color: #dc3232;"></span>';
            case 'price':
                return number_format($item->price, 2);
            case 'distance':
                return number_format($item->distance, 2);
            default:
                return $item->$column_name ?? '';
        }
    }

    /**
     * Render route_name column with actions
     */
    public function column_route_name($item) {
        $actions = [
            'edit'   => sprintf(
                '<a href="?page=%s&action=%s&id=%s">%s</a>',
                'chauffeur-routes',
                'edit',
                $item->id,
                __('Edit', 'chauffeur-booking')
            ),
            'delete' => sprintf(
                '<a href="%s" onclick="return confirm(\'%s\')">%s</a>',
                wp_nonce_url(
                    add_query_arg([
                        'page'   => 'chauffeur-routes',
                        'action' => 'delete',
                        'id'     => $item->id
                    ], admin_url('admin.php')),
                    'delete_route_' . $item->id
                ),
                __('Are you sure?', 'chauffeur-booking'),
                __('Delete', 'chauffeur-booking')
            )
        ];
        
        return sprintf(
            '<strong><a href="?page=%s&action=%s&id=%s">%s</a></strong>%s',
            'chauffeur-routes',
            'edit',
            $item->id,
            $item->route_name,
            $this->row_actions($actions)
        );
    }

    /**
     * Define bulk actions
     */
    public function get_bulk_actions() {
        return [
            'delete' => __('Delete', 'chauffeur-booking'),
            'export' => __('Export', 'chauffeur-booking')
        ];
    }

    /**
     * Process bulk actions
     */
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
                $this->export_routes($ids);
                break;
        }
    }

    /**
     * Export routes to CSV
     */
    private function export_routes(array $ids) {
        // Implementation moved to Routes_Admin_Interface
        do_action('chauffeur_export_routes', $ids);
    }
}


