<?php

class Angel_Auth_Adapter_Mongo implements Zend_Auth_Adapter_Interface{
    
    private $_dm;
    private $_email;
    private $_password;
    private $_document;
    
    /**
     *
     * @param mongo document manager $dm
     * @param string $email
     * @param string $password 
     */
    public function __construct($dm, $document, $email, $password){
        $this->_dm = $dm;
        $this->_document = $document;
        $this->_email = $email;
        $this->_password = $password;
    }


    public function authenticate(){
        $user = $this->_dm->createQueryBuilder($this->_document)
                          ->field('email')->equals($this->_email)
                          ->field('active_bln')->equals(true)
                          ->getQuery()
                          ->execute();
        
        $code = Zend_Auth_Result::FAILURE;
        $identity = '';
        $messages = array();
        
        if($user->count()){
            if($user->count() > 1){
                $code = Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS;
                $messages[] = Angel_Exception_User::returnDetail(Angel_Exception_User::EMAIL_NOT_UNIQUE);
            }
            else{
                $user = $user->getNext();
                
                if($user->password != crypt($this->_password, $user->salt)){
                    $code = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
                    $messages[] = Angel_Exception_User::returnDetail(Angel_Exception_User::PASSWORD_INCORRECT);
                }
                else{
                    $code = Zend_Auth_Result::SUCCESS;
                    $identity = $user->id;
                    $messages[] = "Success";
                }
            }
        }
        else{
            $code = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            $messages[] = Angel_Exception_User::returnDetail(Angel_Exception_User::EMAIL_NOT_EXIST);
        }
        
        return new Zend_Auth_Result($code, $identity, $messages);
    }
}
