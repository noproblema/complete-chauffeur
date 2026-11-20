<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

interface Booking_Service_Interface {
    public function create_booking(array $data): int;
    public function update_booking(int $id, array $data): bool;
    public function get_booking(int $id): ?object;
    public function get_upcoming_bookings(int $days = 7): array;
    public function calculate_price(array $booking_data): float;
}




