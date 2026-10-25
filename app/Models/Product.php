<?php
class Product extends Model
{
    // Turns the search text into a LIKE pattern. A typed % or _ is searched as normal text
    private function like(string $q): string
    {
        return '%' . addcslashes($q, '%_\\') . '%';
    }

    // One page of products, with the category and supplier names joined in
    public function paginate(string $q, int $limit, int $offset): array
    {
        // $limit and $offset are integers made by us, so they are safe inside the query
        $sql = "SELECT p.*, c.name AS category, s.name AS supplier
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN suppliers s ON s.id = p.supplier_id
                WHERE p.name LIKE ? OR p.sku LIKE ?
                ORDER BY p.id DESC
                LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        $like = $this->like($q);
        $st = $this->db->prepare($sql);
        $st->execute([$like, $like]);   // two ? marks, so two values
        return $st->fetchAll();
    }

    public function count(string $q = ''): int
    {
        $like = $this->like($q);
        $st = $this->db->prepare("SELECT COUNT(*) FROM products WHERE name LIKE ? OR sku LIKE ?");
        $st->execute([$like, $like]);
        return (int)$st->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $st = $this->db->prepare("SELECT * FROM products WHERE id = ?");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    // Is this SKU taken by ANOTHER product?
    public function skuExists(string $sku, int $ignoreId = 0): bool
    {
        $st = $this->db->prepare("SELECT COUNT(*) FROM products WHERE sku = ? AND id != ?");
        $st->execute([$sku, $ignoreId]);
        return (bool)$st->fetchColumn();
    }

    public function create(array $d): int
    {
        $st = $this->db->prepare(
            "INSERT INTO products (category_id, supplier_id, name, sku, price, quantity, reorder_level, photo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $st->execute([
            $d['category_id'], $d['supplier_id'], $d['name'], $d['sku'],
            $d['price'], $d['quantity'], $d['reorder_level'], $d['photo'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): void
    {
        // COALESCE(?, photo): if no new photo is given (null), the old photo stays
        $st = $this->db->prepare(
            "UPDATE products
             SET category_id = ?, supplier_id = ?, name = ?, sku = ?, price = ?,
                 quantity = ?, reorder_level = ?, photo = COALESCE(?, photo)
             WHERE id = ?"
        );
        $st->execute([
            $d['category_id'], $d['supplier_id'], $d['name'], $d['sku'],
            $d['price'], $d['quantity'], $d['reorder_level'], $d['photo'] ?? null, $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    }

        // Numbers for the dashboard cards (one query for all of them)
    public function stats(): array
    {
        return $this->db->query(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(quantity <= reorder_level), 0) AS low,
                    COALESCE(SUM(quantity = 0), 0) AS out_of_stock,
                    COALESCE(SUM(price * quantity), 0) AS stock_value,
                    COALESCE(SUM(DATE(created_at) = CURDATE()), 0) AS added_today
             FROM products"
        )->fetch();
    }

    // The products that need restocking first (lowest quantity first)
    public function lowStock(int $limit = 5): array
    {
        $sql = "SELECT p.id, p.name, p.sku, p.quantity, p.reorder_level, c.name AS category
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                WHERE p.quantity <= p.reorder_level
                ORDER BY p.quantity ASC, p.name
                LIMIT " . (int)$limit;   // an integer made by us, so it is safe
        return $this->db->query($sql)->fetchAll();
    }

        // One product with its category and supplier names (used by the API)
    public function findDetailed(int $id): ?array
    {
        $st = $this->db->prepare(
            "SELECT p.*, c.name AS category, s.name AS supplier
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN suppliers s ON s.id = p.supplier_id
             WHERE p.id = ?"
        );
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }
}