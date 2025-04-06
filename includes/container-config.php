<?php
    
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
    
return function (Chauffeur_DIContainer $container) {
    // Database Tables
    $container->set('Chauffeur_Bookings_Database', function() {
        return new Chauffeur_Bookings_Database();
    });
    
    $container->set('Chauffeur_Routes_Database', function() {
        return new Chauffeur_Routes_Database();
    });
    
    $container->set('Chauffeur_Vehicles_Database', function() {
        return new Chauffeur_Vehicles_Database();
    });

    // Repositories
    $container->set('BookingRepositoryInterface', function($c) {
        return new Chauffeur_Booking_Repository($c->get('Chauffeur_Bookings_Database'));
    });
    
    $container->set('RouteRepositoryInterface', function($c) {
        return new Chauffeur_Route_Repository($c->get('Chauffeur_Routes_Database'));
    });
    
    $container->set('VehicleRepositoryInterface', function($c) {
        return new Chauffeur_Vehicle_Repository($c->get('Chauffeur_Vehicles_Database'));
    });

    // Services
    $container->set('BookingServiceInterface', function($c) {
        return new Chauffeur_Booking_Service(
            $c->get('BookingRepositoryInterface'),
            $c->get('RouteRepositoryInterface'),
            $c->get('VehicleRepositoryInterface')
        );
    });

    // Admin
    $container->set('BookingsAdminInterface', function($c) { 
        return new Chauffeur_Bookings_Admin(
            $c->get('BookingRepositoryInterface')
        );
    });
    
    // Form Handler
    $container->set('FormHandlerInterface', function($c) {
        return new Chauffeur_Form_Handler(
            $c->get('BookingServiceInterface'),
            $c->get('RouteRepositoryInterface'),
            $c->get('VehicleRepositoryInterface')
        );
    });

    // Admin Components
    $container->set('Chauffeur_Admin_Menu', function($c) { 
        return new Chauffeur_Admin_Menu(
            $c->get('BookingsAdminInterface'),
            $c->get('RoutesAdminInterface'),
            $c->get('VehiclesAdminInterface')
        );
    });

    // Database schema
    $container->set('Chauffeur_DB_Schema', function() {
        return new Chauffeur_DB_Schema();
    });
};
