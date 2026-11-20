<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles form submissions and rendering for the booking form
 */
class Complete_Chauffeur_Form_Handler implements Form_Handler_Interface {
    private $booking_service;    // Service for booking operations
    private $route_repository;   // Repository for route data
    private $vehicle_repository; // Repository for vehicle data
    private $submitted_data = []; // Stores submitted form data
    private $transient_key = 'complete_chauffeur_booking_notice'; // Key for temporary notices


    /**
     * Constructor
     *
     * @param Booking_Service_Interface $booking_service
     * @param Route_Repository_Interface $route_repository
     * @param Vehicle_Repository_Interface $vehicle_repository
     */
    public function __construct(
        Booking_Service_Interface $booking_service,
        Route_Repository_Interface $route_repository,
        Vehicle_Repository_Interface $vehicle_repository
    ) {
        $this->booking_service = $booking_service;
        $this->route_repository = $route_repository;
        $this->vehicle_repository = $vehicle_repository;
        $this->init_hooks();
    }

    /**
     * Render the booking form (only public method needed for display)
     * 
     * @return string HTML output of the form
     */
    public function render_form(): string {
        // Handle form submission if needed
        $this->handle_submission();
        
        // Start output buffering
        ob_start();
        
        // Make routes and vehicles available to template
        $routes = $this->get_routes();
        $vehicles = $this->get_vehicles();
        include COMPLETE_CHAUFFEUR_PATH . 'assets/templates/booking-form.php';
        
        // Return the buffered content
        return ob_get_clean();
    }

    /**
     * Display any stored notices (public as called from template)
     */
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

    /**
     * Get field value for form population (public as called from template)
     * @param string $field Field name
     * @param mixed $default Default value if field not set
     * @return string Field value
     */
    public function get_field_value(string $field, $default = ''): string {
        return $this->submitted_data[$field] ?? $default;
    }

    /**
     * Handle AJAX submissions (public as called via WordPress hooks)
     */
    public function handle_ajax_submission(): void {
        // Verify nonce for AJAX request
        check_ajax_referer('complete_chauffeur_booking_nonce', 'nonce');

        try {
            // Sanitize input data
            $data = $this->sanitize_input($_POST);
            
            // Create booking and get ID
            $booking_id = $this->booking_service->create_booking($data);
            
            // Return success response
            wp_send_json_success([
                'message' => __('Booking successful!', 'complete-chauffeur'),
                'booking_id' => $booking_id
            ]);
            
        } catch (Exception $e) {
            // Return error response
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enqueue assets (public as called via WordPress hooks)
     */
    public function enqueue_assets(): void {
        // Enqueue CSS
        wp_enqueue_style(
            'complete-chauffeur-form',
            COMPLETE_CHAUFFEUR_URL . 'assets/css/frontend.css',
            [],
            COMPLETE_CHAUFFEUR_VERSION
        );

        // Enqueue JavaScript
        wp_enqueue_script(
            'complete-chauffeur-form',
            COMPLETE_CHAUFFEUR_URL . 'assets/js/frontend.js',
            ['jquery', 'flatpickr-js'],
            COMPLETE_CHAUFFEUR_VERSION,
            true
        );

        // Enqueue Flatpickr CSS and JS for the frontend
        wp_enqueue_style(
            'flatpickr-css',
            COMPLETE_CHAUFFEUR_URL . 'assets/css/flatpickr.min.css',
            [],
            '4.6.9'
        );

        wp_enqueue_script(
            'flatpickr-js',
            COMPLETE_CHAUFFEUR_URL . 'assets/js/flatpickr.min.js',
            ['jquery'], // Dependencies (jQuery)
            '4.6.9',
            true
        );

        // Localize script for AJAX
        wp_localize_script('complete-chauffeur-form', 'chauffeurBooking', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('complete_chauffeur_booking_nonce')
        ]);
    }

    /***************************
     * PRIVATE METHODS BELOW
     ***************************/

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks(): void {
        // Enqueue frontend assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // Handle AJAX submissions
        add_action('wp_ajax_complete_chauffeur_submit_booking', [$this, 'handle_ajax_submission']);
        add_action('wp_ajax_nopriv_complete_chauffeur_submit_booking', [$this, 'handle_ajax_submission']);
    }

    /**
     * Handle form submission (both regular and AJAX)
     */
    private function handle_submission(): void {
        // Check if form was submitted and verify nonce
        if (!$this->is_form_submitted()) {
            return;
        }

        // Sanitize and store submitted data
        $this->submitted_data = $this->sanitize_input($_POST);

        try {
            // Create booking through service layer
            $booking_id = $this->booking_service->create_booking($this->submitted_data);
            
            // Set success notice
            $this->set_notice(__('Booking created successfully!', 'complete-chauffeur'));
            
        } catch (Exception $e) {
            // Set error notice if something goes wrong
            $this->set_notice($e->getMessage(), 'error');
        }
    }

    /**
     * Check if form was submitted with valid nonce
     * 
     * @return bool True if valid form submission
     */
    private function is_form_submitted(): bool {
        return isset($_POST['chbo_submit']) && 
               wp_verify_nonce($_POST['complete_chauffeur_booking_nonce'], 'complete_chauffeur_booking_form');
    }

    /**
     * Sanitize form input data
     * 
     * @param array $input Raw form data
     * @return array Sanitized data
     */
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

    /**
     * Set a transient notice to display to the user
     * 
     * @param string $message The notice message
     * @param string $type The notice type (success|error)
     */
    private function set_notice(string $message, string $type = 'success'): void {
        set_transient($this->transient_key, [
            'message' => $message,
            'type' => $type
        ], 30); // Store for 30 seconds
    }

    /**
     * Get available routes
     * 
     * @return array List of route objects
     */
    public function get_routes(): array {
        return $this->route_repository->get_all();
    }

    /**
     * Get available vehicles
     * 
     * @return array List of vehicle objects
     */
    public function get_vehicles(): array {
        return $this->vehicle_repository->get_all();
    }
}




