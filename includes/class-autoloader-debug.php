<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Complete_Chauffeur_Autoloader_Debug {
    const CACHE_KEY = 'complete_chauffeur_classmap';
    const CACHE_EXPIRE = WEEK_IN_SECONDS;
    const DEBUG_FILE = COMPLETE_CHAUFFEUR_PATH . 'autoloader-debug.log';
    
    /**
     * Register the autoloader with caching support
     */
    public static function register(): void {
        spl_autoload_register([__CLASS__, 'load_class']);
        self::log_debug("Autoloader registered");
    }

    /**
     * Load class using cached classmap or generate new one
     */
    public static function load_class(string $class): void {
        self::log_debug("Attempting to load class: " . $class);
        
        $classmap = self::get_classmap();
        
        if (isset($classmap[$class])) {
            self::log_debug("Found in classmap: " . $classmap[$class]);
            require_once $classmap[$class];
            return;
        }
        
        // Fallback to direct loading if not in cache
        $file = self::locate_class_file($class);
        if ($file) {
            self::log_debug("Found via fallback: " . $file);
            require_once $file;
            // Add to classmap for future requests
            $classmap[$class] = $file;
            set_transient(self::CACHE_KEY, $classmap, self::CACHE_EXPIRE);
        } else {
            self::log_debug("Class not found: " . $class);
        }
    }

    /**
     * Get cached classmap or generate new one with debug output
     */
    private static function get_classmap(): array {
        self::log_debug("Getting classmap...");
        
        // Try to get from cache first
        $cached = get_transient(self::CACHE_KEY);
        
        if ($cached !== false) {
            self::log_debug("Using cached classmap with " . count($cached) . " entries");
            return $cached;
        }
        
        // Generate fresh classmap
        self::log_debug("Generating new classmap...");
        $classmap = self::generate_classmap();
        self::log_debug("Generated classmap with " . count($classmap) . " entries");
        
        // Output full classmap to debug file
        self::log_debug("Classmap contents:\n" . print_r($classmap, true));
        
        return $classmap;
    }

    /**
     * Scan directories and generate classmap with debug output
     */
    private static function generate_classmap(): array {
        $classmap = [];

        // Define scanning directories (absolute paths; RecursiveIteratorIterator recurses into subdirectories)
        $directories = [
            COMPLETE_CHAUFFEUR_PATH . 'includes/',
            COMPLETE_CHAUFFEUR_PATH . 'admin/',
        ];

        self::log_debug("Starting directory scan");

        foreach ($directories as $path) {
            if (!is_dir($path)) {
                self::log_debug("Directory not found: " . $path);
                continue;
            }
            
            self::log_debug("Scanning directory: " . $path);
            
            try {
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
                
                foreach ($iterator as $file) {
                    if ($file->getExtension() !== 'php') continue;
                    
                    $filepath = $file->getPathname();
                    $classes = self::find_classes_in_file($filepath);
                    
                    foreach ($classes as $class) {
                        $classmap[$class] = $filepath;
                        self::log_debug("Found class {$class} in {$filepath}");
                    }
                }
            } catch (Exception $e) {
                self::log_debug("Error scanning directory {$path}: " . $e->getMessage());
            }
        }
        
        set_transient(self::CACHE_KEY, $classmap, self::CACHE_EXPIRE);
        return $classmap;
    }

    /**
     * Locate class file using naming conventions
     */
    private static function locate_class_file(string $class): ?string {
        // Convert class name to filename (strip plugin prefix, then underscores to dashes)
        $relative = preg_replace('/^Complete_Chauffeur_/', '', $class);
        $relative = str_replace('_', '-', $relative);
        $filename = 'class-' . strtolower($relative) . '.php';
        
        // Possible locations (matches your structure)
        $locations = [
            COMPLETE_CHAUFFEUR_PATH . 'includes/' . $filename,
            COMPLETE_CHAUFFEUR_PATH . 'includes/database/' . $filename,
            COMPLETE_CHAUFFEUR_PATH . 'includes/repositories/' . $filename,
            COMPLETE_CHAUFFEUR_PATH . 'includes/services/' . $filename,
            COMPLETE_CHAUFFEUR_PATH . 'includes/interfaces/' . $filename,
            COMPLETE_CHAUFFEUR_PATH . 'admin/' . $filename
        ];
        
        self::log_debug("Looking for class {$class} as {$filename}");
        self::log_debug("Checking locations:\n" . print_r($locations, true));
        
        foreach ($locations as $file) {
            if (file_exists($file)) {
                self::log_debug("Found at: {$file}");
                return $file;
            }
        }
        
        self::log_debug("Class file not found for: {$class}");
        return null;
    }
    
    /**
     * Extract class/interface names from a file
     */
    private static function find_classes_in_file(string $filepath): array {
        $contents = file_get_contents($filepath);
        $classes = [];
        
        // Using tokenizer approach
        $tokens = token_get_all($contents);
        $count = count($tokens);
        
        for ($i = 0; $i < $count; $i++) {
            if (!is_array($tokens[$i])) continue;
            
            if ($tokens[$i][0] === T_CLASS || $tokens[$i][0] === T_INTERFACE) {
                if (isset($tokens[$i+2][1])) {
                    $classes[] = $tokens[$i+2][1];
                }
            }
        }
        
        return $classes;
    }    

    /**
     * Rebuild the classmap cache
     */
    public static function rebuild_cache(): void {
        self::clear_cache();
        self::generate_classmap();
    }

    /**
     * Clear the autoloader cache
     */
    public static function clear_cache(): void {
        self::log_debug("Clearing cache...");
        delete_transient(self::CACHE_KEY);
    }
    
    /**
     * Log debug messages to file
     */
    private static function log_debug(string $message): void {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] {$message}\n";
        
        // Write to debug file
        file_put_contents(
            self::DEBUG_FILE, 
            $log_entry, 
            FILE_APPEND
        );
    }
    
    /**
     * Get the current cache contents
     */
    public static function get_cache_contents(): array {
        $cache = get_transient(self::CACHE_KEY);
        return $cache ?: [];
    }
    
    /**
     * Output cache to debug file
     */
    public static function dump_cache_to_file(): void {
        $cache = self::get_cache_contents();
        $output = "Autoloader Cache Dump\n";
        $output .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= "Cache Key: " . self::CACHE_KEY . "\n";
        $output .= "Entries: " . count($cache) . "\n\n";
        $output .= print_r($cache, true);
        
        file_put_contents(
            COMPLETE_CHAUFFEUR_PATH . 'autoloader-cache-dump.txt',
            $output
        );
    }
}




