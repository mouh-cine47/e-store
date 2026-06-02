<?php

class Controller
{
    protected function render($view, array $data = [])
    {
        unset($data['this']);
        extract($data, EXTR_SKIP);
        require project_path('app/views/' . $view . '.php');
    }
}
