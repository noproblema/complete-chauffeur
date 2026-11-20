# Complete Chauffeur Plugin - Migration Guide

## Overview
This plugin has been renamed from "Chauffeur Booking" to "Complete Chauffeur".

## Important Changes

### 1. Plugin Name Changed
- Old: Chauffeur Booking
- New: Complete Chauffeur

### 2. Text Domain Changed
- Old: chauffeur-booking
- New: complete-chauffeur

### 3. Class Prefix Changed
- Old: Chauffeur_
- New: Complete_Chauffeur_

### 4. Database Tables Renamed
- Old: chauffeur_bookings
- New: complete_chauffeur_bookings

- Old: chauffeur_routes
- New: complete_chauffeur_routes

- Old: chauffeur_vehicles
- New: complete_chauffeur_vehicles

### 5. Option Names Changed
All options now use the prefix "complete_chauffeur_" instead of "chauffeur_"

### 6. Capabilities Changed
- Old: manage_chauffeur_bookings
- New: manage_complete-chauffeur_bookings

- Old: manage_chauffeur_routes
- New: manage_complete-chauffeur_routes

- Old: manage_chauffeur_vehicles
- New: manage_complete-chauffeur_vehicles

### 7. Shortcode Changed
- Old: [chauffeur_booking]
- New: [complete-chauffeur_booking]

## Migration Steps

### For Developers

1. Update your code to use the new class names:
   Old: $booking = new Chauffeur_Booking_Service();
   New: $booking = new Complete_Chauffeur_Booking_Service();

2. Update capability checks:
   Old: current_user_can('manage_chauffeur_bookings')
   New: current_user_can('manage_complete-chauffeur_bookings')

3. Update shortcodes:
   Old: [chauffeur_booking]
   New: [complete-chauffeur_booking]

4. Update text domain in translation functions:
   Old: __('Hello World', 'chauffeur-booking')
   New: __('Hello World', 'complete-chauffeur')

5. Update database queries:
   Old: $wpdb->prefix . 'chauffeur_bookings'
   New: $wpdb->prefix . 'complete_chauffeur_bookings'

### For Site Administrators

1. Deactivate the old plugin "Chauffeur Booking"
2. Activate the new plugin "Complete Chauffeur"
3. Check your shortcodes and update them if needed
4. Review user capabilities if you've customized access

### Database Migration

The plugin includes an automatic migration script that will:
1. Detect existing tables
2. Rename them to the new naming convention
3. Update option names
4. Preserve all data

IMPORTANT: Back up your database before activating the new version!

## Rollback Plan

If you need to rollback:
1. Deactivate "Complete Chauffeur"
2. Reactivate "Chauffeur Booking" (previous version)
3. The data should remain intact in both versions

---
Migration Guide generated: 2026-07-04 14:09:11
