<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Complete_Chauffeur_Routes_Admin implements Routes_Admin_Interface {
    private $repository;
    private $notices = [];

    public function __construct(Route_Repository_Interface $repository) {
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
        if (!isset($_POST['complete_chauffeur_route_nonce'])) return;
        
        check_admin_referer('route_action', 'complete_chauffeur_route_nonce');

        try {
            $data = $this->sanitize_route_data($_POST);
            
            if (!empty($_POST['route_id'])) {
                $this->repository->update(absint($_POST['route_id']), $data);
                $this->add_notice(__('Route updated successfully!', 'complete-chauffeur'));
            } else {
                $this->repository->save($data);
                $this->add_notice(__('Route created successfully!', 'complete-chauffeur'));
            }
        } catch (Exception $e) {
            $this->add_notice($e->getMessage(), 'error');
        }
    }

    public function delete(int $id): bool {
        check_admin_referer('delete_route_' . $id);
        
        try {
            $result = $this->repository->delete($id);
            if ($result) {
                $this->add_notice(__('Route deleted successfully!', 'complete-chauffeur'));
                return true;
            }
            throw new RuntimeException(__('Failed to delete route', 'complete-chauffeur'));
        } catch (Exception $e) {
            $this->add_notice($e->getMessage(), 'error');
            return false;
        }
    }

    public function export_routes(array $ids): void {
        check_admin_referer('bulk-export');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="routes-export-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, [
            __('ID', 'complete-chauffeur'),
            __('Route Name', 'complete-chauffeur'),
            __('Start Location', 'complete-chauffeur'),
            __('End Location', 'complete-chauffeur'),
            __('Distance', 'complete-chauffeur')
        ]);
        
        foreach ($ids as $id) {
            $route = $this->repository->find(absint($id));
            if ($route) {
                fputcsv($output, [
                    $route->id,
                    $route->route_name,
                    $route->start_location,
                    $route->end_location,
                    $route->distance
                ]);
            }
        }
        
        fclose($output);
        exit;
    }

    public function get_current_route(): ?object {
        $id = $_GET['id'] ?? 0;
        return $id ? $this->repository->find(absint($id)) : null;
    }

    private function render_edit_form(int $id): void {
        $route = $this->repository->find($id);
        if (!$route) {
            wp_die(__('Route not found', 'complete-chauffeur'));
        }

        include COMPLETE_CHAUFFEUR_PATH . 'admin/views/routes/edit.php';
    }

    private function render_add_form(): void {
        include COMPLETE_CHAUFFEUR_PATH . 'admin/views/routes/add.php';
    }

    private function render_list_table(): void {
        $routes_table = new Complete_Chauffeur_Routes_List_Table($this->repository);
        $routes_table->render();
    }

    private function sanitize_route_data(array $data): array {
        return [
            'route_name' => sanitize_text_field($data['route_name'] ?? ''),
            'start_location' => sanitize_text_field($data['start_location'] ?? ''),
            'end_location' => sanitize_text_field($data['end_location'] ?? ''),
            'distance' => floatval($data['distance'] ?? 0),
            'price' => floatval($data['price'] ?? 0),
            'roundtrip_price' => floatval($data['roundtrip_price'] ?? 0),
            'is_active' => isset($data['is_active']) ? 1 : 0
        ];
    }

    private function add_notice(string $message, string $type = 'success'): void {
        $this->notices[] = [
            'message' => $message,
            'type' => $type
        ];
        
        set_transient(
            'complete_chauffeur_routes_admin_notice_' . get_current_user_id(),
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
        
        if ($notice = get_transient('complete_chauffeur_routes_admin_notice_' . get_current_user_id())) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr($notice['type']),
                esc_html($notice['message'])
            );
            delete_transient('complete_chauffeur_routes_admin_notice_' . get_current_user_id());
        }
    }
}




