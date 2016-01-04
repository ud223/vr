<?php
/**
 * 自定义Exception的目的在于，可以比较方便的customize exception的message，因为只要在一个地方修改就可以了。
 * 另一个好处，如果系统是中英文的，那也比较方便返回中文信息或英文信息
 */
class Angel_Exception_User extends Angel_Exception_Abstract{
    
    const ADD_USER_FAIL = 'add_user_fail';
    const ADDRESS_REQUIRED = 'address_required';
    const EMAIL_EMPTY = 'email_empty';
    const EMAIL_INVALID = 'email_invalid';
    const EMAIL_NOT_UNIQUE = 'email_not_unique';
    const USERTYPE_INVALID = 'usertype_invalid';
    const EMAIL_VALIDATION_TOKEN_INVALID = 'email_validation_token_invalid';
    const EMAIL_VALIDATION_TOKEN_EXPIRED = 'email_validation_token_expired';
    const EMAIL_VALIDATION_VALIDATED_USER = 'email_validation_validated_user';
    const IDENTITY_TYPE_REQUIRED = 'identity_type_required';
    const IDENTITY_ID_REQUIRED = 'identity_id_required';
    const INCORRECT_ORIGINAL_PASSWORD = 'incorrect_original_password';
    const USER_UPDATE_FAIL = 'user_update_fail';
    const USER_NOT_FOUND = 'user_not_found';
    const PASSWORD_INCORRECT = 'password_incorrect';
    const PASSWORD_TOO_SHORT = 'password_too_short';
    const PASSWORD_TOO_LONG = 'password_too_long';
    const EMAIL_NOT_EXIST = 'email_not_exist';
    const USERNAME_REQUIRED = 'username_required';
    const USERNAME_NOT_UNIQUE = 'username_not_unique';
    const PHONE_REQUIRED = 'phone_required';
    
    private static $_user = null;
    
    /**
     * a static method to wrap getDetail
     * user singlaton pattern
     */
    public static function returnDetail($msg_code){
        if(!self::$_user){
            self::$_user = new Angel_Exception_User('');
        }
        self::$_user->setMessageCode($msg_code);
        
        return self::$_user->getDetail();
    }
    
    /**
     * 返回exception的描述信息
     * @return string
     */
    public function getDetail(){
        switch($this->msg_code){
            case self::ADD_USER_FAIL:
                return '用户添加失败';
                break;
            case self::ADDRESS_REQUIRED:
                return '必须提供你的地址信息';
                break;
            case self::EMAIL_EMPTY:
                return 'Email地址不能为空';
                break;
            case self::EMAIL_INVALID:
                return 'Email地址错误';
                break;
            case self::EMAIL_NOT_EXIST:
                return '用户不存在';
                break;
            case self::EMAIL_NOT_UNIQUE:
                return 'Email地址已经存在, 不能重复注册';
                break;
            case self::IDENTITY_TYPE_REQUIRED:
                return '必须提供身份证件类型';
                break;
            case self::IDENTITY_ID_REQUIRED:
                return '必须提供身份证件号码';
                break;
            case self::INCORRECT_ORIGINAL_PASSWORD:
                return '原密码不对';
                break;
            case self::PASSWORD_INCORRECT:
                return '密码错误';
                break;
            case self::PASSWORD_TOO_LONG:
                return '密码太长，不能超过18位';
                break;
            case self::PASSWORD_TOO_SHORT:
                return '密码太短，至少需要6位';
                break;
            case self::PHONE_REQUIRED:
                return '必须提供一个可供联系的电话号码';
                break;
            case self::USER_NOT_FOUND:
                return '此用户不存在';
                break;
            case self::USER_UPDATE_FAIL:
                return '用户信息修改失败';
                break;
            case self::USERTYPE_INVALID:
                return '用户身份不正确';
                break;
            case self::USERNAME_REQUIRED:
                return '请输入一个用户名';
                break;
            case self::USERNAME_NOT_UNIQUE:
                return '用户名已经存在, 请使用其他用户名';
                break;
            default:
                return '用户信息错误';   
        }
    }
    
}
?>
