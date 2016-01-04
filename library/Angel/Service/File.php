<?php

class Angel_Service_File{
    
    protected $_bootstrap_options;
    
    // array is not allowed as constant
    private $accepted_doc_type = array('application/pdf', 'application/msword', 'application/vnd.ms-excel', 'image/jpeg', 'image/png');
    private $accepted_doc_extension = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png');
    
    public function __construct($bootstrap_options){
        $this->_bootstrap_options = $bootstrap_options;
    }
    
    /**
     * 首先，并不是所有浏览器对minetype的支持都是很可靠的，二是服务器端apache的服务器没有对minetype配置好，也无法辨认上传文件的mimetype
     * 所以这里为什么还要通过extension辅助识别
     * @param type $filepath
     * @return boolean 
     */
    public function isAcceptedDocument($mimetype, $extension=''){
        $result = in_array(strtolower($mimetype), $this->accepted_doc_type);
        if(!$result && (strtolower($mimetype) == 'application/octet-stream') && !empty($extension)){
            $result = in_array(strtolower($extension), $this->accepted_doc_extension);
        }
        
        return $result;
    }
    
    public function getExtensionByMinetype($mimetype){
        $extension = '';
        switch(strtolower($mimetype)){
            case 'application/pdf':
                $extension = 'pdf';
                break;
            case 'application/msword':
                $extension = 'doc';
                break;
            case 'application/vnd.ms-excel':
                $extension = 'xls';
                break;
            case 'image/jpeg':
                $extension = 'jpg';
                break;
            case 'image/png':
                $extension = 'png';
                break;
        }
        
        return $extension;
    }
    
    public function getExtensionByFilename($filename){
        $extension = '';
        
        $pos = strrpos($filename, '.');
        if($pos !== false){
            $extension = substr($filename, $pos+1);
        }
        
        return $extension;
    }
}
