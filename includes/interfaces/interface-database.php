<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

interface Database_Interface {
    public function get_table_name(): string;
    public function create_table(): void;
    public function find(int $id): ?object;
    public function get_all(array $args = []): array;
    public function save(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}


