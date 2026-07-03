<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Chauffeur_Autoloader {
    const CACHE_KEY = 'chauffeur_autoload_classmap';
    const CACHE_EXPIRE = WEEK_IN_SECONDS; // 1 week cache
    
    /**
     * Register the autoloader with caching support
     */
    public static function register(): void {
        spl_autoload_register([__CLASS__, 'load_class']);
    }

    /**
     * Load a class using cached classmap or generate on the fly
     */
    public static function load_class(string $class): void {
        static $classmap = null;
        
        // Initialize classmap if not loaded
        if ($classmap === null) {
            $classmap = self::get_cached_classmap();
        }
        
        // Try cached path first
        if (isset($classmap[$class])) {
            require_once $classmap[$class];
            return;
        }
        
        // Fallback to direct file lookup
        $file = self::locate_class_file($class);
        if ($file && file_exists($file)) {
            require_once $file;
            
            // Update classmap cache
            $classmap[$class] = $file;
            self::cache_classmap($classmap);
        }
    }

    /**
     * Get cached classmap or generate fresh
     */
    private static function get_cached_classmap(): array {
        $cached = get_transient(self::CACHE_KEY);
        
        if ($cached !== false) {
            return $cached;
        }
        
        return self::generate_classmap();
    }

    /**
     * Generate fresh classmap by scanning directories
     */
    private static function generate_classmap(): array {
        $classmap = [];
        $base_dir = CHAUFFEUR_BOOKING_PATH . 'includes/';
        
        // Directories to scan (matches your structure)
        $scan_dirs = [
            '',
            'database/',
            'repositories/',
            'services/',
            'interfaces/',
            'admin/'
        ];
        
        foreach ($scan_dirs as $dir) {
            $path = $base_dir . $dir;
            
            if (!is_dir($path)) {
                continue;
            }
            
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }
                
                $classes = self::extract_classes_from_file($file->getPathname());
                foreach ($classes as $class) {
                    $classmap[$class] = $file->getPathname();
                }
            }
        }
        
        self::cache_classmap($classmap);
        return $classmap;
    }

    /**
     * Extract class/interface names from a file
     */
    private static function extract_classes_from_file(string $filepath): array {
        $contents = file_get_contents($filepath);
        $classes = [];
        
        // Match both classes and interfaces
        if (preg_match_all('/\b(?:class|interface)\s+([a-zA-Z0-9_]+)/', $contents, $matches)) {
            $classes = $matches[1];
        }
        
        return $classes;
    }

    /**
     * Locate class file using naming conventions
     */
    private static function locate_class_file(string $class): ?string {
        // Convert class name to filename
        $filename = 'class-' . strtolower(str_replace('_', '-', $class)) . '.php';
        
        // Possible locations (matches your structure)
        $locations = [
            CHAUFFEUR_BOOKING_PATH . 'includes/' . $filename,
            CHAUFFEUR_BOOKING_PATH . 'includes/database/' . $filename,
            CHAUFFEUR_BOOKING_PATH . 'includes/repositories/' . $filename,
            CHAUFFEUR_BOOKING_PATH . 'includes/services/' . $filename,
            CHAUFFEUR_BOOKING_PATH . 'includes/interfaces/' . $filename,
            CHAUFFEUR_BOOKING_PATH . 'admin/' . $filename
        ];
        
        foreach ($locations as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }

    /**
     * Cache the classmap
     */
    private static function cache_classmap(array $classmap): void {
        set_transient(self::CACHE_KEY, $classmap, self::CACHE_EXPIRE);
    }

    /**
     * Clear the cached classmap
     */
    public static function clear_cache(): void {
        delete_transient(self::CACHE_KEY);
    }

    /**
     * Rebuild the classmap cache
     */
    public static function rebuild_cache(): void {
        self::clear_cache();
        self::generate_classmap();
    }
}


