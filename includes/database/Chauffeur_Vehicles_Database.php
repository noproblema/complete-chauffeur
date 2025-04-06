<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles all database operations for the vehicles table
 * 
 * @package ChauffeurBooking
 * @since 1.0.0
 */
class Chauffeur_Vehicles_Database implements DatabaseInterface {
    /**
     * @var wpdb $wpdb WordPress database abstraction object
     */
    private $wpdb;

    /**
     * @var string $table_name The name of the vehicles table
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
        $this->table_name = $wpdb->prefix . 'chauffeur_vehicles';
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
     * Creates the vehicles table if it doesn't exist
     * 
     * Uses WordPress dbDelta() which handles table creation/modification safely
     */
    public function create_table(): void {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            class varchar(50) NOT NULL COMMENT 'Vehicle class (sedan, suv, etc)',
            name varchar(100) NOT NULL COMMENT 'Display name',
            description text DEFAULT NULL COMMENT 'Vehicle description',
            capacity int(11) NOT NULL COMMENT 'Passenger capacity',
            price_rate decimal(5,2) NOT NULL COMMENT 'Percentage rate to apply to base price',
            image_url varchar(255) DEFAULT NULL COMMENT 'URL of vehicle image',
            is_available tinyint(1) DEFAULT 1 NOT NULL COMMENT '1 if available for bookings',
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY class (class),
            KEY is_available (is_available)
        ) {$this->charset_collate};";

        // dbDelta is picky about formatting - don't change indentation
        dbDelta($sql);

        // Add initial data if table was just created
        if ($this->wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") == $this->table_name) {
            if ($this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}") == 0) {
                $this->insert_initial_vehicles();
            }
        }
    }

    /**
     * Finds a vehicle by its ID
     *
     * @param int $id The vehicle ID to find
     * @return object|null Vehicle object or null if not found
     */
    public function find(int $id): ?object {
        return $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id)
        );
    }

    /**
     * Finds a vehicle by its class name
     *
     * @param string $class The vehicle class (sedan, suv, etc)
     * @return object|null Vehicle object or null if not found
     */
    public function get_by_class(string $class): ?object {
        return $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table_name} WHERE class = %s", $class)
        );
    }

    /**
     * Gets all vehicles with optional filters
     *
     * @param array $args {
     *     Optional query parameters
     *
     *     @type int    $limit        Number of records to return
     *     @type int    $offset       Query offset for pagination
     *     @type string $orderby      Column to order by (default: 'name')
     *     @type string $order        Sort order (ASC/DESC)
     *     @type bool   $is_available Filter by availability status
     * }
     * @return array Array of vehicle objects
     */
    public function get_all(array $args = []): array {
        $defaults = [
            'limit' => 0,
            'offset' => 0,
            'orderby' => 'name',
            'order' => 'ASC',
            'is_available' => null
        ];
        $args = wp_parse_args($args, $defaults);

        // Build WHERE clause if filters exist
        $where = [];
        $prepare_values = [];

        if (!is_null($args['is_available'])) {
            $where[] = 'is_available = %d';
            $prepare_values[] = $args['is_available'];
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
     * Gets available vehicles
     *
     * @param int $quantity Maximum number of vehicles to return
     * @return array Array of available vehicle objects
     */
    public function get_available(int $quantity = 1): array {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} 
                WHERE is_available = 1 
                ORDER BY name ASC
                LIMIT %d",
                $quantity
            )
        );
    }

    /**
     * Inserts a new vehicle into the database
     *
     * @param array $data Vehicle data including:
     *              - class (string) Vehicle class (sedan, suv, etc)
     *              - name (string) Display name
     *              - capacity (int) Passenger capacity
     *              - price_rate (float) Percentage rate for pricing
     * @return int The ID of the newly created vehicle
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
     * Updates an existing vehicle
     *
     * @param int $id The vehicle ID to update
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
     * Deletes a vehicle from the database
     *
     * @param int $id The vehicle ID to delete
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
     * Sanitizes vehicle data before database operations
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
            case 'capacity':
                return absint($value);
            case 'price_rate':
                return floatval($value);
            case 'is_available':
                return $value ? 1 : 0;
            case 'created_at':
            case 'updated_at':
                return sanitize_text_field($value);
            case 'description':
                return sanitize_textarea_field($value);
            case 'image_url':
                return esc_url_raw($value);
            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * Inserts initial sample vehicles into the database
     */
    private function insert_initial_vehicles(): void {
        $default_vehicles = [
            [
                'class' => 'sedan',
                'name' => 'Executive Sedan',
                'description' => 'Luxury sedan with leather seats',
                'capacity' => 3,
                'price_rate' => 100.00,
                'is_available' => 1
            ],
            [
                'class' => 'suv',
                'name' => 'Premium SUV',
                'description' => 'Spacious SUV for groups',
                'capacity' => 6,
                'price_rate' => 130.00,
                'is_available' => 1
            ],
            [
                'class' => 'limousine',
                'name' => 'Stretch Limousine',
                'description' => 'Luxury limousine for special occasions',
                'capacity' => 8,
                'price_rate' => 200.00,
                'is_available' => 1
            ]
        ];

        foreach ($default_vehicles as $vehicle) {
            $this->save($vehicle);
        }
    }
}
