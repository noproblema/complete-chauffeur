<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

interface Vehicles_Admin_Interface {
    /**
     * Render the main vehicles admin page
     */
    public function render_page(): void;
    
    /**
     * Handle vehicle creation/editing form submission
     */
    public function handle_form_submission(): void;
    
    /**
     * Delete a vehicle
     * 
     * @param int $id Vehicle ID to delete
     * @return bool True on success
     */
    public function delete(int $id): bool;
    
    /**
     * Toggle vehicle availability status
     * 
     * @param int $id Vehicle ID
     * @param bool $available New availability status
     * @return bool True on success
     */
    public function set_availability(int $id, bool $available): bool;
    
    /**
     * Get the current vehicle being edited
     * 
     * @return object|null Vehicle object or null if new
     */
    public function get_current_vehicle(): ?object;
    
    /**
     * Export vehicles data
     * 
     * @param array $ids Vehicle IDs to export
     */
    public function export_vehicles(array $ids): void;
}


