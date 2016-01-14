<?php
namespace Method\Common\Test\DB;

use PHPUnit_Framework_TestCase;

use Method\Common\DB\MysqliDB;

use Method\Common\Config\DBConfig;

class MysqliDBTest extends PHPUnit_Framework_TestCase
{

    public function testDBConfig()
    {
        $dbConfig = new DBConfig('tests/data/MysqliDBTest1.json');

    }

    public function testDBConnect()
    {
        $dbConfig = new DBConfig('tests/data/MysqliDBTest1.json');

        $example = new MysqliDB($dbConfig);
    }

    public function testDBQuery()
    {
        $dbConfig = new DBConfig('tests/data/MysqliDBTest1.json');

        $example = new MysqliDB($dbConfig);

        $query = "SELECT * FROM `test`";

        $result = $example->Query($query);

        $data = $result->fetchAll();

    }

    public function testDBQueryFailure()
    {
        $this->setExpectedException('Exception');
        $dbConfig = new DBConfig('tests/data/MysqliDBTest1.json');

        $example = new MysqliDB($dbConfig);

        $query = "SELECT * FROM `not_a_real_table`";

        $result = $example->Query($query);

    }

}