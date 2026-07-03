<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

interface Bookings_Admin_Interface {
    public function render_page(): void;
    public function render_calendar(): void;
    public function delete(int $id): bool;
    public function export_bookings(array $ids): void;
    public function handle_form_submission(): void;
}


