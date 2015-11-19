<?php
namespace Method\Common\Config;


use RuntimeException;

/*

*** Please note, this class is immutable once loaded; 
*** any attempt to load a new config afetr loading one, will be blocked

Usage:

$example = new JSONConfig();
$example->SetConfigLocation('config.json');
$example->LoadConfig();
$configData = $example->GetConfig();
// $nestedData = $example->GetConfig("keyname"); // not implemented

OR 

$example = new JSONConfig();
$example->LoadConfig('config.json');
$configData = $example->GetConfig();

OR 

$example = new JSONConfig('config.json');
$configData = $example->GetConfig();

 */


class JSONConfig
{

    protected $configLocation;

    protected $_config;

    public function __construct($location = "")
    {
        if(!empty($location)){
            $this->LoadConfig($location);
        }
    }

    public function SetConfigLocation($location)
    {
        $this->CheckConfigState();

        $this->configLocation = $location;
    }

    public function GetConfig()
    {
        if(empty($this->_config)){
            throw new RuntimeException('Unable to retrieve config');
        }
        return clone $this->_config;
    }

    protected function CheckConfigState()
    {
        if(!empty($this->_config)){
            throw new RuntimeException('Config has already been loaded; please use a new instance to load another config');
        }
    }

    public function LoadConfig($configLocation = '')
    {

        $this->CheckConfigState();

        if(!empty($configLocation)){
            $this->SetConfigLocation($configLocation);
        }

        if(empty($this->configLocation)){
            throw new RuntimeException("Config file location not provided");
        }

        if(!file_exists($this->configLocation)){
            throw new RuntimeException("Unable to locate config file");
        }

        $configData = @file_get_contents($this->configLocation);
        if($configData === false){
            throw new RuntimeException("Unable to open the provided config file: ".$this->configLocation);
        }

        $this->ParseConfig($configData);

    }

    protected function ParseConfig($configData)
    {
        $configData = preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#", '', $configData);
        $configData = json_decode($configData);
        if($configData === NULL){
            throw new RuntimeException("Unable to parse the provided config file");
        }
        $this->_config = $configData;
    }

}