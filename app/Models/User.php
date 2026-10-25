<?php
class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $st = $this->db->prepare("SELECT * FROM users WHERE email = ?");   // prepared: safe
        $st->execute([$email]);
        return $st->fetch() ?: null;
    }

    // Everyone who registers is a normal "user". Admins are made in the database only
    public function create(string $name, string $email, string $password, string $role = 'user'): void
    {
        $st = $this->db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $st->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
    }
}