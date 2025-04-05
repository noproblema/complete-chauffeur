<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class Chauffeur_Admin_Menu {
    private $bookings_admin;
    private $routes_admin;
    private $vehicles_admin;

    public function __construct(
        BookingsAdminInterface $bookings_admin,
        RoutesAdminInterface $routes_admin,
        VehiclesAdminInterface $vehicles_admin
    ) {
        $this->bookings_admin = $bookings_admin;
        $this->routes_admin = $routes_admin;
        $this->vehicles_admin = $vehicles_admin;
        
        add_action('admin_menu', [$this, 'add_admin_menus']);
        add_action('admin_init', [$this, 'handle_global_actions']);
    }

    public function add_admin_menus() {
        // Main Bookings Menu
        add_menu_page(
            __('Bookings Calendar', 'chauffeur-booking'),
            __('Chauffeur Bookings', 'chauffeur-booking'),
            'manage_chauffeur_bookings',
            'chauffeur-bookings',
            [$this, 'render_calendar_page'],
            'dashicons-calendar',
            6
        );

        // Submenu Items
        add_submenu_page(
            'chauffeur-bookings',
            __('Bookings Table', 'chauffeur-booking'),
            __('All Bookings', 'chauffeur-booking'),
            'manage_chauffeur_bookings',
            'chauffeur-bookings',
            [$this->bookings_admin, 'render_page']
        );

        add_submenu_page(
            'chauffeur-bookings',
            __('Routes Editor', 'chauffeur-booking'),
            __('Routes', 'chauffeur-booking'),
            'manage_chauffeur_routes',
            'chauffeur-routes',
            [$this->routes_admin, 'render_page']
        );

        add_submenu_page(
            'chauffeur-bookings',
            __('Manage Vehicles', 'chauffeur-booking'),
            __('Vehicles', 'chauffeur-booking'),
            'manage_chauffeur_vehicles',
            'chauffeur-vehicles',
            [$this->vehicles_admin, 'render_page']
        );
    }

    public function render_calendar_page() {
        // Delegate to bookings admin for calendar rendering
        $this->bookings_admin->render_calendar();
    }

    public function handle_global_actions() {
        // Handle bulk actions across admin pages
        if (isset($_REQUEST['action']) && current_user_can('manage_chauffeur_bookings')) {
            $this->handle_bulk_actions();
        }
    }

    private function handle_bulk_actions() {
        $action = $_REQUEST['action'];
        $ids = $_REQUEST['ids'] ?? [];

        if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-bookings')) {
            wp_die(__('Security check failed', 'chauffeur-booking'));
        }

        try {
            switch ($action) {
                case 'delete':
                    foreach ($ids as $id) {
                        $this->bookings_admin->delete(absint($id));
                    }
                    break;
                case 'export':
                    $this->bookings_admin->export_bookings($ids);
                    break;
            }
        } catch (Exception $e) {
            wp_die($e->getMessage());
        }
    }
}
