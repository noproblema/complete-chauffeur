<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Repository for accessing booking data
 * 
 * @package ChauffeurBooking
 */
class Chauffeur_Booking_Repository implements BookingRepositoryInterface {
    /** @var Chauffeur_Bookings_Database $database */
    private $database;

    /**
     * Constructor
     *
     * @param Chauffeur_Bookings_Database $database
     */
    public function __construct(Chauffeur_Bookings_Database $database) {
        $this->database = $database;
    }

    /**
     * Find booking by ID
     *
     * @param int $id Booking ID
     * @return object|null Booking object or null if not found
     */
    public function find(int $id): ?object {
        return $this->database->find($id);
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
        return $this->database->get_all($args);
    }

    /**
     * Get paginated bookings
     *
     * @param int $per_page Number of items per page
     * @param int $page Current page number
     * @return array Array of booking objects
     */
    public function get_paginated(int $per_page = 10, int $page = 1): array {
        $offset = ($page - 1) * $per_page;
        return $this->database->get_all([
            'limit' => $per_page,
            'offset' => $offset
        ]);
    }

    /**
     * Get bookings within a date range
     *
     * @param string $start_date Start date (Y-m-d H:i:s)
     * @param string $end_date End date (Y-m-d H:i:s)
     * @return array Array of booking objects
     */
    public function get_by_date_range(string $start_date, string $end_date): array {
        return $this->database->get_all([
            'start_date' => $start_date,
            'end_date' => $end_date,
            'orderby' => 'pickup_date',
            'order' => 'ASC'
        ]);
    }

    /**
     * Save new booking
     *
     * @param array $data Booking data
     * @return int New booking ID
     */
    public function save(array $data): int {
        $defaults = [
            'status' => 'pending',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ];
        $data = array_merge($defaults, $data);
        return $this->database->save($data);
    }

    /**
     * Update existing booking
     *
     * @param int $id Booking ID
     * @param array $data Data to update
     * @return bool True on success
     */
    public function update(int $id, array $data): bool {
        $data['updated_at'] = current_time('mysql');
        return $this->database->update($id, $data);
    }

    /**
     * Delete booking
     *
     * @param int $id Booking ID
     * @return bool True on success
     */
    public function delete(int $id): bool {
        return $this->database->delete($id);
    }

    /**
     * Update booking status
     *
     * @param int $id Booking ID
     * @param string $status New status
     * @return bool True on success
     * @throws InvalidArgumentException If invalid status provided
     */
    public function update_status(int $id, string $status): bool {
        $valid_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        if (!in_array($status, $valid_statuses)) {
            throw new InvalidArgumentException('Invalid booking status');
        }
        return $this->database->update($id, ['status' => $status]);
    }
}
