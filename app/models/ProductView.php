<?php

class ProductView extends Model
{
    public function tableExists()
    {
        return (bool)$this->pdo->query("SHOW TABLES LIKE 'product_views'")->fetch();
    }
}
