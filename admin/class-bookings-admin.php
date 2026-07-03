<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Admin interface for managing bookings
 *
 * Handles:
 * - Booking list table display
 * - Booking creation/editing
 * - Booking status management
 * - Booking exports
 */
class Chauffeur_Bookings_Admin implements Bookings_Admin_Interface {
    /**
     * @var Booking_Repository_Interface $repository Booking repository instance
     */
    private $repository;

    /**
     * @var array $notices Admin notices to display
     */
    private $notices = [];

    /**
     * Constructor
     *
     * @param Booking_Repository_Interface $repository Booking repository instance
     */
    public function __construct(Booking_Repository_Interface $repository) {
        $this->repository = $repository;
        add_action('admin_init', [$this, 'handle_form_submission']);
        add_action('admin_notices', [$this, 'admin_notices']);
    }

    /**
     * Render the bookings admin page
     *
     * Displays different views based on action:
     * - List view (default)
     * - Edit view
     * - Add new view
     */
    public function render_page(): void {
        $action = $_GET['action'] ?? '';
        $id = $_GET['id'] ?? 0;

        switch ($action) {
            case 'edit':
                $this->render_edit_form(absint($id));
                break;
            case 'add':
                $this->render_add_form();
                break;
            default:
                $this->render_list_table();
        }
    }

    /**
     * Handle booking form submissions (create/update)
     */
    public function handle_form_submission(): void {
        if (!isset($_POST['booking_nonce'])) return;
        
        check_admin_referer('booking_action', 'booking_nonce');

        try {
            $data = $this->sanitize_booking_data($_POST);
            
            if (!empty($_POST['booking_id'])) {
                $this->repository->update(absint($_POST['booking_id']), $data);
                $this->add_notice(__('Booking updated successfully!', 'chauffeur-booking'));
            } else {
                $this->repository->save($data);
                $this->add_notice(__('Booking created successfully!', 'chauffeur-booking'));
            }
        } catch (Exception $e) {
            $this->add_notice($e->getMessage(), 'error');
        }
    }

    /**
     * Display admin notices
     */
    public function admin_notices() {
        foreach ($this->notices as $notice) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr($notice['type']),
                esc_html($notice['message'])
            );
        }
    }

    /**
     * Render the bookings calendar view
     */
    public function render_calendar(): void {
        wp_enqueue_script('tui-calendar');
        wp_enqueue_style('tui-calendar-css');

        $start_date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : date('Y-m-d');
        $bookings = $this->repository->get_by_date_range(
            $start_date,
            date('Y-m-d', strtotime($start_date . ' +7 days'))
        );

        include CHAUFFEUR_BOOKING_PATH . 'admin/views/bookings/calendar.php';
    }

    /**
     * Delete a booking
     *
     * @param int $id Booking ID to delete
     * @return bool True on success
     */
    public function delete(int $id): bool {
        check_admin_referer('delete_booking_' . $id);
        
        try {
            $result = $this->repository->delete($id);
            if ($result) {
                $this->add_notice(__('Booking deleted successfully!', 'chauffeur-booking'));
                return true;
            }
            throw new RuntimeException(__('Failed to delete booking', 'chauffeur-booking'));
        } catch (Exception $e) {
            $this->add_notice($e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Export bookings to CSV
     *
     * @param array $ids Booking IDs to export
     */
    public function export_bookings(array $ids): void {
        check_admin_referer('bulk-export');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="bookings-export-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, [
            __('ID', 'chauffeur-booking'),
            __('Name', 'chauffeur-booking'),
            __('Pickup Date', 'chauffeur-booking'),
            __('Status', 'chauffeur-booking')
        ]);
        
        // Data rows
        foreach ($ids as $id) {
            $booking = $this->repository->find(absint($id));
            if ($booking) {
                fputcsv($output, [
                    $booking->id,
                    $booking->name,
                    $booking->pickup_date,
                    $booking->status
                ]);
            }
        }
        
        fclose($output);
        exit;
    }

    // Private helper methods
    private function render_edit_form(int $id): void {
        $booking = $this->repository->find($id);
        if (!$booking) {
            wp_die(__('Booking not found', 'chauffeur-booking'));
        }

        include CHAUFFEUR_BOOKING_PATH . 'admin/views/bookings/edit.php';
    }

    private function render_add_form(): void {
        include CHAUFFEUR_BOOKING_PATH . 'admin/views/bookings/add.php';
    }

    private function render_list_table(): void {
        $bookings_table = new Chauffeur_Bookings_List_Table($this->repository);
        $bookings_table->render();
    }

    private function sanitize_booking_data(array $data): array {
        return [
            'name' => sanitize_text_field($data['name'] ?? ''),
            'email' => sanitize_email($data['email'] ?? ''),
            'phone' => sanitize_text_field($data['phone'] ?? ''),
            'vehicle' => sanitize_text_field($data['vehicle'] ?? ''),
            'route_id' => absint($data['route_id'] ?? 0),
            'pickup_date' => sanitize_text_field($data['pickup_date'] ?? ''),
            'pickup_location' => sanitize_text_field($data['pickup_location'] ?? ''),
            'destination' => sanitize_text_field($data['destination'] ?? ''),
            'distance' => floatval($data['distance'] ?? 0),
            'price' => floatval($data['price'] ?? 0),
            'status' => $this->sanitize_status($data['status'] ?? 'pending')
        ];
    }

    private function sanitize_status(string $status): string {
        $valid_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        return in_array($status, $valid_statuses) ? $status : 'pending';
    }

    private function add_notice(string $message, string $type = 'success'): void {
        $this->notices[] = [
            'message' => $message,
            'type' => $type
        ];
        
        // Also store in transients for page reloads
        set_transient(
            'chauffeur_admin_notice_' . get_current_user_id(),
            ['message' => $message, 'type' => $type],
            45
        );
    }

    private function display_notices(): void {
        // Show immediate notices
        foreach ($this->notices as $notice) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr($notice['type']),
                esc_html($notice['message'])
            );
        }
        
        // Show persistent notices (from transients)
        if ($notice = get_transient('chauffeur_admin_notice_' . get_current_user_id())) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr($notice['type']),
                esc_html($notice['message'])
            );
            delete_transient('chauffeur_admin_notice_' . get_current_user_id());
        }
    }
}


