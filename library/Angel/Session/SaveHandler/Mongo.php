<?php
/**
 * The Mongo customized PHP session save handler
 * 
 * @author powerdream5 
 */
class Angel_Session_SaveHandler_Mongo implements Zend_Session_SaveHandler_Interface{
    
    private $_options;
    private $_session;
    
    public function __construct($options) {
        $this->_options = $options;
    }


    public function open($save_path, $name){
        $result = false;
        try{
            $mongo = new MongoClient($this->_options['mongo']['server']);
            $this->_session = $mongo->selectCollection($this->_options['mongo']['dbname'], 'Session');
        }
        catch(Exception $e){
            if(APPLICATION_ENV != 'production'){
                echo "Mongo session start fails: ".$e->getMessage();
                exit;
            }
            else{
                throw $e;
            }
        }
        
        if($this->_session){
            $result = true;
        }
        
        return $result;
    }
    
    public function close(){
        return true;
    }
    
    public function read($id){
        $result = '';
        
        $sessionDocument = $this->_session->findOne(array('session_id' => $id));
        if($sessionDocument){
            $result = $sessionDocument['data'];
        }
        
        return $result;
    }
    
    public function write($id, $data){
        $sessionDocument = $this->_session->findOne(array('session_id' => $id));
        if(!$sessionDocument){
            $sessionDocument = array();
            $sessionDocument['createdAt'] = new MongoDate();
        }
        
        $sessionDocument['session_id'] = $id;
        $sessionDocument['updatedAt'] = new MongoDate();
        $sessionDocument['data'] = $data;
        
        $result = $this->_session->save($sessionDocument);
    
        return $result;
    }
    
    public function destroy($id){
        return $this->_session->remove(array('session_id' => $id));
    }
    
    public function gc($maxlifetime){
        $js = "function(){
                    var date = new Date();
                    var lapse = (date.getTime() - this.updatedAt.getTime())/1000;
                    return lapse > ".$maxlifetime.";
               }";
        $cursor = $this->_session->find(array('$where' => $js));
        foreach($cursor as $document){
            $this->destroy($document['session_id']);
        }
        
        return true;
    }
}