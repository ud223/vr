<?php

require_once APPLICATION_PATH.'/../tests/AngelTestAbstract.php';

class Angel_Service_UtilTest extends AngelTestAbstract{
    
    private $_utilService = null;
    
    public function setUp(){
        parent::setUp();
        
        $this->_utilService = $this->_container->get('util');
    }
    
    /**
     * @group localdate 
     */
    public function testLocalDate(){
        $date = $this->_utilService->localDate(new \DateTime('2013-05-03 07:10:30', new \DateTimeZone('UTC')));
        $this->assertEquals('2013-05-03 15:10:30', $date->format('Y-m-d H:i:s'));
    }
}
