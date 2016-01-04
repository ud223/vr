<?php
namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Token extends AbstractDocument{
    
    /** @ODM\String */
    protected $token;

    /** @ODM\Date */
    protected $expire_date;

    /** @ODM\String */
    protected $params;
    
    /** @ODM\Boolean */
    protected $active_bln;
    
    public function isActive(){
        return $this->active_bln;
    }
    
    public function getParams(){
        return \Zend_Json::decode($this->params);
    }
    
    public function isExpired(){
        $expired = false;
        
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        
        if($now > $this->expire_date){
            $expired = true;
        }
        
        return $expired;
    }
}

?>
