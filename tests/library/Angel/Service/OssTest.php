<?php

require_once APPLICATION_PATH.'/../tests/AngelTestAbstract.php';

class Angel_Service_OssTest extends AngelTestAbstract{
    
    private $_ossService = null;
    
    public function setUp(){
        parent::setUp();
        
        $this->_ossService = $this->_container->get('oss');
    }
    
    /**
     * @group aliyun 
     */
    public function testAliyun(){
        $result = $this->_ossService->upload('angeltesting', 'test.jpg', 'test.jpg');
        $this->assertTrue($result);
    }
}
