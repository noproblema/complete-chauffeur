<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Vehicle Repository - Handles all database operations for vehicles
 * 
 * Implements the Vehicle_Repository_Interface to provide:
 * - Vehicle inventory management
 * - Availability checks
 * - Class-based lookups
 */
class Chauffeur_Vehicle_Repository implements Vehicle_Repository_Interface {
    /**
     * @var Chauffeur_Vehicles_Database $database Reference to the database handler
     */
    private $database;

    /**
     * Constructor - Initializes with database dependency
     * 
     * @param Chauffeur_Vehicles_Database $database The vehicles database handler
     */
    public function __construct(Chauffeur_Vehicles_Database $database) {
        $this->database = $database;
    }

    /**
     * Find a single vehicle by ID
     * 
     * @param int $id The vehicle ID to find
     * @return object|null Vehicle object or null if not found
     */
    public function find(int $id): ?object {
        return $this->database->find($id);
    }

    /**
     * Find vehicle by class name (e.g., 'sedan', 'suv')
     * 
     * @param string $class The vehicle class to search for
     * @return object|null Vehicle object or null if not found
     */
    public function get_by_class(string $class): ?object {
        $vehicles = $this->database->get_all([
            'class' => $class,
            'limit' => 1
        ]);
        return $vehicles[0] ?? null;
    }

    /**
     * Get all available vehicles
     * 
     * @return array Array of vehicle objects
     */
    public function get_all(): array {
        return $this->database->get_all(['is_available' => 1]);
    }

    /**
     * Get available vehicles with quantity limit
     * 
     * @param int $quantity Maximum number of vehicles to return
     * @return array Array of vehicle objects
     */
    public function get_available(int $quantity = 1): array {
        return $this->database->get_all([
            'is_available' => 1,
            'limit' => $quantity
        ]);
    }

    /**
     * Create a new vehicle record
     * 
     * @param array $data Vehicle data including:
     *              - class (string)
     *              - name (string)
     *              - description (string)
     *              - capacity (int)
     *              - price_rate (float)
     * @return int The ID of the newly created vehicle
     */
    public function save(array $data): int {
        return $this->database->save($data);
    }

    /**
     * Update an existing vehicle
     * 
     * @param int $id The vehicle ID to update
     * @param array $data The data to update
     * @return bool True if update was successful
     */
    public function update(int $id, array $data): bool {
        return $this->database->update($id, $data);
    }

    /**
     * Delete a vehicle record
     * 
     * @param int $id The vehicle ID to delete
     * @return bool True if deletion was successful
     */
    public function delete(int $id): bool {
        return $this->database->delete($id);
    }
}


