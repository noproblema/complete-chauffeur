<?php
    
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
    
return function (Complete_Chauffeur_Container $container) {
    // Repositories
    $container->set('Booking_Repository_Interface', function($c) {
        return new Complete_Chauffeur_Booking_Repository($c->get('Complete_Chauffeur_Bookings_Database'));
    });
    
    $container->set('Route_Repository_Interface', function($c) {
        return new Complete_Chauffeur_Route_Repository($c->get('Complete_Chauffeur_Routes_Database'));
    });
    
    $container->set('Vehicle_Repository_Interface', function($c) {
        return new Complete_Chauffeur_Vehicle_Repository($c->get('Complete_Chauffeur_Vehicles_Database'));
    });

    // Services
    $container->set('Booking_Service_Interface', function($c) {
        return new Complete_Chauffeur_Booking_Service(
            $c->get('Booking_Repository_Interface'),
            $c->get('Route_Repository_Interface'),
            $c->get('Vehicle_Repository_Interface')
        );
    });

    // Admin
    $container->set('Bookings_Admin_Interface', function($c) { 
        return new Complete_Chauffeur_Bookings_Admin(
            $c->get('Booking_Repository_Interface')
        );
    });

    $container->set('Routes_Admin_Interface', function($c) {
        return new Complete_Chauffeur_Routes_Admin(
            $c->get('Route_Repository_Interface')
        );
    });

    $container->set('Vehicles_Admin_Interface', function($c) {
        return new Complete_Chauffeur_Vehicles_Admin(
            $c->get('Vehicle_Repository_Interface')
        );
    });    

    // Form Handler
    $container->set('Form_Handler_Interface', function($c) {
        return new Complete_Chauffeur_Form_Handler(
            $c->get('Booking_Service_Interface'),
            $c->get('Route_Repository_Interface'),
            $c->get('Vehicle_Repository_Interface')
        );
    });

    // Public Components
    $container->set('Complete_Chauffeur_Public_Shortcode', function($c) {
        return new Complete_Chauffeur_Public_Shortcode(
            $c->get('Form_Handler_Interface')
        );
    });

    // Admin Components
    $container->set('Complete_Chauffeur_Admin_Assets', function($c) {
        return new Complete_Chauffeur_Admin_Assets();
    });

    $container->set('Complete_Chauffeur_Admin_Menu', function($c) {
        return new Complete_Chauffeur_Admin_Menu(
            $c->get('Bookings_Admin_Interface'),
            $c->get('Routes_Admin_Interface'),
            $c->get('Vehicles_Admin_Interface')
        );
    });

    $container->set('Complete_Chauffeur_Bookings_List_Table', function($c) {
        return new Complete_Chauffeur_Bookings_List_Table($c->get('Booking_Repository_Interface'));
    });

    // Database schema
    $container->set('Complete_Chauffeur_Bookings_Database', function() {
        return new Complete_Chauffeur_Bookings_Database();
    });

    $container->set('Complete_Chauffeur_Routes_Database', function() {
        return new Complete_Chauffeur_Routes_Database();
    });

    $container->set('Complete_Chauffeur_Vehicles_Database', function() {
        return new Complete_Chauffeur_Vehicles_Database();
    });

    $container->set('Complete_Chauffeur_Db_Schema', function($c) {
        return new Complete_Chauffeur_Db_Schema(
            $c->get('Complete_Chauffeur_Bookings_Database'),
            $c->get('Complete_Chauffeur_Routes_Database'),
            $c->get('Complete_Chauffeur_Vehicles_Database')
        );
    });
};




