<?php

class Angel_Exception_Program extends Angel_Exception_Abstract {
    
    const ADD_PROGRAM_FAIL = 'add_program_fail';
    const SAVE_PROGRAM_FAIL = 'save_program_fail';
    const PROGRAM_PRICE_INVALID = 'program_price_invalid';
    const PROGRAM_NOT_FOUND = 'program_not_found';

    private static $_program = null;

    /**
     * a static method to wrap getDetail
     * program singlaton pattern
     */
    public static function returnDetail($msg_code) {
        if (!self::$_program) {
            self::$_program = new Angel_Exception_Program('');
        }
        self::$_program->setMessageCode($msg_code);

        return self::$_program->getDetail();
    }

    /**
     * 返回exception的描述信息
     * @return string
     */
    public function getDetail() {
        switch ($this->msg_code) {
            case self::SAVE_PROGRAM_FAIL:
                return '保存节目信息失败';
            case self::ADD_PROGRAM_FAIL:
                return '添加节目信息失败';
            case self::PROGRAM_PRICE_INVALID:
                return '价格必须是小数';
            case self::PROGRAM_NOT_FOUND:
                return '节目未找到';
        }
    }

}

?>
