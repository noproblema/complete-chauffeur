<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles database schema creation and updates
 * 
 * @package ChauffeurBooking
 */
class Chauffeur_DB_Schema {
    /** @var wpdb $wpdb WordPress database abstraction */
    private $wpdb;

    /** @var string $charset_collate Database charset */
    private $charset_collate;

    /** @var float $current_version Current database version */
    private $current_version = 1.0;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->charset_collate = $wpdb->get_charset_collate();
    }

    /**
     * Create database tables on plugin activation
     */
    public function activate(): void {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $this->create_bookings_table();
        $this->create_routes_table();
        $this->create_vehicles_table();

        add_option('chauffeur_booking_db_version', $this->current_version);
    }

    /**
     * Remove database tables on plugin deactivation (optional)
     */
    public function deactivate(): void {
        // Typically you wouldn't delete tables on deactivation
        // But here's how you could if needed:
        /*
        $this->wpdb->query("DROP TABLE IF EXISTS {$this->wpdb->prefix}chauffeur_bookings");
        $this->wpdb->query("DROP TABLE IF EXISTS {$this->wpdb->prefix}chauffeur_routes");
        $this->wpdb->query("DROP TABLE IF EXISTS {$this->wpdb->prefix}chauffeur_vehicles");
        delete_option('chauffeur_booking_db_version');
        */
    }

    /**
     * Create bookings table
     */
    private function create_bookings_table(): void {
        $table_name = $this->wpdb->prefix . 'chauffeur_bookings';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(30) NOT NULL,
            vehicle varchar(50) NOT NULL,
            route_id bigint(20) NOT NULL,
            pickup_date datetime NOT NULL,
            pickup_location varchar(255) NOT NULL,
            destination varchar(255) NOT NULL,
            distance decimal(10,2) NOT NULL,
            price decimal(10,2) NOT NULL,
            roundtrip tinyint(1) DEFAULT 0 NOT NULL,
            notes text DEFAULT NULL,
            status varchar(20) DEFAULT 'pending' NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY route_id (route_id),
            KEY pickup_date (pickup_date),
            KEY status (status)
        ) {$this->charset_collate};";

        dbDelta($sql);
    }

    /**
     * Create routes table
     */
    private function create_routes_table(): void {
        $table_name = $this->wpdb->prefix . 'chauffeur_routes';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            route_name varchar(100) NOT NULL,
            start_location varchar(255) NOT NULL,
            end_location varchar(255) NOT NULL,
            distance decimal(10,2) NOT NULL,
            price decimal(10,2) NOT NULL,
            roundtrip_price decimal(10,2) NOT NULL,
            is_active tinyint(1) DEFAULT 1 NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY is_active (is_active)
        ) {$this->charset_collate};";

        dbDelta($sql);
    }

    /**
     * Create vehicles table
     */
    private function create_vehicles_table(): void {
        $table_name = $this->wpdb->prefix . 'chauffeur_vehicles';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            class varchar(50) NOT NULL,
            name varchar(100) NOT NULL,
            description text DEFAULT NULL,
            capacity int(11) NOT NULL,
            price_rate decimal(5,2) NOT NULL COMMENT 'Percentage rate to apply to base price',
            image_url varchar(255) DEFAULT NULL,
            is_available tinyint(1) DEFAULT 1 NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY class (class),
            KEY is_available (is_available)
        ) {$this->charset_collate};";

        dbDelta($sql);
    }

    /**
     * Update database schema if needed
     */
    public function update_db_if_needed(): void {
        $installed_version = get_option('chauffeur_booking_db_version', 0);

        if (version_compare($installed_version, $this->current_version, '<')) {
            $this->activate();
        }
    }
}
