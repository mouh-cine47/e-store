<?php

class PublicWomenController extends PublicShopController
{
    public function index()
    {
        $_GET['section'] = 'women';
        parent::index();
    }
}
