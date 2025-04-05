<?php
// /includes/services/class-booking-service.php

class Chauffeur_Booking_Service implements BookingServiceInterface {
    private $booking_repo;
    private $route_repo;
    private $vehicle_repo;

    public function __construct(
        BookingRepositoryInterface $booking_repo,
        RouteRepositoryInterface $route_repo,
        VehicleRepositoryInterface $vehicle_repo
    ) {
        $this->booking_repo = $booking_repo;
        $this->route_repo = $route_repo;
        $this->vehicle_repo = $vehicle_repo;
    }

    public function create_booking(array $data): int {
        $this->validate_booking_data($data);
        $data['price'] = $this->calculate_price($data);
        return $this->booking_repo->save($data);
    }

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

    // ... other interface implementations ...
}
