<?php

require_once APPLICATION_PATH.'/../library/Aliyun/Oss/sdk.class.php';

class Angel_Service_Oss{

    protected $_bootstrap_options;
    
    public function __construct($bootstrap_options){
        $this->_bootstrap_options = $bootstrap_options;
    }
    
    public function upload($bucket, $object, $file_path){ 
        $response = $this->getOss()->upload_file_by_file($bucket,$object,$file_path);
        
        if($response->status != '200'){
            throw new Exception('上传文件到阿里云失败：object: '.$object.'   file_path: '.$file_path);
        }
        
        return true;
    }
    
    private function getOss(){
        $access_key = $this->_bootstrap_options['aliyun']['access_key'];
        $key_secret = $this->_bootstrap_options['aliyun']['key_secret'];
        
        $oss = new \ALIOSS($access_key, $key_secret);
        
        return $oss;
    }
}
