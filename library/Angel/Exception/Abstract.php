<?php
/**
 * 自定义Exception的目的在于，可以比较方便的customize exception的message，因为只要在一个地方修改就可以了。
 * 另一个好处，如果系统是中英文的，那也比较方便返回中文信息或英文信息
 */
abstract class Angel_Exception_Abstract extends Zend_Exception{
    
    protected $msg_code = null;
    
    public function __construct($msg) {
        $this->msg_code = $msg;
        parent::__construct($msg);
    }
    
    /**
     * 返回exception的描述信息
     * @return string
     */
    abstract public function getDetail();
    
    /**
     * 返回exception的错误码
     * @return string 
     */
    public function getMessageCode(){
        return $this->msg_code;
    }
    
    protected function setMessageCode($msg_code){
        $this->msg_code = $msg_code;
    }
}

?>
