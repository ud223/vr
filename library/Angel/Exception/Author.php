<?php

class Angel_Exception_Author extends Angel_Exception_Abstract {
    
    const AUTHOR_NOT_FOUND = 'author_not_found';
    const AUTHOR_CANT_BE_REMOVED = 'author_cant_be_removed';

    private static $_author = null;

    /**
     * a static method to wrap getDetail
     * author singlaton pattern
     */
    public static function returnDetail($msg_code) {
        if (!self::$_author) {
            self::$_author = new Angel_Exception_Author('');
        }
        self::$_author->setMessageCode($msg_code);

        return self::$_author->getDetail();
    }

    /**
     * 返回exception的描述信息
     * @return string
     */
    public function getDetail() {
        switch ($this->msg_code) {
            case self::AUTHOR_NOT_FOUND:
                return '作者未找到';
            case self::AUTHOR_CANT_BE_REMOVED:
                return '作者无法删除';
        }
    }

}

?>
