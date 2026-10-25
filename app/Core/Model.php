<?php
// Every model extends this, so it gets $this->db for free
abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }
}