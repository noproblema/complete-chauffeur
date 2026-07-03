<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Defines the public interface for the form handler
 * 
 * @package ChauffeurBooking
 */
interface Form_Handler_Interface {
    /**
     * Renders the booking form
     * 
     * @return string HTML output of the form
     */
    public function render_form(): string;

    /**
     * Displays any stored notices
     */
    public function display_notices(): void;

    /**
     * Gets a field value from submitted data
     * 
     * @param string $field Field name
     * @param mixed $default Default value
     * @return string Field value
     */
    public function get_field_value(string $field, $default = ''): string;

    /**
     * Handles AJAX form submissions
     */
    public function handle_ajax_submission(): void;

    /**
     * Enqueues necessary frontend assets
     */
    public function enqueue_assets(): void;
}


