<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Abstract base database class with shared functionality
 * 
 * @package ChauffeurBooking
 */
abstract class Chauffeur_Base_Database implements Database_Interface {
    /**
     * @var wpdb $wpdb WordPress database abstraction
     */
    protected $wpdb;

    /**
     * @var string $table_name Database table name
     */
    protected $table_name;

    /**
     * @var string $charset_collate Database charset
     */
    protected $charset_collate;

    /**
     * Constructor - sets up database references
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . $this->get_table_prefix();
        $this->charset_collate = $wpdb->get_charset_collate();
    }

    /**
     * Get table name with prefix
     * 
     * @return string
     */
    public function get_table_name(): string {
        return $this->table_name;
    }

    /**
     * Child classes must define their table prefix
     * 
     * @return string Table prefix without wp_ prefix
     */
    abstract protected function get_table_prefix(): string;

    /**
     * Create the database table
     */
    public function create_table(): void {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        $schema = $this->get_table_schema();
        dbDelta($schema);
        
        $this->maybe_insert_default_data();
    }

    /**
     * Get the full table schema
     * 
     * @return string SQL CREATE TABLE statement
     */
    abstract protected function get_table_schema(): string;

    /**
     * Insert default data if table is empty
     */
    protected function maybe_insert_default_data(): void {
        if ($this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}") == 0) {
            $this->insert_default_data();
        }
    }

    /**
     * Child classes can implement default data insertion
     */
    protected function insert_default_data(): void {
        // Optional implementation in child classes
    }

    /**
     * Drop the database table
     */
    public function drop_table(): bool {
        $this->clear_table_transients();
        return (bool) $this->wpdb->query("DROP TABLE IF EXISTS {$this->table_name}");
    }

    /**
     * Clear all transients related to this table
     */
    public function clear_table_transients(): void {
        $this->wpdb->query(
            $this->wpdb->prepare(
                "DELETE FROM {$this->wpdb->options} 
                 WHERE option_name LIKE %s",
                '_transient_chauffeur_booking_' . $this->get_table_prefix() . '_%'
            )
        );
    }

    /**
     * Find a single record by ID
     * 
     * @param int $id Record ID
     * @return object|null
     */
    public function find(int $id): ?object {
        return $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id)
        ) ?: null;
    }

    /**
     * Get all records with optional filters
     * 
     * @param array $args Query parameters
     * @return array
     */
    public function get_all(array $args = []): array {
        $defaults = $this->get_default_query_args();
        $args = wp_parse_args($args, $defaults);

        $where = $this->build_where_clause($args);
        $query = "SELECT * FROM {$this->table_name} {$where}";

        if ($args['orderby']) {
            $query .= " ORDER BY {$args['orderby']} {$args['order']}";
        }

        if ($args['limit'] > 0) {
            $query .= $this->wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }

        return $this->wpdb->get_results($query);
    }

    /**
     * Get default query arguments
     * 
     * @return array
     */
    protected function get_default_query_args(): array {
        return [
            'limit' => 0,
            'offset' => 0,
            'orderby' => 'id',
            'order' => 'ASC'
        ];
    }

    /**
     * Build WHERE clause from query args
     * 
     * @param array $args Query arguments
     * @return string WHERE clause
     */
    protected function build_where_clause(array $args): string {
        $where = [];
        $values = [];

        foreach ($this->get_where_conditions($args) as $condition) {
            $where[] = $condition['sql'];
            if (isset($condition['value'])) {
                $values[] = $condition['value'];
            }
        }

        if (empty($where)) {
            return '';
        }

        $clause = 'WHERE ' . implode(' AND ', $where);
        return $values ? $this->wpdb->prepare($clause, $values) : $clause;
    }

    /**
     * Get WHERE conditions for query
     * 
     * @param array $args Query arguments
     * @return array
     */
    abstract protected function get_where_conditions(array $args): array;

    /**
     * Save a record (insert or update)
     * 
     * @param array $data Record data
     * @return int Inserted/updated record ID
     */
    public function save(array $data): int {
        $data = $this->sanitize_data($data);
        
        if (!empty($data['id'])) {
            $this->update($data['id'], $data);
            return $data['id'];
        }
        
        $this->wpdb->insert($this->table_name, $data);
        return $this->wpdb->insert_id;
    }

    /**
     * Update a record
     * 
     * @param int $id Record ID
     * @param array $data Update data
     * @return bool
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
     * Delete a record
     * 
     * @param int $id Record ID
     * @return bool
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
     * @param array $data Input data
     * @return array Sanitized data
     */
    protected function sanitize_data(array $data): array {
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
    abstract protected function sanitize_field(string $field, $value);
}


