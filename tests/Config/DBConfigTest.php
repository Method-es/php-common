<?php
namespace Method\Common\Test\Config;

use PHPUnit_Framework_TestCase;

use Method\Common\Config\DBConfig;

class DBConfigTest extends PHPUnit_Framework_TestCase
{
    /*
    need to test:
    loading and accessing a simple config
    " on a config with a different key
    loading and failing on a missing key
    loading and failing on a missing value
     */
    public function testSimpleLoad()
    {
        $example = new DBConfig();
        $example->SetConfigLocation('tests/data/DBConfigTest1.json');
        $example->LoadConfig();

        $this->assertEquals('host1',$example->GetHost());
        $this->assertEquals('user1',$example->GetUsername());
        $this->assertEquals('pass1',$example->GetPassword());
        $this->assertEquals('name1',$example->GetName());

    }

    public function testComplexLoad()
    {
        $example = new DBConfig();
        $example->SetConfigLocation('tests/data/DBConfigTest2.json');
        $example->SetJSONKey('test-key');
        $example->LoadConfig();

        $this->assertEquals('host2',$example->GetHost());
        $this->assertEquals('user2',$example->GetUsername());
        $this->assertEquals('pass2',$example->GetPassword());
        $this->assertEquals('name2',$example->GetName());
    }

    public function testInvalidJSONKey()
    {
        $this->setExpectedException('RuntimeException', 'Invalid JSON Key provided');
        $example = new DBConfig();
        $example->SetJSONKey(123);

    }

    public function testFailedLoad()
    {
        $this->setExpectedException('Exception', 'Unable to locate the database key in the provided config');
        $example = new DBConfig();
        $example->SetConfigLocation('tests/data/DBConfigTest1.json');
        $example->SetJSONKey('test-key');
        $example->LoadConfig();

    }

    public function testMissingKeyException()
    {
        $this->setExpectedException('Exception', 'Unable to locate the required database key \'host\' in the provided database config');
        $example = new DBConfig();
        $example->SetConfigLocation('tests/data/DBConfigTest3.json');
        $example->LoadConfig();
    }

}