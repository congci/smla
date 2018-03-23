<?php

model("DB");

class BaseModel{
    protected $db;

    public function __construct()
    {
        $this->db = new DB();
    }
    public function __call($name, $arguments)
    {
        $this->db->$name($arguments);
    }

}