<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
interface FormHandlerInterface {
    public function handle_submission(): void;
    public function handle_ajax_submission(): void;
    public function render_form(): string;
}
