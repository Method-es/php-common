<?php
namespace Method\Common\DB;

use mysqli;
use Exception;
use mysqli_result;

/**
 * Class MysqliDB
 * @package Method\Common\DB
 * @property mysqli $mysqli
 */
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

        $this->connect();
    }

    protected function connect()
    {
        $this->mysqli = new mysqli(
            $this->_config->GetHost(),
            $this->_config->GetUsername(),
            $this->_config->GetPassword(),
            $this->_config->GetName());
    }

    public function Ping()
    {
        return $this->mysqli->ping();
    }

    public function Reconnect($force = false)
    {
        if(!$force && $this->Ping())
            return; //already connected

        $this->connect();
    }

    public function Query($query)
    {
        $result = $this->mysqli->query($query);
        $this->QueryCount++;
        $this->QueryLog[] = $query;
        if ($result instanceof mysqli_result) {
            return new MysqliResult($result);
        }

        if ($result === true) {
            if ($this->LastInsertID() > 0) {
                return $this->LastInsertID();
            }
            if ($this->AffectedRows() > 0) {
                return $this->AffectedRows();
            }
            return true;
        }

        throw new QueryException($query, $this->GetLastError());
    }

    public function LastInsertID()
    {
        return $this->mysqli->insert_id;
    }

    public function AffectedRows()
    {
        return $this->mysqli->affected_rows;
    }

    public static function Rows($result)
    {
        $rows = [];
        while ($row = $result->fetch_assoc())
            $rows[] = $row;
        $result->free_result();
        return $rows;
    }

    public function GetLastError()
    {
        return $this->mysqli->error;
    }

    public function getMysqli()
    {
        return $this->mysqli;
    }
}