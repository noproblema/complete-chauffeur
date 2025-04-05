<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class Chauffeur_DIContainer {
    private $definitions = [];
    private $instances = [];

    public function __construct(array $definitions = []) {
        $this->definitions = $definitions;
    }

    public function set(string $id, $concrete): void {
        $this->definitions[$id] = $concrete;
        unset($this->instances[$id]); // Remove cached instance if exists
    }

    public function get(string $id) {
        // Return cached instance if available
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // Create new instance
        $concrete = $this->definitions[$id] ?? $id;
        
        if (is_callable($concrete)) {
            $instance = $concrete($this);
        } elseif (is_string($concrete) && class_exists($concrete)) {
            $instance = $this->autowire($concrete);
        } else {
            $instance = $concrete;
        }

        // Cache the instance
        $this->instances[$id] = $instance;
        return $instance;
    }

    private function autowire(string $class) {
        $reflection = new ReflectionClass($class);
        
        // Check if class is instantiable
        if (!$reflection->isInstantiable()) {
            throw new RuntimeException("Class {$class} is not instantiable");
        }

        // Get constructor params
        $constructor = $reflection->getConstructor();
        if (is_null($constructor)) {
            return new $class();
        }

        // Resolve dependencies
        $parameters = $constructor->getParameters();
        $dependencies = array_map(
            function ($param) {
                return $this->get($this->getParameterClassName($param));
            },
            $parameters
        );

        return $reflection->newInstanceArgs($dependencies);
    }

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
