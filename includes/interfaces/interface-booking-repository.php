<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
interface Booking_Repository_Interface {
    public function find(int $id): ?object;
    public function get_all(array $args = []): array;
    public function get_paginated(int $per_page = 10, int $page = 1): array;
    public function get_by_date_range(string $start_date, string $end_date): array;
    public function save(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function update_status(int $id, string $status): bool;
}


