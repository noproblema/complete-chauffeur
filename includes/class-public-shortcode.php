<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class Chauffeur_Public_Shortcode {
    private $form_handler;
    
    public function __construct(Chauffeur_Form_Handler $form_handler) {
        $this->form_handler = $form_handler;
        add_shortcode('chauffeur_booking', [$this, 'render_shortcode']);
    }

    public function render_shortcode() {
        return $this->form_handler->render_form();
    }
}

