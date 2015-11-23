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


}