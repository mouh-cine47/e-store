<?php

class User extends Model
{
    public function findByEmail($email)
    {
        $stmt = $this->pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }
}
