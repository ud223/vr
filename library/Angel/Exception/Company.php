<?php

class Angel_Exception_Company extends Angel_Exception_Abstract{
    
    const ONLY_STARTUP_CAN_CREATE_COMPANY = 'only_startup_can_create_company';
    
    const GUARANTOR_CANDIDATE_LIMITED = 'guarantor_candidate_limited';
    
    const GUARANTOR_CANDIDATE_EMAIL_NOT_VALID = 'guarantor_candidate_email_not_valid';
    
    const GUARANTOR_CANDIDATE_INFO_MISSED = 'guarantor_candidate_info_missed';
    
    const COMPANY_NOT_FOUND = 'company_not_found';
    
    const ONLY_INVESTOR_CAN_INVEST_COMPANY = 'only_investor_can_invest_company';
    
    const INVALID_INVEST_PERCENT = 'invalid_invest_percent';
    
    const INVEST_PERCENT_OVER_MAXIMUM = 'invest_percent_over_maximum';
    
    const INVESTOR_FOR_ONE_COMPANY_ONLY_ONCE = 'investor_for_one_company_only_once';
    
    const COMPANY_ALREADY_STOP_FUNDING = 'company_already_stop_funding';
    
    const COMPANY_NOT_IN_FUNDING = 'company_not_in_funding';
    
    /**
     * 返回exception的描述信息
     * @return string
     */
    public function getDetail(){
        switch($this->msg_code){
            case self::ONLY_STARTUP_CAN_CREATE_COMPANY:
                return '只有创业者才能创建公司';
            case self::GUARANTOR_CANDIDATE_LIMITED:
                return '你不能再添加担保候选人了';
            case self::GUARANTOR_CANDIDATE_EMAIL_NOT_VALID:
                return '担保候选人的email地址无效。注意，担保候选人的email地址必须唯一，并且不能和公司创建人的email地址相同';
            case self::GUARANTOR_CANDIDATE_INFO_MISSED:
                return '担保人的信息不完全，请完整输入担保人的姓名，电话，Email地址以及与公司创建人的关系';
            case self::COMPANY_NOT_FOUND:
                return '公司还未创建';
            case self::ONLY_INVESTOR_CAN_INVEST_COMPANY:
                return '只有投资人才能投资公司';
            case self::INVALID_INVEST_PERCENT:
                return '投资比例无可为0';
            case self::INVEST_PERCENT_OVER_MAXIMUM:
                return '投资比例超过限制';
            case self::INVESTOR_FOR_ONE_COMPANY_ONLY_ONCE:
                return '一个投资人只能对一个公司投资一次';
            case self::COMPANY_ALREADY_STOP_FUNDING:
                return '公司已经过了融资阶段，不能接受投资';
            case self::COMPANY_NOT_IN_FUNDING:
                return '公司没有处于融资阶段，不能接受投资';
        }
    }
}

?>
