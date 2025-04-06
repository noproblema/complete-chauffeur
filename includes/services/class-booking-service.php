<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles business logic for chauffeur bookings
 * 
 * @package ChauffeurBooking
 */
class Chauffeur_Booking_Service implements BookingServiceInterface {
    /** @var BookingRepositoryInterface $booking_repo */
    private $booking_repo;
    
    /** @var RouteRepositoryInterface $route_repo */
    private $route_repo;
    
    /** @var VehicleRepositoryInterface $vehicle_repo */
    private $vehicle_repo;

    /**
     * Constructor
     *
     * @param BookingRepositoryInterface $booking_repo
     * @param RouteRepositoryInterface $route_repo
     * @param VehicleRepositoryInterface $vehicle_repo
     */
    public function __construct(
        BookingRepositoryInterface $booking_repo,
        RouteRepositoryInterface $route_repo,
        VehicleRepositoryInterface $vehicle_repo
    ) {
        $this->booking_repo = $booking_repo;
        $this->route_repo = $route_repo;
        $this->vehicle_repo = $vehicle_repo;
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
        return $this->booking_repo->save($data);
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
        return $this->booking_repo->update($id, $data);
    }

    /**
     * Get a booking by ID
     *
     * @param int $id Booking ID
     * @return object|null Booking object or null if not found
     */
    public function get_booking(int $id): ?object {
        return $this->booking_repo->find($id);
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
        return $this->booking_repo->get_by_date_range($start_date, $end_date);
    }

    /**
     * Calculate booking price based on route and vehicle
     *
     * @param array $booking_data Must contain route_id and vehicle
     * @return float Calculated price
     * @throws RuntimeException If route or vehicle not found
     */
    public function calculate_price(array $booking_data): float {
        $route = $this->route_repo->find($booking_data['route_id']);
        $vehicle = $this->vehicle_repo->get_by_class($booking_data['vehicle']);

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
