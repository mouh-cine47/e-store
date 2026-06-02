<?php

class Order extends Model
{
    public function tableExists()
    {
        return (bool)$this->pdo->query("SHOW TABLES LIKE 'orders'")->fetch();
    }
}
