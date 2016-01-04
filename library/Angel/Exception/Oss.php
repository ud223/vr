<?php

class Angel_Exception_Oss extends Angel_Exception_Abstract {
    
    const ADD_OSS_FAIL = 'add_oss_fail';
    const SAVE_OSS_FAIL = 'save_oss_fail';
    const OSS_NOT_FOUND = 'oss_not_found';

    private static $_oss = null;

    /**
     * a static method to wrap getDetail
     * oss singlaton pattern
     */
    public static function returnDetail($msg_code) {
        if (!self::$_oss) {
            self::$_oss = new Angel_Exception_Oss('');
        }
        self::$_oss->setMessageCode($msg_code);

        return self::$_oss->getDetail();
    }

    /**
     * 返回exception的描述信息
     * @return string
     */
    public function getDetail() {
        switch ($this->msg_code) {
            case self::SAVE_OSS_FAIL:
                return '保存节目信息失败';
            case self::ADD_OSS_FAIL:
                return '添加节目信息失败';
            case self::OSS_NOT_FOUND:
                return '节目未找到';
        }
    }

}

?>
