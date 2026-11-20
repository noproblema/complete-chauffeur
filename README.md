# Complete Chauffeur Plugin

A comprehensive WordPress plugin for managing chauffeur bookings.

## Features

- Booking Management - Create, edit, and manage chauffeur bookings
- Vehicle Management - Manage vehicle inventory and availability
- Route Management - Define routes with pricing
- Admin Dashboard - Full admin interface with WP_List_Table
- Calendar View - Visual booking calendar
- Security - Nonce verification, capability checks, CSRF protection
- Performance - Caching, optimized queries, autoloader
- Email Notifications - Automatic confirmation emails
- Logging - Comprehensive logging system

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Installation

1. Upload the plugin folder to /wp-content/plugins/
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Access the plugin from the WordPress admin menu

## Configuration

### Creating a Chauffeur

1. Go to Complete Chauffeur -> Add New
2. Fill in the chauffeur details
3. Save the chauffeur

### Creating a Route

1. Go to Complete Chauffeur -> Routes -> Add New
2. Fill in the route details
3. Save the route

### Creating a Booking

1. Go to Complete Chauffeur -> All Bookings -> Add New
2. Select chauffeur, route, and date/time
3. Save the booking

## Usage Examples

### Display Booking Form

Add the shortcode to any page or post:
[complete-chauffeur_booking]

### Programmatic Bookings

// Create a booking
$service = new Complete_Chauffeur_Booking_Service(
    new Complete_Chauffeur_Booking_Repository(),
    new Complete_Chauffeur_Route_Repository(),
    new Complete_Chauffeur_Vehicle_Repository()
);

$booking_id = $service->create_booking([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+1234567890',
    'vehicle' => 'sedan',
    'route_id' => 1,
    'pickup_date' => '2024-01-15 14:00:00',
    'pickup_location' => 'City Center',
    'destination' => 'Airport'
]);

## API

### Class Reference

#### Complete_Chauffeur_Booking_Service
Main service for booking operations.

Methods:
- create_booking(array $data): int
- update_booking(int $id, array $data): bool
- get_booking(int $id): ?object
- get_upcoming_bookings(int $days = 7): array
- calculate_price(array $booking_data): float

#### Complete_Chauffeur_Booking_Repository
Data access layer for bookings.

Methods:
- find(int $id): ?object
- get_all(array $args = []): array
- get_paginated(int $per_page = 10, int $page = 1): array
- get_by_date_range(string $start_date, string $end_date): array
- save(array $data): int
- update(int $id, array $data): bool
- delete(int $id): bool
- update_status(int $id, string $status): bool

### Hooks

#### Actions
- complete_chauffeur_before_booking_created - Before booking creation
- complete_chauffeur_after_booking_created - After booking creation
- complete_chauffeur_booking_status_updated - When status changes

#### Filters
- complete_chauffeur_booking_data - Modify booking data before save
- complete_chauffeur_price_calculation - Modify price calculation

## Security

- All admin actions require capability checks
- Nonce verification on all forms
- CSRF protection on all POST requests
- Input sanitization and validation
- Prepared statements for database queries
- Rate limiting on form submissions
- Security headers added automatically

## Contributing

Please read CONTRIBUTING.md for details on our code of conduct and the process for submitting pull requests.

## License

This project is licensed under the GPL v2 or later - see the LICENSE file for details.

---
Plugin restored and renamed: 2026-07-04 14:09:11
