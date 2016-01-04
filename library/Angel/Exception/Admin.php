<?php
/**
 * 自定义Exception的目的在于，可以比较方便的customize exception的message，因为只要在一个地方修改就可以了。
 * 另一个好处，如果系统是中英文的，那也比较方便返回中文信息或英文信息
 */
class Angel_Exception_Admin extends Angel_Exception_Abstract{
    
    const REFUSE_IDENTITY_INFO_REASON_REQUIRED = 'refuse_identity_info_reason_required';
    const REFUSE_COMPANY_INFO_REASON_REQUIRED = 'refuse_company_info_reason_required';
    
    /**
     * 返回exception的描述信息
     * @return string
     */
    public function getDetail(){
        switch($this->msg_code){
            case self::REFUSE_IDENTITY_INFO_REASON_REQUIRED:
                return '必须提供用户信息未审核通过的原因';
                break;
            case self::REFUSE_COMPANY_INFO_REASON_REQUIRED:
                return '必须提供公司信息未审核通过的原因';
                break;
        }
    }
}

?>
