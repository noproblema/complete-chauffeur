<?php
/**
 * Plugin Name: Complete Chauffeur
 * Description: A comprehensive plugin to manage complete chauffeur bookings on your WordPress site.
 * Version: 2.0.0
 * Author: Rede Baia Lda
 * Text Domain: complete-chauffeur
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

// Define plugin constants
define('COMPLETE_CHAUFFEUR_PATH', plugin_dir_path(__FILE__));
define('COMPLETE_CHAUFFEUR_URL', plugin_dir_url(__FILE__));
define('COMPLETE_CHAUFFEUR_BASENAME', plugin_basename(__FILE__));
define('COMPLETE_CHAUFFEUR_VERSION', '2.0.0');

// Load autoloader
require_once COMPLETE_CHAUFFEUR_PATH . 'includes/class-autoloader.php';
Complete_Chauffeur_Autoloader::register();

// Initialize the Container
require COMPLETE_CHAUFFEUR_PATH . 'includes/class-container.php';
$container = new Complete_Chauffeur_Container();

// Configure the container
$container_config = require COMPLETE_CHAUFFEUR_PATH . 'includes/container-config.php';
$container_config($container);

// ========================
// PLUGIN LIFECYCLE HOOKS
// ========================

/**
 * Activation Hook
 */
register_activation_hook(
    __FILE__,
    function() use ($container) {
        try {
            $db_schema = $container->get('Complete_Chauffeur_Db_Schema');
            $db_schema->activate();

            // Grant administrator capabilities
            $admin_role = get_role('administrator');
            if ($admin_role) {
                $admin_role->add_cap('manage_complete_chauffeur_bookings');
                $admin_role->add_cap('manage_complete_chauffeur_routes');
                $admin_role->add_cap('manage_complete_chauffeur_vehicles');
            }
        } catch (Exception $e) {
            wp_die('Plugin activation failed: ' . $e->getMessage());
        }
    }
);

/**
 * Deactivation Hook
 */
register_deactivation_hook(
    __FILE__,
    function() use ($container) {
        try {
            $db_schema = $container->get('Complete_Chauffeur_Db_Schema');
            $db_schema->deactivate();

            // Remove administrator capabilities
            $admin_role = get_role('administrator');
            if ($admin_role) {
                $admin_role->remove_cap('manage_complete_chauffeur_bookings');
                $admin_role->remove_cap('manage_complete_chauffeur_routes');
                $admin_role->remove_cap('manage_complete_chauffeur_vehicles');
            }
        } catch (Exception $e) {
            error_log('Complete Chauffeur deactivation error: ' . $e->getMessage());
        }
    }
);

/**
 * Uninstall Hook (separate file)
 */
register_uninstall_hook(
    __FILE__,
    'complete_chauffeur_booking_uninstall'
);

// ========================
// UNINSTALL FUNCTIONALITY
// ========================

/**
 * Handle plugin uninstallation
 * 
 * @note This must be a standalone function (not a class method)
 *       because it runs when the plugin is no longer loaded
 */
function complete_chauffeur_booking_uninstall() {
    // Security check
    if (!defined('WP_UNINSTALL_PLUGIN')) {
        exit;
    }

    // Load minimal dependencies
    require_once COMPLETE_CHAUFFEUR_PATH . 'includes/database/class-bookings-database.php';
    require_once COMPLETE_CHAUFFEUR_PATH . 'includes/database/class-routes-database.php';
    require_once COMPLETE_CHAUFFEUR_PATH . 'includes/database/class-vehicles-database.php';

    global $wpdb;

    try {
        // Delete database tables
        $tables = [
            new Complete_Chauffeur_Bookings_Database(),
            new Complete_Chauffeur_Routes_Database(),
            new Complete_Chauffeur_Vehicles_Database()
        ];

        foreach ($tables as $table) {
            $table->drop_table();
        }

        // Delete plugin options
        delete_option('complete_chauffeur_booking_db_version');
        delete_option('complete_chauffeur_booking_flush_rewrites');
        
        // Cleanup transients (including classmap caches)
        $wpdb->query("
            DELETE FROM {$wpdb->options}
            WHERE option_name LIKE '%complete_chauffeur_booking%'
               OR option_name LIKE '%complete_chauffeur_classmap%'
               OR option_name LIKE '%complete_chauffeur_autoload_classmap%'
        ");

    } catch (Exception $e) {
        error_log('Complete Chauffeur uninstall error: ' . $e->getMessage());
    }
}
    
// Main plugin class that handles plugin initialization
final class Complete_Chauffeur_Booking_Plugin {
    private $container;
    
    public function __construct(Complete_Chauffeur_Container $container) {
        $this->container = $container;
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Load text domain for translations
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        
        // Initialize components on init hook
        add_action('init', [$this, 'init_components']);
    }
    
    public function load_textdomain() {
        load_plugin_textdomain(
            'complete-chauffeur',
            false,
            dirname(COMPLETE_CHAUFFEUR_BASENAME) . '/languages/'
        );
    }
    
    public function init_components() {
        // Initialize frontend components
        $this->container->get('Complete_Chauffeur_Public_Shortcode');
        $this->container->get('Form_Handler_Interface');
        
        // Initialize admin components only in admin area
        if (is_admin()) {
            $this->container->get('Complete_Chauffeur_Admin_Menu');
            $this->container->get('Complete_Chauffeur_Admin_Assets');
        }
    }
}

// ========================
// PLUGIN INITIALIZATION
// ========================

// Initialize the plugin
add_action('plugins_loaded', function() use ($container) {
    // Register the autoloader (regular version for production)
    // Complete_Chauffeur_Autoloader::register();
    
    // Initialize plugin components
    new Complete_Chauffeur_Booking_Plugin($container);
}, 5);




