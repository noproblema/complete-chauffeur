<?php
/**
 * Plugin Name: Chauffeur Booking
 * Description: A plugin to manage chauffeur bookings on your WordPress site.
 * Version: 1.0
 * Author: Your Name
 * Text Domain: chauffeur-booking
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

// Define plugin constants
define('CHAUFFEUR_BOOKING_PATH', plugin_dir_path(__FILE__));
define('CHAUFFEUR_BOOKING_URL', plugin_dir_url(__FILE__));
define('CHAUFFEUR_BOOKING_BASENAME', plugin_basename(__FILE__));
define('CHAUFFEUR_BOOKING_VERSION', '1.0.0');

// Load autoloader with debug version for development
require_once CHAUFFEUR_BOOKING_PATH . 'includes/class-autoloader-debug.php';
Chauffeur_Autoloader_Debug::clear_cache();
Chauffeur_Autoloader_Debug::register();

// Initialize the Container
require CHAUFFEUR_BOOKING_PATH . 'includes/class-container.php';
$container = new Chauffeur_Container();

// Configure the container
$container_config = require CHAUFFEUR_BOOKING_PATH . 'includes/container-config.php';
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
            $db_schema = $container->get('Chauffeur_Db_Schema');
            $db_schema->activate();
            
            // Add default options if needed
            add_option('chauffeur_booking_flush_rewrites', true);
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
            $db_schema = $container->get('Chauffeur_Db_Schema');
            $db_schema->deactivate();
            
            // Cleanup transients and scheduled events
            wp_clear_scheduled_hook('chauffeur_booking_daily_maintenance');
        } catch (Exception $e) {
            error_log('Chauffeur Booking deactivation error: ' . $e->getMessage());
        }
    }
);

/**
 * Uninstall Hook (separate file)
 */
register_uninstall_hook(
    __FILE__,
    'chauffeur_booking_uninstall'
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
function chauffeur_booking_uninstall() {
    // Security check
    if (!defined('WP_UNINSTALL_PLUGIN')) {
        exit;
    }

    // Load minimal dependencies
    require_once CHAUFFEUR_BOOKING_PATH . 'includes/database/class-bookings-database.php';
    require_once CHAUFFEUR_BOOKING_PATH . 'includes/database/class-routes-database.php';
    require_once CHAUFFEUR_BOOKING_PATH . 'includes/database/class-vehicles-database.php';

    global $wpdb;

    try {
        // Delete database tables
        $tables = [
            new Chauffeur_Bookings_Database(),
            new Chauffeur_Routes_Database(),
            new Chauffeur_Vehicles_Database()
        ];

        foreach ($tables as $table) {
            $table->drop_table();
        }

        // Delete plugin options
        delete_option('chauffeur_booking_db_version');
        delete_option('chauffeur_booking_flush_rewrites');
        
        // Cleanup transients
        $wpdb->query("
            DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '%chauffeur_booking%'
        ");

    } catch (Exception $e) {
        error_log('Chauffeur Booking uninstall error: ' . $e->getMessage());
    }
}
    
// Main plugin class that handles plugin initialization
final class Chauffeur_Booking_Plugin {
    private $container;
    
    public function __construct(Chauffeur_Container $container) {
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
            'chauffeur-booking',
            false,
            dirname(CHAUFFEUR_BOOKING_BASENAME) . '/languages/'
        );
    }
    
    public function init_components() {
        // Initialize frontend components
        $this->container->get('Chauffeur_Public_Shortcode');
        $this->container->get('Form_Handler_Interface');
        
        // Initialize admin components only in admin area
        if (is_admin()) {
            $this->container->get('Chauffeur_Admin_Menu');
            $this->container->get('Chauffeur_Admin_Assets');
        }
    }
}

// ========================
// PLUGIN INITIALIZATION
// ========================

// Initialize the plugin
add_action('plugins_loaded', function() use ($container) {
    // Register the autoloader (regular version for production)
    // Chauffeur_Autoloader::register();
    
    // Initialize plugin components
    new Chauffeur_Booking_Plugin($container);
}, 5);


