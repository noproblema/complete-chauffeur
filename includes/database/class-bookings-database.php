<?php
// /includes/database/class-bookings-database.php

class Chauffeur_Bookings_Database implements DatabaseInterface {
    private $wpdb;
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'chauffeur_bookings';
    }

    public function get_table_name(): string {
        return $this->table_name;
    }

    public function find(int $id): ?object {
        return $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id)
        ) ?: null;
    }

    public function get_all(array $args = []): array {
        $defaults = [
            'limit' => 0,
            'offset' => 0,
            'orderby' => 'pickup_date',
            'order' => 'DESC'
        ];
        $args = wp_parse_args($args, $defaults);

        $query = "SELECT * FROM {$this->table_name}";
        $query .= $this->build_where_clause($args);
        $query .= " ORDER BY {$args['orderby']} {$args['order']}";

        if ($args['limit'] > 0) {
            $query .= $this->wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }

        return $this->wpdb->get_results($query);
    }

    // ... other interface implementations ...
}
