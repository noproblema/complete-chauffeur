<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles database schema creation and version management
 *
 * Responsible for:
 * - Creating database tables on plugin activation
 * - Managing database versioning
 * - Coordinating table creation across repositories
 */
class Chauffeur_Db_Schema {
    /**
     * @var Database_Interface $bookings_db Bookings database handler
     */
    private $bookings_db;

    /**
     * @var Database_Interface $routes_db Routes database handler
     */
    private $routes_db;

    /**
     * @var Database_Interface $vehicles_db Vehicles database handler
     */
    private $vehicles_db;

    /**
     * @var float $current_version Current database schema version
     */
    private $current_version = 1.0;

    /**
     * Constructor
     *
     * @param Database_Interface $bookings_db Bookings database handler
     * @param Database_Interface $routes_db Routes database handler
     * @param Database_Interface $vehicles_db Vehicles database handler
     */
    public function __construct(
        Database_Interface $bookings_db,
        Database_Interface $routes_db,
        Database_Interface $vehicles_db
    ) {
        $this->bookings_db = $bookings_db;
        $this->routes_db = $routes_db;
        $this->vehicles_db = $vehicles_db;
    }

    /**
     * Create all database tables on plugin activation
     */
    public function activate(): void {
        $this->bookings_db->create_table();
        $this->routes_db->create_table();
        $this->vehicles_db->create_table();
        add_option('chauffeur_booking_db_version', $this->current_version);
    }

    /**
     * Plugin deactivation handler (tables are preserved)
     */
    public function deactivate(): void {
        // Typically leave tables intact on deactivation
    }

    /**
     * Check and update database schema if needed
     */
    public function update_db_if_needed(): void {
        $installed_version = get_option('chauffeur_booking_db_version', 0);

        if (version_compare($installed_version, $this->current_version, '<')) {
            $this->activate();
        }
    }
}


