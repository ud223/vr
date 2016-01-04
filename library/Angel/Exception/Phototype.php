<?php

class Angel_Exception_Phototype extends Angel_Exception_Abstract {
    
    const PHOTOTYPE_NOT_FOUND = 'phototype_not_found';
    const PHOTOTYPE_CANT_BE_REMOVED = 'phototype_cant_be_removed';

    private static $_phototype = null;

    /**
     * a static method to wrap getDetail
     * phototype singlaton pattern
     */
    public static function returnDetail($msg_code) {
        if (!self::$_phototype) {
            self::$_phototype = new Angel_Exception_Phototype('');
        }
        self::$_phototype->setMessageCode($msg_code);

        return self::$_phototype->getDetail();
    }

    /**
     * 返回exception的描述信息
     * @return string
     */
    public function getDetail() {
        switch ($this->msg_code) {
            case self::PHOTOTYPE_NOT_FOUND:
                return '图片分类未找到';
            case self::PHOTOTYPE_CANT_BE_REMOVED:
                return '图片分类无法删除（可能是由于该分类不为空导致，请移出或删除分类下所有图片后重试）';
        }
    }

}

?>
