<?php

class PublicMenController extends PublicShopController
{
    public function index()
    {
        $_GET['section'] = 'men';
        parent::index();
    }
}
