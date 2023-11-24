<?php

namespace App\Controllers;

use Pimple\Container;
use Twig\Environment;

class AbstractController
{
    protected Container $container;

    protected Object $model;

    public function setContainer(Container $container) : void {
        $this->container = $container;
    }

    protected function db() : \PDO {
        return $this->container['db'];
    }

    protected function twig() : Environment {
        return $this->container['twig'];
    }

    protected function render(string $view, array $data = []) : void {
        echo $this->twig()->render($view, $data);
    }

    public function setModel($class)
    {
        $class_parts = explode("\\", $class);
        $class = end($class_parts);
        $parts = preg_split('/(?=[A-Z])/', $class);
        $model = "App\\Models\\".$parts[1];
        if(class_exists($model)){
            $this->model = new $model($this->container['db']);
        }
    }
}