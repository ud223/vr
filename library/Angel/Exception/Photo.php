<?php

class Angel_Exception_Photo extends Angel_Exception_Abstract {
    
    const PHOTO_NOT_FOUND = 'photo_not_found';
    const PHOTO_CANT_BE_REMOVED = 'photo_cant_be_removed';

    private static $_photo = null;

    /**
     * a static method to wrap getDetail
     * photo singlaton pattern
     */
    public static function returnDetail($msg_code) {
        if (!self::$_photo) {
            self::$_photo = new Angel_Exception_Photo('');
        }
        self::$_photo->setMessageCode($msg_code);

        return self::$_photo->getDetail();
    }

    /**
     * 返回exception的描述信息
     * @return string
     */
    public function getDetail() {
        switch ($this->msg_code) {
            case self::PHOTO_NOT_FOUND:
                return '图片未找到';
            case self::PHOTO_CANT_BE_REMOVED:
                return '图片无法删除';
        }
    }

}

?>
