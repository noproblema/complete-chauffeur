<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Bookings database operations
 */
class Complete_Chauffeur_Bookings_Database extends Complete_Chauffeur_Base_Database {
    protected function get_table_prefix(): string {
        return 'complete_chauffeur_bookings';
    }

    protected function get_table_schema(): string {
        return "CREATE TABLE {$this->table_name} (
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
    }

    protected function get_default_query_args(): array {
        return array_merge(parent::get_default_query_args(), [
            'status' => '',
            'start_date' => '',
            'end_date' => ''
        ]);
    }

    protected function get_where_conditions(array $args): array {
        $conditions = [];
        
        if ($args['status']) {
            $conditions[] = [
                'sql' => 'status = %s',
                'value' => $args['status']
            ];
        }

        if ($args['start_date']) {
            $conditions[] = [
                'sql' => 'pickup_date >= %s',
                'value' => $args['start_date']
            ];
        }

        if ($args['end_date']) {
            $conditions[] = [
                'sql' => 'pickup_date <= %s',
                'value' => $args['end_date']
            ];
        }

        return $conditions;
    }

    protected function sanitize_field(string $field, $value) {
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




