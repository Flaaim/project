<?php

namespace App\Models;

class Model
{
    protected $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function db()
    {
        return $this->db;
    }
}