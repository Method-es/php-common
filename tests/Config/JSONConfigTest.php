<?php
namespace Method\Common\Test\Config;

use PHPUnit_Framework_TestCase;

use Method\Common\Config\JSONConfig;

class JSONConfigTest extends PHPUnit_Framework_TestCase
{
    public function testSimpleLoad()
    {
        $example = new JSONConfig();
        $example->SetConfigLocation('tests/data/JSONConfigTest1.json');
        $example->LoadConfig();
        $configData = $example->GetConfig();

        $this->assertInstanceOf('StdClass', $configData);
        $this->assertObjectHasAttribute('key1',$configData);
        $this->assertEquals('val1', $configData->key1);
        $this->assertObjectHasAttribute('key2',$configData);
        $this->assertEquals('val2', $configData->key2);

    }

    public function testComplexLoad()
    {
        $example = new JSONConfig();
        $example->SetConfigLocation('tests/data/JSONConfigTest2.json');
        $example->LoadConfig();
        $configData = $example->GetConfig();

        $this->assertInstanceOf('StdClass', $configData);
        $this->assertObjectHasAttribute('key1',$configData);
        $this->assertInstanceOf('StdClass', $configData->key1);
        $this->assertObjectHasAttribute('key2',$configData->key1);
        $this->assertEquals('val2', $configData->key1->key2);
        $this->assertObjectHasAttribute('key3',$configData->key1);
        $this->assertInstanceOf('StdClass', $configData->key1->key3);
        $this->assertObjectHasAttribute('key4',$configData->key1->key3);
        $this->assertEquals('val4', $configData->key1->key3->key4);
    }

    public function testNoConfigException()
    {
        $this->setExpectedException('RuntimeException', 'Unable to retrieve config');
        $example = new JSONConfig();
        $configData = $example->GetConfig();
    }

    public function testNoConfigLocationException()
    {
        $this->setExpectedException('RuntimeException', 'Config file location not provided');
        $example = new JSONConfig();
        $example->LoadConfig();
    }

    public function testInvalidConfigLocationException()
    {
        $this->setExpectedException('RuntimeException', 'Unable to locate config file');
        $example = new JSONConfig();
        $example->SetConfigLocation('tests/data/NonExistantFile.json');
        $example->LoadConfig();
    }

    public function testInvalidJSONConfigException()
    {
        $this->setExpectedException('RuntimeException', 'Unable to parse the provided config file');
        $example = new JSONConfig();
        $example->SetConfigLocation('tests/data/JSONConfigTest3.json');
        $example->LoadConfig();
    }

    public function testInvalidConfigStateException()
    {
        $this->setExpectedException('RuntimeException', 'Config has already been loaded; please use a new instance to load another config');
        $example = new JSONConfig();
        $example->SetConfigLocation('tests/data/JSONConfigTest1.json');
        $example->LoadConfig();
        $example->SetConfigLocation('tests/data/JSONConfigTest2.json');
    }

}