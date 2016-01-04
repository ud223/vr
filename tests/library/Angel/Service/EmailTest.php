<?php

require_once APPLICATION_PATH.'/../tests/AngelTestAbstract.php';

class Angel_Service_EmailTest extends AngelTestAbstract{
    
    private $_emailService = null;
    
    public function setUp(){
        parent::setUp();
        
        $this->_emailService = $this->_container->get('email');
    }
    
    /**
     * @group sendemail 
     */
    public function testSendEmail(){
        $this->_emailService->sendEmail(\Angel_Model_Email::EMAIL_NEW_USER_EMAIL_VALIDATION, 'wuc@angelhere.com', 'Email Tester', array('username'=>'邮件测试', 'url'=>"http://test.com"));
    }
}
