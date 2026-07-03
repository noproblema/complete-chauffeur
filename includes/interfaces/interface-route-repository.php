<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
interface Route_Repository_Interface {
    public function find(int $id): ?object;
    public function get_all(): array;
    public function get_by_name(string $name): ?object;
    public function save(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}


