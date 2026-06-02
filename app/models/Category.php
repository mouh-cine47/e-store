<?php

class Category extends Model
{
    public function all()
    {
        return $this->pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
    }
}
