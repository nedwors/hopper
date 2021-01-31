<?php

namespace Nedwors\Hopper;

class Database
{
    public $name;
    public $db_database;
    public $connection;

    public function __construct($name, $db_database, $connection)
    {
        $this->name = $name;
        $this->db_database = $db_database;
        $this->connection = $connection;
    }
}
