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

// Load dependencies
require CHAUFFEUR_BOOKING_PATH . 'includes/class-container.php';
require CHAUFFEUR_BOOKING_PATH . 'includes/class-autoloader.php';

// Initialize the DI Container
$container = new Chauffeur_DIContainer();

// Configure the container
$container_config = require CHAUFFEUR_BOOKING_PATH . 'includes/container-config.php';
$container_config($container);

// Register activation/deactivation hooks
register_activation_hook(__FILE__, function() use ($container) {
    $db_schema = $container->get('Chauffeur_DB_Schema');
    $db_schema->activate();
    
    // Rebuild classmap cache on activation
    Chauffeur_Autoloader::scan_directory();
    Chauffeur_Autoloader::cache_classmap();
});

register_deactivation_hook(__FILE__, function() use ($container) {
    $db_schema = $container->get('Chauffeur_DB_Schema');
    $db_schema->deactivate();
});

// Main plugin class
final class Chauffeur_Booking_Plugin {
    private $container;
    
    public function __construct(Chauffeur_DIContainer $container) {
        $this->container = $container;
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Initialize the autoloader
        Chauffeur_Autoloader::register();
        
        // Load text domain
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        
        // Initialize components
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
        $this->container->get('FormHandlerInterface');
        
        // Initialize admin components
        if (is_admin()) {
            $this->container->get('Chauffeur_Admin_Menu');
            $this->container->get('Chauffeur_Admin_Assets');
        }
    }
}

// Initialize the plugin
$plugin = new Chauffeur_Booking_Plugin($container);
