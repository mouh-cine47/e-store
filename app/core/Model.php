<?php

abstract class Model
{
    protected $pdo;

    public function __construct($pdo = null)
    {
        $this->pdo = $pdo ?: Database::connection();
    }
}
