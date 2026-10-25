<?php
class Category extends Model
{
    // All categories, with how many products each one has
    public function all(): array
    {
        return $this->db->query(
            "SELECT c.*, COUNT(p.id) AS products
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.id
             GROUP BY c.id
             ORDER BY c.name"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $st = $this->db->prepare("SELECT * FROM categories WHERE id = ?");   // prepared: safe
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    // Is this name already used? (ignoreId lets a category keep its own name when editing)
    public function nameExists(string $name, int $ignoreId = 0): bool
    {
        $st = $this->db->prepare("SELECT COUNT(*) FROM categories WHERE name = ? AND id != ?");
        $st->execute([$name, $ignoreId]);
        return (bool)$st->fetchColumn();
    }

    public function create(string $name): void
    {
        $this->db->prepare("INSERT INTO categories (name) VALUES (?)")->execute([$name]);
    }

    public function update(int $id, string $name): void
    {
        $this->db->prepare("UPDATE categories SET name = ? WHERE id = ?")->execute([$name, $id]);
    }

    // The foreign key (ON DELETE SET NULL) makes its products "uncategorized" automatically
    public function delete(int $id): void
    {
        $this->db->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    }
}