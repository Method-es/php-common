<?php

namespace Method\Common\DB;

use mysqli_result;
use Exception;
use Iterator;

class MysqliResult implements Iterator
{
    const RESULT_TYPE_OBJECT = "object";
    const RESULT_TYPE_ARRAY = "array";

    const UNSUPPORTED_RESULT_TYPE = "Unsupported Result Type";

    protected $result;
    protected $numRows;

    protected $cursorPosition = 0;
    protected $currentRow;

    protected $resultType = self::RESULT_TYPE_OBJECT;

    protected $freed = false;

    public function __construct(mysqli_result $result)
    {
        $this->result = $result;
        $this->numRows = $this->result->num_rows;
    }

    public function __destruct()
    {
        $this->free();
    }

    public function numRows():int
    {
        return (int)$this->numRows;
    }

    public function fetch($className = null, $params = null)
    {
        if($this->resultType == self::RESULT_TYPE_OBJECT){
            return $this->fetchObject($className, $params);
        }else if($this->resultType == self::RESULT_TYPE_ARRAY){
            return $this->fetchArray();
        }
        throw new Exception(self::UNSUPPORTED_RESULT_TYPE);
    }

    public function fetchArray()
    {
        return $this->result->fetch_assoc();
    }

    public function fetchObject($className = null, $params = null)
    {
        if($className === null){
            return $this->result->fetch_object();
        }else if($params === null) {
            return $this->result->fetch_object($className);
        }else{
            return $this->result->fetch_object($className, $params);
        }

    }

    public function fetchAll($classname = null, $params = null)
    {
        if($this->resultType == self::RESULT_TYPE_OBJECT){
            return $this->fetchAllObject($classname, $params);
        }else if($this->resultType == self::RESULT_TYPE_ARRAY){
            return $this->fetchAllArray();
        }
        throw new Exception(self::UNSUPPORTED_RESULT_TYPE);
    }

    public function fetchAllObject($classname = null, $params = null)
    {
        $this->rewind();
        $this->result->data_seek($this->cursorPosition);

        $data = [];

        while($row = $this->fetchObject($classname, $params)){
            $data[] = $row;
        }

        return $data;

    }

    public function fetchAllArray()
    {
        $this->rewind();
        $this->result->data_seek($this->cursorPosition);

        $data = [];

        while($row = $this->fetchArray()){
            $data[] = $row;
        }

        return $data;
    }

    public function current()
    {
        $this->result->data_seek($this->cursorPosition);
        return $this->fetch();
    }

    public function next()
    {
        ++$this->cursorPosition;
    }

    public function key()
    {
        return $this->cursorPosition;
    }

    public function valid()
    {
        return ($this->cursorPosition < $this->numRows);
    }

    public function rewind()
    {
        $this->cursorPosition = 0;
    }

    public function free()
    {
        if(!$this->freed) {
            $this->result->free();
        }
        $this->freed = true;
    }

    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param string $resultType
     */
    public function setResultType($resultType)
    {
        $this->resultType = $resultType;
    }


}
