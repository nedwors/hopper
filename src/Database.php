<?php

namespace Nedwors\Hopper;

class Database
{
    public $name;
    public $db_database;

    public function __construct($name, $db_database)
    {
        $this->name = $name;
        $this->db_database = $db_database;
    }
}
