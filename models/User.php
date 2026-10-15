<?php
class User extends Model
{
    protected string $table = 'users';

    public function findByUsername(string $username): ?array
    {
        return $this->queryOne('SELECT * FROM users WHERE username = ? LIMIT 1', [$username]);
    }

    public function listAll(): array
    {
        return $this->query(
            'SELECT u.id, u.name, u.username, u.role, u.is_active, u.created_at,
                    (SELECT id FROM instructors WHERE user_id = u.id LIMIT 1) AS instructor_id
             FROM users u ORDER BY u.name');
    }
}
