<?php
class System {
    function currentTimeMills() {
        list($usec, $sec) = explode(" ", microtime());

        return $sec.substr($usec, 2, 3);
    }
}

class NetAddress {
    var $Name = 'localhost';
    var $IP = '127.0.0.1';
    
    function getLocalHost() {
        $address = new NetAddress();
        $address->Name = $_ENV["COMPUTERNAME"];
        $address->IP = $_SERVER["SERVER_ADDR"];
        
        return $address;
    }
    
    function toString() {
        return strtolower($this->Name.'/'. $this->IP);
    }
}

class Random {
    function nextLong() {
        $tmp = rand()?'-':'';
        
        
        return $tmp.rand(1000, 9999).rand(1000, 9999).rand(1000, 9999).rand(1000, 9999);
    }
}

class Angel_Model_Guid  extends Angel_Model_AbstractModel {
    var $valueBeforeMD5;
    var $valueAfterMD5;
    
    public function Guid() {
        $this->getGuid();
    }
    
    public function getGuid() {
        $address = NetAddress::getLocalHost();
        $this->valueBeforeMD5 = $address->toString().':'.System::currentTimeMills().':'.Random::nextLong();
        $this->valueAfterMD5 = md5($this->valueBeforeMD5);
    }
    
    public function newGuid() {
        $Guid = new Guid();
        
        return $Guid;
    }
    
    public function toString() {
        $address = NetAddress::getLocalHost(); 
        $this->valueBeforeMD5 = $address->toString().':'.System::currentTimeMills().':'.Random::nextLong();
        $this->valueAfterMD5 = md5($this->valueBeforeMD5);
        $raw = strtoupper($this->valueAfterMD5);
        
        return substr($raw, 0, 8).'-'.substr($raw, 8, 4).'-'.substr($raw, 16, 4).'-'.substr($raw, 20);
    }
}
