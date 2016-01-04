<?php
      
class Angel_Model_Token extends Angel_Model_AbstractModel{
    
    protected $_document_class = '\Documents\Token';
    
    /**
     *
     * @param string $token 编码过的token
     * @param int $expiry   过期的期限，以分钟为单位
     * @param string $params 
     * @return \Document\Token model
     */
    public function generateToken($token, $expiry, $params=''){
        $tokenModel = new $this->_document_class();
        $tokenModel->token = preg_replace('/\//', '\\', $token);
        
        $date = new DateTime();
        $date->add(new DateInterval('PT'.$expiry.'M'));
        $tokenModel->expire_date = $date;
        $tokenModel->active_bln = true;
        $tokenModel->params = $params;
        
        $this->_dm->persist($tokenModel);
        $this->_dm->flush();
        
        return $tokenModel;
    }
    
    public function getTokenByToken($token, $active_bln=true){
        $token = $this->_dm->createQueryBuilder($this->_document_class)
                           ->field('token')->equals($token);
        if($active_bln === true){
            $token->field('active_bln')->equals(true);
        }
        $token = $token->getQuery()->getSingleResult();
        
        return $token;
    }
    
    public function disableToken($token){
        $result = false;
        
        $tokenDocument = $this->getTokenByToken($token);
        if($tokenDocument){
            $tokenDocument->active_bln = false;
            
            $this->_dm->persist($tokenDocument);
            $this->_dm->flush();
            
            $result = true;
        }
        
        return $result;
    }
}
