<?php

class Product extends Model
{
    public function tableExists()
    {
        return (bool)$this->pdo->query("SHOW TABLES LIKE 'products'")->fetch();
    }
}
