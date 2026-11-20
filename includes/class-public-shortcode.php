<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class Complete_Chauffeur_Public_Shortcode {
    private $form_handler;
    
    public function __construct(Form_Handler_Interface $form_handler) {
        $this->form_handler = $form_handler;
        add_shortcode('complete_chauffeur_booking', [$this, 'render_shortcode']);
    }

    public function render_shortcode() {
        return $this->form_handler->render_form();
    }
}





