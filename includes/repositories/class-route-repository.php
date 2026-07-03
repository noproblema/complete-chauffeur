<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Route Repository - Handles all business logic for routes
 * 
 * Implements the Route_Repository_Interface to provide:
 * - Basic CRUD operations
 * - Route-specific queries
 * - Data access abstraction
 */
class Chauffeur_Route_Repository implements Route_Repository_Interface {
    /**
     * @var Chauffeur_Routes_Database $database Reference to the database handler
     */
    private $database;

    /**
     * Constructor - Initializes with database dependency
     * 
     * @param Chauffeur_Routes_Database $database The routes database handler
     */
    public function __construct(Chauffeur_Routes_Database $database) {
        $this->database = $database;
    }

    /**
     * Find a single route by ID
     * 
     * @param int $id The route ID to find
     * @return object|null Route object or null if not found
     */
    public function find(int $id): ?object {
        return $this->database->find($id);
    }

    /**
     * Get all active routes
     * 
     * @return array Array of route objects
     */
    public function get_all(): array {
        return $this->database->get_all(['is_active' => 1]);
    }

    /**
     * Find route by exact name match
     * 
     * @param string $name The route name to search for
     * @return object|null Route object or null if not found
     */
    public function get_by_name(string $name): ?object {
        $routes = $this->database->get_all([
            'route_name' => $name,
            'limit' => 1
        ]);
        return $routes[0] ?? null;
    }

    /**
     * Create a new route
     * 
     * @param array $data Route data including:
     *              - route_name (string)
     *              - start_location (string)
     *              - end_location (string)  
     *              - distance (float)
     *              - price (float)
     *              - roundtrip_price (float)
     * @return int The ID of the newly created route
     */
    public function save(array $data): int {
        $data = array_merge([
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ], $data);
        return $this->database->save($data);
    }

    /**
     * Update an existing route
     * 
     * @param int $id The route ID to update
     * @param array $data The data to update
     * @return bool True if update was successful
     */
    public function update(int $id, array $data): bool {
        $data['updated_at'] = current_time('mysql');
        return $this->database->update($id, $data);
    }

    /**
     * Delete a route
     * 
     * @param int $id The route ID to delete
     * @return bool True if deletion was successful
     */
    public function delete(int $id): bool {
        return $this->database->delete($id);
    }

    /**
     * Get routes with optional filters
     */
    public function get_filtered(array $filters = []): array {
        return $this->database->get_all($filters);
    }
}


