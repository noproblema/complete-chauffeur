<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles all database operations for the routes table
 * 
 * @package ChauffeurBooking
 * @since 1.0.0
 */
class Chauffeur_Routes_Database implements DatabaseInterface {
    /**
     * @var wpdb $wpdb WordPress database abstraction object
     */
    private $wpdb;

    /**
     * @var string $table_name The name of the routes table
     */
    private $table_name;

    /**
     * @var string $charset_collate The database charset collation
     */
    private $charset_collate;

    /**
     * Constructor - sets up the database references
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'chauffeur_routes';
        $this->charset_collate = $wpdb->get_charset_collate();
    }

    /**
     * Gets the full table name with prefix
     * 
     * @return string The complete table name
     */
    public function get_table_name(): string {
        return $this->table_name;
    }

    /**
     * Creates the routes table if it doesn't exist
     * 
     * Uses WordPress dbDelta() which handles table creation/modification safely
     */
    public function create_table(): void {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            route_name varchar(100) NOT NULL,
            start_location varchar(255) NOT NULL,
            end_location varchar(255) NOT NULL,
            distance decimal(10,2) NOT NULL COMMENT 'Distance in kilometers',
            price decimal(10,2) NOT NULL COMMENT 'Base price for one-way trip',
            roundtrip_price decimal(10,2) NOT NULL COMMENT 'Price for round trip',
            is_active tinyint(1) DEFAULT 1 NOT NULL COMMENT '1 if route is active',
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY is_active (is_active)
        ) {$this->charset_collate};";

        // dbDelta is picky about formatting - don't change indentation
        dbDelta($sql);

        // Add initial data if table was just created
        if ($this->wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") == $this->table_name) {
            if ($this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}") == 0) {
                $this->insert_initial_routes();
            }
        }
    }

    /**
     * Finds a single route by ID
     *
     * @param int $id The route ID to find
     * @return object|null Route object or null if not found
     */
    public function find(int $id): ?object {
        return $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id)
        );
    }

    /**
     * Gets all routes with optional filters
     *
     * @param array $args {
     *     Optional query parameters
     *
     *     @type int    $limit      Number of records to return
     *     @type int    $offset     Query offset for pagination
     *     @type string $orderby    Column to order by (default: 'route_name')
     *     @type string $order      Sort order (ASC/DESC)
     *     @type bool   $is_active  Filter by active status (true/false)
     * }
     * @return array Array of route objects
     */
    public function get_all(array $args = []): array {
        $defaults = [
            'limit' => 0,
            'offset' => 0,
            'orderby' => 'route_name',
            'order' => 'ASC',
            'is_active' => null
        ];
        $args = wp_parse_args($args, $defaults);

        // Build WHERE clause if filters exist
        $where = [];
        $prepare_values = [];

        if (!is_null($args['is_active'])) {
            $where[] = 'is_active = %d';
            $prepare_values[] = $args['is_active'];
        }

        // Base query
        $query = "SELECT * FROM {$this->table_name}";
        
        // Add WHERE conditions if any exist
        if (!empty($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }

        // Add sorting
        $query .= " ORDER BY {$args['orderby']} {$args['order']}";

        // Add pagination if needed
        if ($args['limit'] > 0) {
            $query .= $this->wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }

        // Prepare the query if we have values to insert
        if (!empty($prepare_values)) {
            $query = $this->wpdb->prepare($query, $prepare_values);
        }

        return $this->wpdb->get_results($query);
    }

    /**
     * Inserts a new route into the database
     *
     * @param array $data Route data including:
     *              - route_name (string)
     *              - start_location (string)
     *              - end_location (string)
     *              - distance (float)
     *              - price (float)
     *              - roundtrip_price (float)
     * @return int The ID of the newly created route
     * @throws InvalidArgumentException If required data is missing
     */
    public function save(array $data): int {
        $data = $this->sanitize_data($data);
        
        // Set timestamps
        $data['created_at'] = current_time('mysql');
        $data['updated_at'] = current_time('mysql');
        
        $this->wpdb->insert($this->table_name, $data);
        return $this->wpdb->insert_id;
    }

    /**
     * Updates an existing route
     *
     * @param int $id The route ID to update
     * @param array $data The data to update
     * @return bool True if update was successful
     */
    public function update(int $id, array $data): bool {
        $data = $this->sanitize_data($data);
        $data['updated_at'] = current_time('mysql');
        
        return (bool) $this->wpdb->update(
            $this->table_name,
            $data,
            ['id' => $id]
        );
    }

    /**
     * Deletes a route from the database
     *
     * @param int $id The route ID to delete
     * @return bool True if deletion was successful
     */
    public function delete(int $id): bool {
        return (bool) $this->wpdb->delete(
            $this->table_name,
            ['id' => $id],
            ['%d']
        );
    }

    /**
     * Sanitizes route data before database operations
     *
     * @param array $data The data to sanitize
     * @return array Sanitized data
     */
    private function sanitize_data(array $data): array {
        $columns = $this->wpdb->get_col("DESC {$this->table_name}", 0);
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $columns)) {
                $sanitized[$key] = $this->sanitize_field($key, $value);
            }
        }

        return $sanitized;
    }

    /**
     * Sanitizes individual fields based on their type
     *
     * @param string $field The field name
     * @param mixed $value The field value
     * @return mixed The sanitized value
     */
    private function sanitize_field(string $field, $value) {
        switch ($field) {
            case 'id':
                return absint($value);
            case 'distance':
            case 'price':
            case 'roundtrip_price':
                return floatval($value);
            case 'is_active':
                return $value ? 1 : 0;
            case 'created_at':
            case 'updated_at':
                return sanitize_text_field($value);
            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * Inserts initial sample routes into the database
     */
    private function insert_initial_routes(): void {
        $default_routes = [
            [
                'route_name' => 'Airport Transfer',
                'start_location' => 'City Center',
                'end_location' => 'International Airport',
                'distance' => 25.5,
                'price' => 75.00,
                'roundtrip_price' => 120.00,
                'is_active' => 1
            ],
            [
                'route_name' => 'City to Resort',
                'start_location' => 'Downtown',
                'end_location' => 'Mountain Resort',
                'distance' => 42.0,
                'price' => 110.00,
                'roundtrip_price' => 180.00,
                'is_active' => 1
            ]
        ];

        foreach ($default_routes as $route) {
            $this->save($route);
        }
    }
}
