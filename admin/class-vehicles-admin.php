<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Chauffeur_Vehicles_Admin implements Vehicles_Admin_Interface {
    private $repository;
    private $notices = [];

    public function __construct(Vehicle_Repository_Interface $repository) {
        $this->repository = $repository;
        add_action('admin_init', [$this, 'handle_form_submission']);
    }

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

    public function handle_form_submission(): void {
        if (!isset($_POST['vehicle_nonce'])) return;
        
        check_admin_referer('vehicle_action', 'vehicle_nonce');

        try {
            $data = $this->sanitize_vehicle_data($_POST);
            
            if (!empty($_POST['vehicle_id'])) {
                $this->repository->update(absint($_POST['vehicle_id']), $data);
                $this->add_notice(__('Vehicle updated successfully!', 'chauffeur-booking'));
            } else {
                $this->repository->save($data);
                $this->add_notice(__('Vehicle created successfully!', 'chauffeur-booking'));
            }
        } catch (Exception $e) {
            $this->add_notice($e->getMessage(), 'error');
        }
    }

    public function delete(int $id): bool {
        check_admin_referer('delete_vehicle_' . $id);
        
        try {
            $result = $this->repository->delete($id);
            if ($result) {
                $this->add_notice(__('Vehicle deleted successfully!', 'chauffeur-booking'));
                return true;
            }
            throw new RuntimeException(__('Failed to delete vehicle', 'chauffeur-booking'));
        } catch (Exception $e) {
            $this->add_notice($e->getMessage(), 'error');
            return false;
        }
    }

    public function set_availability(int $id, bool $available): bool {
        check_admin_referer('toggle_availability_' . $id);
        
        try {
            $result = $this->repository->update($id, ['is_available' => $available]);
            if ($result) {
                $status = $available ? __('available', 'chauffeur-booking') : __('unavailable', 'chauffeur-booking');
                $this->add_notice(sprintf(__('Vehicle marked as %s', 'chauffeur-booking'), $status));
                return true;
            }
            throw new RuntimeException(__('Failed to update vehicle availability', 'chauffeur-booking'));
        } catch (Exception $e) {
            $this->add_notice($e->getMessage(), 'error');
            return false;
        }
    }

    public function get_current_vehicle(): ?object {
        $id = $_GET['id'] ?? 0;
        return $id ? $this->repository->find(absint($id)) : null;
    }

    public function export_vehicles(array $ids): void {
        check_admin_referer('bulk-export');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="vehicles-export-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, [
            __('ID', 'chauffeur-booking'),
            __('Class', 'chauffeur-booking'),
            __('Name', 'chauffeur-booking'),
            __('Capacity', 'chauffeur-booking'),
            __('Price Rate', 'chauffeur-booking')
        ]);
        
        foreach ($ids as $id) {
            $vehicle = $this->repository->find(absint($id));
            if ($vehicle) {
                fputcsv($output, [
                    $vehicle->id,
                    $vehicle->class,
                    $vehicle->capacity,
                    $vehicle->price_rate
                ]);
            }
        }
        
        fclose($output);
        exit;
    }

    private function render_edit_form(int $id): void {
        $vehicle = $this->repository->find($id);
        if (!$vehicle) {
            wp_die(__('Vehicle not found', 'chauffeur-booking'));
        }

        include CHAUFFEUR_BOOKING_PATH . 'admin/views/vehicles/edit.php';
    }

    private function render_add_form(): void {
        include CHAUFFEUR_BOOKING_PATH . 'admin/views/vehicles/add.php';
    }

    private function render_list_table(): void {
        $vehicles_table = new Chauffeur_Vehicles_List_Table($this->repository);
        $vehicles_table->render();
    }

    private function sanitize_vehicle_data(array $data): array {
        return [
            'class' => sanitize_text_field($data['class'] ?? ''),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'capacity' => absint($data['capacity'] ?? 0),
            'price_rate' => floatval($data['price_rate'] ?? 0),
            'image_url' => esc_url_raw($data['image_url'] ?? ''),
            'is_available' => isset($data['is_available']) ? 1 : 0
        ];
    }

    private function add_notice(string $message, string $type = 'success'): void {
        $this->notices[] = [
            'message' => $message,
            'type' => $type
        ];
        
        set_transient(
            'chauffeur_vehicles_admin_notice_' . get_current_user_id(),
            ['message' => $message, 'type' => $type],
            45
        );
    }

    private function display_notices(): void {
        foreach ($this->notices as $notice) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr($notice['type']),
                esc_html($notice['message'])
            );
        }
        
        if ($notice = get_transient('chauffeur_vehicles_admin_notice_' . get_current_user_id())) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr($notice['type']),
                esc_html($notice['message'])
            );
            delete_transient('chauffeur_vehicles_admin_notice_' . get_current_user_id());
        }
    }
}


