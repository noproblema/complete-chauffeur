<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Chauffeur_Form_Handler implements FormHandlerInterface {
    private $booking_service;
    private $route_repository;
    private $vehicle_repository;
    private $submitted_data = [];
    private $transient_key = 'chauffeur_booking_notice';

    public function __construct(
        BookingServiceInterface $booking_service,
        RouteRepositoryInterface $route_repository,
        VehicleRepositoryInterface $vehicle_repository
    ) {
        $this->booking_service = $booking_service;
        $this->route_repository = $route_repository;
        $this->vehicle_repository = $vehicle_repository;
        $this->init_hooks();
    }

    public function get_routes(): array {
        return $this->route_repository->get_all();
    }

    public function get_vehicles(): array {
        return $this->vehicle_repository->get_all();
    }

    public function get_field_value(string $field, $default = ''): string {
        // Get from submitted data if available
        if (isset($this->submitted_data[$field])) {
            return $this->submitted_data[$field];
        }
        
        // Fallback to default or empty string
        return $default;
    }

    private function init_hooks(): void {
        add_action('init', [$this, 'maybe_handle_submission']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_chauffeur_submit_booking', [$this, 'handle_ajax_submission']);
        add_action('wp_ajax_nopriv_chauffeur_submit_booking', [$this, 'handle_ajax_submission']);
    }

    public function enqueue_assets(): void {
        wp_enqueue_style(
            'chauffeur-booking-form',
            CHAUFFEUR_BOOKING_URL . 'assets/css/frontend.css',
            [],
            CHAUFFEUR_BOOKING_VERSION
        );

        wp_enqueue_script(
            'chauffeur-booking-form',
            CHAUFFEUR_BOOKING_URL . 'assets/js/frontend.js',
            ['jquery', 'flatpickr'],
            CHAUFFEUR_BOOKING_VERSION,
            true
        );

        wp_localize_script('chauffeur-booking-form', 'chauffeurBooking', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('chauffeur_booking_nonce')
        ]);
    }

    public function render_form(): string {
        ob_start();
        include CHAUFFEUR_BOOKING_PATH . 'templates/booking-form.php';
        return ob_get_clean();
    }

    public function maybe_handle_submission(): void {
        if ($this->is_form_submitted()) {
            $this->handle_submission();
        }
    }

    private function is_form_submitted(): bool {
        return isset($_POST['chbo_submit']) && 
               wp_verify_nonce($_POST['chauffeur_booking_nonce'], 'chauffeur_booking_form');
    }

    public function handle_ajax_submission(): void {
        check_ajax_referer('chauffeur_booking_nonce', 'nonce');

        try {
            $data = $this->sanitize_input($_POST);
            $booking_id = $this->booking_service->create_booking($data);
            
            wp_send_json_success([
                'message' => __('Booking successful!', 'chauffeur-booking'),
                'booking_id' => $booking_id
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    private function handle_submission(): void {
        $this->submitted_data = $this->sanitize_input($_POST);

        try {
            $booking_id = $this->booking_service->create_booking($this->submitted_data);
            $this->set_notice(__('Booking created successfully!', 'chauffeur-booking'));
        } catch (Exception $e) {
            $this->set_notice($e->getMessage(), 'error');
        }
    }

    private function sanitize_input(array $input): array {
        return [
            'name' => sanitize_text_field($input['chbo_name'] ?? ''),
            'email' => sanitize_email($input['chbo_email'] ?? ''),
            'phone' => sanitize_text_field($input['chbo_phone'] ?? ''),
            'vehicle' => sanitize_text_field($input['chbo_vehicle'] ?? ''),
            'route_id' => absint($input['chbo_route'] ?? 0),
            'pickup_date' => sanitize_text_field($input['chbo_pickup_date'] ?? ''),
            'pickup_location' => sanitize_text_field($input['chbo_pickup_location'] ?? ''),
            'destination' => sanitize_text_field($input['chbo_destination'] ?? ''),
            'distance' => floatval($input['chbo_distance'] ?? 0),
            'price' => floatval($input['chbo_price'] ?? 0),
            'roundtrip' => isset($input['chbo_roundtrip']) ? 1 : 0,
            'notes' => sanitize_textarea_field($input['chbo_notes'] ?? '')
        ];
    }

    private function set_notice(string $message, string $type = 'success'): void {
        set_transient($this->transient_key, [
            'message' => $message,
            'type' => $type
        ], 30);
    }

    public function display_notices(): void {
        if ($notice = get_transient($this->transient_key)) {
            printf(
                '<div class="notice notice-%s"><p>%s</p></div>',
                esc_attr($notice['type']),
                esc_html($notice['message'])
            );
            delete_transient($this->transient_key);
        }
    }
}
