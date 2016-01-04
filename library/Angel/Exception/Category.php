<?php

class Angel_Exception_Category extends Angel_Exception_Abstract {

    const CATEGORY_NOT_FOUND = 'category_not_found';
    const CATEGORY_CANT_BE_REMOVED = 'category_cant_be_removed';
    const CATEGORY_CANT_BE_SELF = 'category_cant_be_self';
    const CATEGORY_CANT_BE_PARENT = 'category_cant_be_parent';

    private static $_category = null;

    /**
     * a static method to wrap getDetail
     * category singlaton pattern
     */
    public static function returnDetail($msg_code) {
        if (!self::$_category) {
            self::$_category = new Angel_Exception_Category('');
        }
        self::$_category->setMessageCode($msg_code);

        return self::$_category->getDetail();
    }

    /**
     * 返回exception的描述信息
     * @return string
     */
    public function getDetail() {
        switch ($this->msg_code) {
            case self::CATEGORY_NOT_FOUND:
                return '分类未找到';
            case self::CATEGORY_CANT_BE_REMOVED:
                return '分类无法删除（可能是由于该分类不为空导致，请移出或删除分类下所有节目后重试）';
            case self::CATEGORY_CANT_BE_SELF:
                return '自己不能作为自己的父类';
            case self::CATEGORY_CANT_BE_PARENT:
                return '该分类不能作为父类';
        }
    }

}

?>
