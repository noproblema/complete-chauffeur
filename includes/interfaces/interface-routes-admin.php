<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

interface Routes_Admin_Interface {
    /**
     * Render the main routes admin page
     */
    public function render_page(): void;
    
    /**
     * Handle route creation/editing form submission
     */
    public function handle_form_submission(): void;
    
    /**
     * Delete a route
     * 
     * @param int $id Route ID to delete
     * @return bool True on success
     */
    public function delete(int $id): bool;
    
    /**
     * Export routes data
     * 
     * @param array $ids Route IDs to export
     */
    public function export_routes(array $ids): void;
    
    /**
     * Get the current route being edited
     * 
     * @return object|null Route object or null if new
     */
    public function get_current_route(): ?object;
}


