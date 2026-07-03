<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Vehicles database operations
 */
class Chauffeur_Vehicles_Database extends Chauffeur_Base_Database {
    protected function get_table_prefix(): string {
        return 'chauffeur_vehicles';
    }

    protected function get_table_schema(): string {
        return "CREATE TABLE {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            class varchar(50) NOT NULL COMMENT 'Vehicle class (sedan, suv, etc)',
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
    }

    protected function get_default_query_args(): array {
        return array_merge(parent::get_default_query_args(), [
            'orderby' => 'class',
            'is_available' => null
        ]);
    }

    protected function get_where_conditions(array $args): array {
        $conditions = [];
        
        if (!is_null($args['is_available'])) {
            $conditions[] = [
                'sql' => 'is_available = %d',
                'value' => $args['is_available'] ? 1 : 0
            ];
        }

        return $conditions;
    }

    protected function sanitize_field(string $field, $value) {
        switch ($field) {
            case 'id':
            case 'capacity':
                return absint($value);
            case 'price_rate':
                return floatval($value);
            case 'is_available':
                return $value ? 1 : 0;
            case 'description':
                return sanitize_textarea_field($value);
            case 'image_url':
                return esc_url_raw($value);
            default:
                return sanitize_text_field($value);
        }
    }

    protected function insert_default_data(): void {
        $defaults = [
            [
                'class' => 'sedan',
                'description' => 'Luxury sedan with leather seats',
                'capacity' => 3,
                'price_rate' => 100.00,
                'is_available' => 1,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            [
                'class' => 'suv',
                'description' => 'Spacious SUV for groups',
                'capacity' => 6,
                'price_rate' => 130.00,
                'is_available' => 1,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ]
        ];

        foreach ($defaults as $data) {
            $this->wpdb->insert($this->table_name, $data);
        }
    }
}


