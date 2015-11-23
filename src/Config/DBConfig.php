<?php
namespace Method\Common\Config;


use Method\Common\DB;
use Exception;
use RuntimeException;
/**
 * MysqlDB class eats this for breakfast.
 *
 * Class DBConfig
 * @package Method\Common\Config
 */
class DBConfig extends JSONConfig implements DB\Config
{
    // protected $Host;
    // protected $Username;
    // protected $Password;
    // protected $Name;

    protected $JSONKey;

    public function __construct($location = "", $jsonKey = "database")
    {
        parent::__construct($location);
        $this->JSONKey = $jsonKey;
    }

    public function SetJSONKey($key)
    {
        $this->CheckConfigState();
        if(empty($key) || !is_string($key)){
            throw new RuntimeException('Invalid JSON Key provided');
        }
        $this->JSONKey = $key;
    }

    protected function ParseConfig($configData)
    {
        parent::ParseConfig($configData);

        //perform basic validation to ensure the config actually has db credentials
        //this can't be loaded twice so we need to ensure its done right
        if(!property_exists($this->_config, $this->JSONKey)){
            throw new Exception('Unable to locate the database key in the provided config');
        }

        $dbConfig = $this->_config->{$this->JSONKey};

        $requiredKeys = ['host','username','password','name'];

        foreach($requiredKeys as $key){
            if(!property_exists($dbConfig, $key)){
                throw new Exception("Unable to locate the required database key '{$key}' in the provided database config");
            }
        }

    }

    public function GetHost()
    {
        return $this->_config->{$this->JSONKey}->host;
    }
    public function GetUsername()
    {
        return $this->_config->{$this->JSONKey}->username;
    }
    public function GetPassword()
    {
        return $this->_config->{$this->JSONKey}->password;
    }
    public function GetName()
    {
        return $this->_config->{$this->JSONKey}->name;
    }
}