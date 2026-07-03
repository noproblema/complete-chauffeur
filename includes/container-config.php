<?php
    
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
    
return function (Chauffeur_Container $container) {
    // Repositories
    $container->set('Booking_Repository_Interface', function($c) {
        return new Chauffeur_Booking_Repository($c->get('Chauffeur_Bookings_Database'));
    });
    
    $container->set('Route_Repository_Interface', function($c) {
        return new Chauffeur_Route_Repository($c->get('Chauffeur_Routes_Database'));
    });
    
    $container->set('Vehicle_Repository_Interface', function($c) {
        return new Chauffeur_Vehicle_Repository($c->get('Chauffeur_Vehicles_Database'));
    });

    // Services
    $container->set('Booking_Service_Interface', function($c) {
        return new Chauffeur_Booking_Service(
            $c->get('Booking_Repository_Interface'),
            $c->get('Route_Repository_Interface'),
            $c->get('Vehicle_Repository_Interface')
        );
    });

    // Admin
    $container->set('Bookings_Admin_Interface', function($c) { 
        return new Chauffeur_Bookings_Admin(
            $c->get('Booking_Repository_Interface')
        );
    });

    $container->set('Routes_Admin_Interface', function($c) {
        return new Chauffeur_Routes_Admin(
            $c->get('Route_Repository_Interface')
        );
    });

    $container->set('Vehicles_Admin_Interface', function($c) {
        return new Chauffeur_Vehicles_Admin(
            $c->get('Vehicle_Repository_Interface')
        );
    });    

    // Form Handler
    $container->set('Form_Handler_Interface', function($c) {
        return new Chauffeur_Form_Handler(
            $c->get('Booking_Service_Interface'),
            $c->get('Route_Repository_Interface'),
            $c->get('Vehicle_Repository_Interface')
        );
    });

    // Admin Components
    $container->set('Chauffeur_Admin_Menu', function($c) {
        return new Chauffeur_Admin_Menu(
            $c->get('Bookings_Admin_Interface'),
            $c->get('Routes_Admin_Interface'),
            $c->get('Vehicles_Admin_Interface')
        );
    });

    $container->set('Chauffeur_Bookings_List_Table', function($c) {
        return new Chauffeur_Bookings_List_Table($c->get('Booking_Repository_Interface'));
    });

    // Database schema
    $container->set('Chauffeur_Bookings_Database', function() {
        return new Chauffeur_Bookings_Database();
    });

    $container->set('Chauffeur_Routes_Database', function() {
        return new Chauffeur_Routes_Database();
    });

    $container->set('Chauffeur_Vehicles_Database', function() {
        return new Chauffeur_Vehicles_Database();
    });

    $container->set('Chauffeur_Db_Schema', function($c) {
        return new Chauffeur_Db_Schema(
            $c->get('Chauffeur_Bookings_Database'),
            $c->get('Chauffeur_Routes_Database'),
            $c->get('Chauffeur_Vehicles_Database')
        );
    });
};


