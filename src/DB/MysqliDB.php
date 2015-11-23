<?php
namespace Method\Common\DB;

use mysqli;
use Exception;

class MysqliDB
{
    protected $mysqli;
    public $QueryCount = 0;
    public $QueryLog = [];

    protected $_config;

    public function __construct(Config $config)
    {
        // if(empty($config->GetHost()) || empty($config->GetUsername()) || empty($config->GetPassword()) || empty($config->GetName())) {
        //     throw new Exception('Missing or empty database credentials');
        // }
        $this->_config = $config;

        $this->mysqli = new mysqli(
                            $this->_config->GetHost(), 
                            $this->_config->GetUsername(), 
                            $this->_config->GetPassword(), 
                            $this->_config->GetName() );
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