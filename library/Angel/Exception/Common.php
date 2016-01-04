<?php

class Angel_Exception_Common extends Angel_Exception_Abstract{
    
    const IMAGE_NOT_ACCEPTED = 'image_not_excepted';
    
    /**
     * 返回exception的描述信息
     * @return string
     */
    public function getDetail(){
        switch($this->msg_code){
            case self::IMAGE_NOT_ACCEPTED:
                return '天使圈只接收JPG和PNG格式的图片.';
                break;
        }
    }
}

?>
