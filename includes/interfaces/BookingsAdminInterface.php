<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
// /includes/interfaces/BookingsAdminInterface.php

interface BookingsAdminInterface {
    public function render_page(): void;
    public function render_calendar(): void;
    public function delete(int $id): bool;
    public function export_bookings(array $ids): void;
    public function handle_form_submission(): void;
}
