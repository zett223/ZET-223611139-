<?php
namespace Src\Repositories;

use PDO;
use Src\Config\Database;

class UserRepository
{
    private PDO $db;

    public function __construct(array $cfg)
    {
        $this->db = Database::conn($cfg);
    }

    public function paginate(int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $total = (int)$this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();

        $stmt = $this->db->prepare('SELECT id, name, email, role, created_at, updated_at FROM users ORDER BY id DESC LIMIT :per OFFSET :off');
        $stmt->bindValue(':per', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => max(1, (int)ceil($total / max(1, $perPage))),
            ],
        ];
    }

    public function find(int $id): array|null
    {
        $stmt = $this->db->prepare('SELECT id, name, email, role, created_at, updated_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $result = $stmt->fetch();

        return $result !== false ? $result : null;
    }

    public function create(string $name, string $email, string $passwordHash, string $role = 'user'): array
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, $passwordHash, $role]);
            $id = (int)$this->db->lastInsertId();
            $this->db->commit();

            return $this->find($id) ?? [];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, string $name, string $email, string $role): array
    {
        $stmt = $this->db->prepare('UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?');
        $stmt->execute([$name, $email, $role, $id]);

        return $this->find($id) ?? [];
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        return (bool)$stmt->execute([$id]);
    }
}
