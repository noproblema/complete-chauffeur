<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Routes database operations
 */
class Complete_Chauffeur_Routes_Database extends Complete_Chauffeur_Base_Database {
    protected function get_table_prefix(): string {
        return 'complete_chauffeur_routes';
    }

    protected function get_table_schema(): string {
        return "CREATE TABLE {$this->table_name} (
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
    }

    protected function get_default_query_args(): array {
        return array_merge(parent::get_default_query_args(), [
            'orderby' => 'route_name',
            'is_active' => null
        ]);
    }

    protected function get_where_conditions(array $args): array {
        $conditions = [];
        
        if (!is_null($args['is_active'])) {
            $conditions[] = [
                'sql' => 'is_active = %d',
                'value' => $args['is_active'] ? 1 : 0
            ];
        }

        return $conditions;
    }

    protected function sanitize_field(string $field, $value) {
        switch ($field) {
            case 'id':
                return absint($value);
            case 'distance':
            case 'price':
            case 'roundtrip_price':
                return floatval($value);
            case 'is_active':
                return $value ? 1 : 0;
            default:
                return sanitize_text_field($value);
        }
    }

    protected function insert_default_data(): void {
        $this->wpdb->insert($this->table_name, [
            'route_name' => 'Airport Transfer',
            'start_location' => 'City Center',
            'end_location' => 'International Airport',
            'distance' => 25.5,
            'price' => 75.00,
            'roundtrip_price' => 120.00,
            'is_active' => 1,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ]);
    }
}




