<?php
interface VehicleRepositoryInterface {
    public function find(int $id): ?object;
    public function get_by_class(string $class): ?object;
    public function get_all(): array;
    public function get_available(int $quantity = 1): array;
    public function save(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
