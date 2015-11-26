<?php
namespace Method\Common\Remote;

use phpseclib\Net;
use phpseclib\Crypt;

use Exception;
use RuntimeException;

class Server 
{
    protected $Host;
    protected $Username;
    protected $Password;
    protected $PEMKeyLocation;
    protected $PEMKeyData;

    //handlers
    protected $RSA;
    protected $SSH;
    protected $SCP;


    public function __construct()
    {

    }

    public function SetConfigurationData($config)
    {
        $tmpConfig = (object)$config;
        //it needs to have a host/username keys; 
        //it needs to have one of either password or pemkey

    }

    public function SetHost($host)
    {
        $this->Host = $host;
    }

    public function SetUsername($username)
    {
        $this->Username = $username;
    }

    public function SetPassword($password)
    {
        $this->Password = $password;
    }

    public function LoadPEMKey($pemKeyLocation)
    {
        $this->PEMKeyLocation = $pemKeyLocation;
        if(!file_exists($this->PEMKeyLocation)){
            throw new RuntimeException('Failed to find PEM key');
        }
        $this->PEMKeyData = @file_get_contents($this->PEMKeyLocation);
        if($this->PEMKeyData === false){
            throw new RuntimeException('Failed to load PEM key');
        }
    }

    public function CreateRSA()
    {
        if(empty($this->PEMKeyData)){
            throw new RuntimeException('Unable to create RSA token without a valid pem key loaded');
        }
        $this->RSA = new Crypt\RSA();
        $this->RSA->loadKey($this->PEMKeyData);
    }

    public function CreateSSH()
    {
        if(empty($this->Host)){
            throw new RuntimeException('Unable to create ssh object without host');
        }
        $this->SSH = new Net\SSH2($this->Host);
    }

    public function CreateSCP()
    {
        if(empty($this->SSH)){
            throw new RuntimeException('Unable to create scp object without an active SSH connection');
        }
        $this->SCP = new Net\SCP($this->SSH);
    }

    public function Connect()
    {
        //perform actual login
        if($this->Password === NULL && empty($this->RSA)){
            throw new RuntimeException('Unable to connect to ssh without some form of credentials');
        }

        if(empty($this->SSH)){
            $this->CreateSSH();
        }

        $loggedin = false;
        if(!empty($this->RSA)){
            $loggedin = $this->SSH->login($this->Username,$this->RSA);
        }else{
            $loggedin = $this->SSH->login($this->Username,$this->Password);
        }
        if(!$loggedin){
            throw new RuntimeException('Invalid login credentials');
        }
    }

    public function Run($command)
    {
        $result = $this->SSH->exec($command);

        if($this->SSH->getExitStatus() !== 0){
            $result = $this->SSH->getStdError() ?: $result;
            throw new RuntimeException($result);
        }
        return $result;
    }

    public function Close()
    {
        if(empty($this->SSH)){
            throw new RuntimeException('Unable to close a connection that was not started');
        }
        $this->SSH->disconnect();
    }

    public function Upload($target,$destination)
    {
        return $this->SCP->put($destination,$target, Net\SCP::SOURCE_LOCAL_FILE);
    }

    public function Download($target,$destination = false)
    {
        return $this->SCP->get($target,$destination);
    }

    public function CommandExists($command)
    {
        $result = $this->SSH->exec("hash $command 2>/dev/null");
        return ($this->SSH->getExitStatus() == 0);
    }

    // this is not really a good location for this cause it would imply expanding the tilde on the remote server; which it doesn't do
    // public static function ExpandTilde($path)
    // {
    //     if (function_exists('posix_getuid') && strpos($path, '~') !== false) {
    //         $info = posix_getpwuid(posix_getuid());
    //         $path = str_replace('~', $info['dir'], $path);
    //     }
    //     return $path;
    // }

}