<?php

require_once APPLICATION_PATH.'/../tests/AngelTestAbstract.php';

class Angel_Model_UserTest extends AngelTestAbstract{
    
    protected $_instance;
    
    public function setUp(){
        parent::setUp();
        
        $this->_instance = new Angel_Model_User($this->_bootstrap);
        
        // clear the user collection
        $this->_documentManager->createQueryBuilder($this->_instance->getDocumentClass())
                               ->remove()
                               ->getQuery()
                               ->execute();
    }
    
    /**
     * @group adduser 
     */
    public function testAddUser(){
        try{
            $this->_instance->addUser('', '', '', '', '');
        }
        catch(Angel_Exception_User $e){
            $this->assertEquals(Angel_Exception_User::EMAIL_EMPTY, $e->getMessageCode());
        }
        
        try{
            $this->_instance->addUser('', 'email@invalid', '', '', '');
        }
        catch(Angel_Exception_User $e){
            $this->assertEquals(Angel_Exception_User::EMAIL_INVALID, $e->getMessageCode());
        }
        
        try{
            $this->_instance->addUser('sdf', 'wu@test.com', 'sdf', 'password', 'salt');
        }
        catch(Angel_Exception_User $e){
            $this->assertEquals(Angel_Exception_User::USERTYPE_INVALID, $e->getMessageCode());
        }
                                        
        $id = $this->_instance->addUser('investor', 'wuc@angelhere.com', 'wu', 'password', 'salt');
        $this->assertEquals(24, strlen($id));
    }
    
    /**
     * @group emailexist 
     */
    public function testIsEmailExist(){
        $this->assertFalse($this->_instance->isEmailExist('notexist@email.com'));
        
        $id = $this->_instance->addUser('investor', 'notexist@email.com', 'testing', 'password', 'salt');
        $this->assertEquals(24, strlen($id));
        
        $this->assertTrue($this->_instance->isEmailExist('notexist@email.com'));
    }
    
    /**
     * @group resetpwd
     */
    public function testResetPassword(){
        $id = $this->_instance->addUser('investor', 'notexist@email.com', 'testing', 'password', 'salt', false);
        try{
            $result = $this->_instance->resetPassword($id, 'sdf', 'newpassword');
        }
        catch(\Angel_Exception_User $e){
            $this->assertEquals(Angel_Exception_User::INCORRECT_ORIGINAL_PASSWORD, $e->getMessageCode());
        }
        
        $result = $this->_instance->resetPassword($id, 'password', 'newpassword');
        $this->assertEquals(1, $result);
    }
    
    /**
     * @group forgotpwd 
     */
    public function testForgotPassword(){
        $email = 'test@angel.com';
        $password = 'password';
        
        $id = $this->_instance->addUser('investor', $email, 'testing', $password, 'salt', false);
        $user = $this->_instance->getUserById($id);
        $this->assertEquals(crypt($password, 'salt'), $user->password);
        
        $result = $this->_instance->forgotPassword($email);
        $this->assertTrue($result);
        
        $this->assertNotEquals(crypt($password, 'salt'), $user->password);
    }
    
    /**
     * @group addUserDoc 
     */
    public function testAddUserDoc(){
        $id = $this->_instance->addUser('investor', 'test@angel.com', 'testing', '123456', 'salt', false);
        $user = $this->_instance->getUserById($id);
        
        $filepath = APPLICATION_PATH.'/../tests/data/test.jpg';
        $filename = 'TestAddUserDoc.jpg';
        $result = $this->_instance->addUserDoc($user, \Angel_Model_User::FILETYPE_CREDIT_REPORT, $filepath, $filename, '');
        
        $filepath = APPLICATION_PATH.'/../tests/data/test_no_ext';
        $filename = 'TestAddUserDocNoExt';
        $result = $this->_instance->addUserDoc($user, \Angel_Model_User::FILETYPE_IDENTITY_BACK, $filepath, $filename, 'image/JPEG');
        
        $this->assertTrue($result instanceof \Documents\UserDoc);
    }
    
    /**
     * @group addProfileImage 
     */
    public function testAddProfileImage(){
        $id = $this->_instance->addUser('investor', 'test@angel.com', 'testing', '123456', 'salt', false);
        $user = $this->_instance->getUserById($id);
        
        $file_path = APPLICATION_PATH.'/../tests/data/test.jpg';
        $result = $this->_instance->addProfileImage($user, $file_path);
        
        $this->assertTrue($result);
    }
}