<?php
class Supplier extends Model
{
    public function all(): array
    {
        return $this->db->query("SELECT * FROM suppliers ORDER BY name")->fetchAll();
    }

    public function find(int $id): ?array
    {
        $st = $this->db->prepare("SELECT * FROM suppliers WHERE id = ?");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }
}