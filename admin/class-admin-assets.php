<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Registers and enqueues admin assets for the Complete Chauffeur plugin.
 */
class Complete_Chauffeur_Admin_Assets {

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Register and enqueue admin assets on plugin admin pages.
     *
     * @param string $hook The current admin page hook.
     */
    public function enqueue_assets(string $hook): void {
        if (!$this->is_plugin_admin_page($hook)) {
            return;
        }

        // Third-party TUI Calendar library
        wp_register_style(
            'tui-calendar-css',
            COMPLETE_CHAUFFEUR_URL . 'assets/css/toastui-calendar.min.css',
            [],
            COMPLETE_CHAUFFEUR_VERSION
        );

        wp_register_script(
            'tui-calendar',
            COMPLETE_CHAUFFEUR_URL . 'assets/js/toastui-calendar.min.js',
            ['jquery'],
            COMPLETE_CHAUFFEUR_VERSION,
            true
        );

        // Plugin-specific admin styles
        wp_register_style(
            'admin-calendar-css',
            COMPLETE_CHAUFFEUR_URL . 'admin/assets/css/admin-calendar.css',
            [],
            COMPLETE_CHAUFFEUR_VERSION
        );

        wp_register_script(
            'admin-calendar',
            COMPLETE_CHAUFFEUR_URL . 'admin/assets/js/admin-calendar.js',
            ['jquery', 'tui-calendar'],
            COMPLETE_CHAUFFEUR_VERSION,
            true
        );

        wp_enqueue_style('tui-calendar-css');
        wp_enqueue_style('admin-calendar-css');
        wp_enqueue_script('tui-calendar');
        wp_enqueue_script('admin-calendar');
    }

    /**
     * Determine whether the current admin page belongs to this plugin.
     *
     * @param string $hook The current admin page hook.
     * @return bool
     */
    private function is_plugin_admin_page(string $hook): bool {
        return strpos($hook, 'complete-chauffeurs') !== false ||
               (isset($_GET['page']) && strpos($_GET['page'], 'complete-chauffeur') !== false);
    }
}
