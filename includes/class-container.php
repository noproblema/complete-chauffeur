<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Dependency Injection Container for managing service instantiation and dependencies
 */
class Complete_Chauffeur_Container {
    private $definitions = []; // Stores service definitions
    private $instances = [];   // Stores instantiated services for reuse
    
    public function __construct(array $definitions = []) {
        $this->definitions = $definitions;
    }

    /**
     * Register a service definition
     * @param string $id Service identifier
     * @param mixed $concrete Service definition (class name, object, or factory function)
     */
    public function set(string $id, $concrete): void {
        $this->definitions[$id] = $concrete;
        unset($this->instances[$id]); // Clear cached instance if it exists
    }

    /**
     * Get a service instance
     * @param string $id Service identifier
     * @return mixed The service instance
     */
    public function get(string $id) {
        // Return cached instance if available
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // Create new instance based on definition
        $concrete = $this->definitions[$id] ?? $id;
        
        if (is_callable($concrete)) {
            // If definition is a factory function, call it with container
            $instance = $concrete($this);
        } elseif (is_string($concrete) && class_exists($concrete)) {
            // If definition is a class name, autowire dependencies
            $instance = $this->autowire($concrete);
        } else {
            // Otherwise use the definition directly (for simple values)
            $instance = $concrete;
        }

        // Cache the instance for future use
        $this->instances[$id] = $instance;
        return $instance;
    }

    /**
     * Automatically resolve class dependencies through reflection
     * @param string $class Class name to instantiate
     * @return object The instantiated class
     * @throws RuntimeException If class cannot be instantiated
     */
    private function autowire(string $class) {
        $reflection = new ReflectionClass($class);
        
        // Check if class is instantiable
        if (!$reflection->isInstantiable()) {
            throw new RuntimeException("Class {$class} is not instantiable");
        }

        // Get constructor params
        $constructor = $reflection->getConstructor();
        if (is_null($constructor)) {
            // No constructor - simple instantiation
            return new $class();
        }

        // Resolve constructor dependencies recursively
        $parameters = $constructor->getParameters();
        $dependencies = array_map(
            function ($param) {
                return $this->get($this->getParameterClassName($param));
            },
            $parameters
        );

        // Create instance with resolved dependencies
        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Get the class name of a parameter's type hint
     * @param ReflectionParameter $param The parameter to inspect
     * @return string The type-hinted class name
     * @throws RuntimeException If parameter has no type hint
     */
    private function getParameterClassName(ReflectionParameter $param): string {
        $type = $param->getType();
        
        if (!$type || $type->isBuiltin()) {
            throw new RuntimeException(
                "Cannot resolve parameter {$param->name} in {$param->getDeclaringClass()->name}"
            );
        }

        return $type->getName();
    }
}




