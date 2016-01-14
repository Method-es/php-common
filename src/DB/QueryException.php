<?php
namespace Method\Common\DB;

use Exception;

class QueryException extends Exception
{
    public $Query = "";
    public function __construct($query, $message = "", $code = 666, $previous = NULL)
    {
        $this->Query = $query;
        $message = "DB Query Error: " . $message . "\n SQL: " . $query;
        parent::__construct($message,$code,$previous);
    }
}