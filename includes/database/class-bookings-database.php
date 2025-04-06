<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Database operations for bookings table
 * 
 * @package ChauffeurBooking
 */
class Chauffeur_Bookings_Database implements DatabaseInterface {
    /** @var wpdb $wpdb WordPress database abstraction */
    private $wpdb;
    
    /** @var string $table_name Database table name */
    private $table_name;
    private $charset_collate;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'chauffeur_bookings';
        $this->charset_collate = $wpdb->get_charset_collate();
    }

    /**
     * Get table name
     *
     * @return string Table name
     */
    public function get_table_name(): string {
        return $this->table_name;
    }

    public function create_table(): void {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        $sql = "CREATE TABLE {$this->table_name} (
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
     * Find single booking by ID
     *
     * @param int $id Booking ID
     * @return object|null Booking object or null if not found
     */
    public function find(int $id): ?object {
        return $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id)
        ) ?: null;
    }

    /**
     * Get all bookings with optional filters
     *
     * @param array $args {
     *     Optional. Array of query parameters.
     *
     *     @type int    $limit      Number of records to return
     *     @type int    $offset     Query offset
     *     @type string $orderby    Field to order by
     *     @type string $order      Order direction (ASC/DESC)
     *     @type string $status     Filter by status
     *     @type string $start_date Filter by start date
     *     @type string $end_date   Filter by end date
     * }
     * @return array Array of booking objects
     */
    public function get_all(array $args = []): array {
        $defaults = [
            'limit' => 0,
            'offset' => 0,
            'orderby' => 'pickup_date',
            'order' => 'DESC',
            'status' => '',
            'start_date' => '',
            'end_date' => ''
        ];
        $args = wp_parse_args($args, $defaults);

        $where = [];
        $prepare_values = [];

        if ($args['status']) {
            $where[] = 'status = %s';
            $prepare_values[] = $args['status'];
        }

        if ($args['start_date']) {
            $where[] = 'pickup_date >= %s';
            $prepare_values[] = $args['start_date'];
        }

        if ($args['end_date']) {
            $where[] = 'pickup_date <= %s';
            $prepare_values[] = $args['end_date'];
        }

        $query = "SELECT * FROM {$this->table_name}";
        if (!empty($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }
        $query .= " ORDER BY {$args['orderby']} {$args['order']}";

        if ($args['limit'] > 0) {
            $query .= $this->wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }

        if (!empty($prepare_values)) {
            $query = $this->wpdb->prepare($query, $prepare_values);
        }

        return $this->wpdb->get_results($query);
    }

    /**
     * Save new booking
     *
     * @param array $data Booking data
     * @return int Inserted booking ID
     */
    public function save(array $data): int {
        $data = $this->sanitize_data($data);
        $this->wpdb->insert($this->table_name, $data);
        return $this->wpdb->insert_id;
    }

    /**
     * Update existing booking
     *
     * @param int $id Booking ID
     * @param array $data Data to update
     * @return bool True on success
     */
    public function update(int $id, array $data): bool {
        $data = $this->sanitize_data($data);
        return (bool) $this->wpdb->update(
            $this->table_name,
            $data,
            ['id' => $id]
        );
    }

    /**
     * Delete booking
     *
     * @param int $id Booking ID
     * @return bool True on success
     */
    public function delete(int $id): bool {
        return (bool) $this->wpdb->delete(
            $this->table_name,
            ['id' => $id],
            ['%d']
        );
    }

    /**
     * Sanitize data before database operations
     *
     * @param array $data Data to sanitize
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
     * Sanitize individual field based on its type
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @return mixed Sanitized value
     */
    private function sanitize_field(string $field, $value) {
        switch ($field) {
            case 'id':
            case 'route_id':
                return absint($value);
            case 'price':
            case 'distance':
                return floatval($value);
            case 'roundtrip':
                return $value ? 1 : 0;
            case 'status':
                $valid = ['pending', 'confirmed', 'completed', 'cancelled'];
                return in_array($value, $valid) ? $value : 'pending';
            case 'pickup_date':
            case 'created_at':
            case 'updated_at':
                return sanitize_text_field($value);
            default:
                return sanitize_text_field($value);
        }
    }
}
