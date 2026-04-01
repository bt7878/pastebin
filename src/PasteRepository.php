<?php

declare(strict_types=1);

namespace App;

use PDO;

readonly class PasteRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function find(string $id): ?string
    {
        $stmt = $this->db->prepare('SELECT encrypted_text FROM pastes WHERE id = ?');
        $stmt->execute([$id]);

        $value = $stmt->fetchColumn();
        return $value === false ? null : $value;
    }

    public function store(string $encrypted_text): string
    {
        $stmt = $this->db->prepare('INSERT INTO pastes (encrypted_text) VALUES (?) RETURNING id');
        $stmt->execute([$encrypted_text]);

        return $stmt->fetchColumn();
    }
}
