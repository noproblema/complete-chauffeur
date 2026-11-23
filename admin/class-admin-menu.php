<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class Complete_Chauffeur_Admin_Menu {
    private $bookings_admin;
    private $routes_admin;
    private $vehicles_admin;

    public function __construct(
        Bookings_Admin_Interface $bookings_admin,
        Routes_Admin_Interface $routes_admin,
        Vehicles_Admin_Interface $vehicles_admin
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
            __('Bookings Calendar', 'complete-chauffeur'),
            __('Complete Chauffeur Bookings', 'complete-chauffeur'),
            'manage_complete_chauffeur_bookings',
            'complete-chauffeurs',
            [$this, 'render_calendar_page'],
            'dashicons-calendar',
            6
        );

        // Submenu Items
        add_submenu_page(
            'complete-chauffeurs',
            __('Bookings Table', 'complete-chauffeur'),
            __('All Bookings', 'complete-chauffeur'),
            'manage_complete_chauffeur_bookings',
            'complete-chauffeurs',
            [$this->bookings_admin, 'render_page']
        );

        add_submenu_page(
            'complete-chauffeurs',
            __('Routes Editor', 'complete-chauffeur'),
            __('Routes', 'complete-chauffeur'),
            'manage_complete_chauffeur_routes',
            'complete-chauffeur-routes',
            [$this->routes_admin, 'render_page']
        );

        add_submenu_page(
            'complete-chauffeurs',
            __('Manage Vehicles', 'complete-chauffeur'),
            __('Vehicles', 'complete-chauffeur'),
            'manage_complete_chauffeur_vehicles',
            'complete-chauffeur-vehicles',
            [$this->vehicles_admin, 'render_page']
        );
    }

    public function render_calendar_page() {
        // Delegate to bookings admin for calendar rendering
        $this->bookings_admin->render_calendar();
    }

    public function handle_global_actions() {
        // Only handle bulk actions on this plugin's admin pages
        if (empty($_REQUEST['page']) || strpos($_REQUEST['page'], 'complete-chauffeur') !== 0) {
            return;
        }

        if (isset($_REQUEST['action']) && current_user_can('manage_complete_chauffeur_bookings')) {
            $this->handle_bulk_actions();
        }
    }

    private function handle_bulk_actions() {
        $action = $_REQUEST['action'];
        $ids = $_REQUEST['ids'] ?? [];

        if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-complete-chauffeurs')) {
            wp_die(__('Security check failed', 'complete-chauffeur'));
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




