<?php
namespace Method\Common\DB;

use mysqli;

use Method\Common\Config\DBConfig;

class MysqlDB
{
    protected $mysqli;
    public $QueryCount = 0;
    public $QueryLog = [];

    protected $_config;

    public function __construct(DBConfig $config)
    {
        if(empty(DBConfig::$Host) || empty(DBConfig::$Username) || empty(DBConfig::$DBName)) {
            throw new \Exception('Missing or empty database credentials');
        }

        $this->mysqli = new mysqli(DBConfig::$Host, DBConfig::$Username, DBConfig::$Password, DBConfig::$DBName);
    }

    public function Query($query)
    {
        $result = $this->mysqli->query($query);
        $this->QueryCount++;
        $this->QueryLog[] = $query;
        if($result instanceof mysqli_result)
            return $result->fetch_object();
        return $result;
    }

    public function LastInsertID() {
        return $this->mysqli->insert_id;
    }

    public static function Rows($result)
    {
        $rows = [];
        while($row = $result->fetch_assoc())
            $rows[] = $row;
        $result->free_result();
        return $rows;
    }

    public function GetLastError()
    {
        return $this->mysqli->error;
    }
}