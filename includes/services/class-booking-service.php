<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles business logic for chauffeur bookings
 * 
 * @package ChauffeurBooking
 */
class Complete_Chauffeur_Booking_Service implements Booking_Service_Interface {
    /** @var Booking_Repository_Interface $booking_repository */
    private $booking_repository;
    
    /** @var Route_Repository_Interface $route_repository */
    private $route_repository;
    
    /** @var Vehicle_Repository_Interface $vehicle_repository */
    private $vehicle_repository;

    /**
     * Constructor
     *
     * @param Booking_Repository_Interface $booking_repository
     * @param Route_Repository_Interface $route_repository
     * @param Vehicle_Repository_Interface $vehicle_repository
     */
    public function __construct(
        Booking_Repository_Interface $booking_repository,
        Route_Repository_Interface $route_repository,
        Vehicle_Repository_Interface $vehicle_repository
    ) {
        $this->booking_repository = $booking_repository;
        $this->route_repository = $route_repository;
        $this->vehicle_repository = $vehicle_repository;
    }

    /**
     * Create a new booking
     *
     * @param array $data Booking data including:
     *              - name (string) Customer name
     *              - email (string) Customer email
     *              - phone (string) Customer phone
     *              - vehicle (string) Vehicle class
     *              - route_id (int) Route ID
     *              - pickup_date (string) Pickup datetime
     *              - [roundtrip] (bool) Whether roundtrip
     * @return int Booking ID
     * @throws InvalidArgumentException If validation fails
     * @throws RuntimeException If booking creation fails
     */
    public function create_booking(array $data): int {
        $this->validate_booking_data($data);
        $data['status'] = 'pending';
        $data['created_at'] = current_time('mysql');
        $data['price'] = $this->calculate_price($data);
        return $this->booking_repository->save($data);
    }

    /**
     * Update an existing booking
     *
     * @param int $id Booking ID
     * @param array $data Booking data to update
     * @return bool True on success
     * @throws InvalidArgumentException If validation fails
     */
    public function update_booking(int $id, array $data): bool {
        $this->validate_booking_data($data);
        if (isset($data['route_id'])) {
            $data['price'] = $this->calculate_price($data);
        }
        return $this->booking_repository->update($id, $data);
    }

    /**
     * Get a booking by ID
     *
     * @param int $id Booking ID
     * @return object|null Booking object or null if not found
     */
    public function get_booking(int $id): ?object {
        return $this->booking_repository->find($id);
    }

    /**
     * Get upcoming bookings within specified days
     *
     * @param int $days Number of days to look ahead (default: 7)
     * @return array Array of booking objects
     */
    public function get_upcoming_bookings(int $days = 7): array {
        $start_date = current_time('mysql');
        $end_date = date('Y-m-d H:i:s', strtotime("+$days days"));
        return $this->booking_repository->get_by_date_range($start_date, $end_date);
    }

    /**
     * Calculate booking price based on route and vehicle
     *
     * @param array $booking_data Must contain route_id and vehicle
     * @return float Calculated price
     * @throws RuntimeException If route or vehicle not found
     */
    public function calculate_price(array $booking_data): float {
        $route = $this->route_repository->find($booking_data['route_id']);
        $vehicle = $this->vehicle_repository->get_by_class($booking_data['vehicle']);

        if (!$route || !$vehicle) {
            throw new RuntimeException('Invalid route or vehicle');
        }

        $base_price = !empty($booking_data['roundtrip']) 
            ? $route->roundtrip_price 
            : $route->price;

        return $base_price * ($vehicle->price_rate / 100);
    }

    /**
     * Validate booking data
     *
     * @param array $data Booking data to validate
     * @throws InvalidArgumentException If validation fails
     */
    private function validate_booking_data(array $data): void {
        $required = ['name', 'email', 'phone', 'vehicle', 'route_id', 'pickup_date'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException("Missing required field: $field");
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address');
        }

        if (strtotime($data['pickup_date']) < time()) {
            throw new InvalidArgumentException('Pickup date must be in the future');
        }
    }
}




