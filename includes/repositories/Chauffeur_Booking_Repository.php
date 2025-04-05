<?php
class Chauffeur_Booking_Repository implements BookingRepositoryInterface {
    private $database;

    public function __construct(Chauffeur_Bookings_Database $database) {
        $this->database = $database;
    }

    // Implement all interface methods...
    public function find(int $id): ?object {
        return $this->database->find($id);
    }
    
    // ... other methods ...
}
