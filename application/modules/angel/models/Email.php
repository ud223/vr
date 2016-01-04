<?php

class Angel_Model_Email{
    
    // 当有新的注册用户，email通知他激活帐户
    const EMAIL_NEW_USER_EMAIL_VALIDATION = 'email-validation';
    
    // 当用户忘记了密码，我们发送临时密码给他
    const EMAIL_FORGOT_PASSWORD = 'forgot-password';
    
    // 当用户提交了实名认证申请，通知管理员
    const EMAIL_REALNAME_VALIDATE_ADMIN = 'realname-notice-admin';
    
    // 当用户的个人信息未通过审核，email通知用户
    const EMAIL_IDENTITY_INFO_REFUSED = 'identity-info-refused-notice-user';
    
    // 当用户的个人信息通过了审核，email通知用户
    const EMAIL_IDENTITY_INFO_ACCEPTED = 'identity-info-accepted-notice-user';
    
    // 当用户提交公司信息时，通知管理员
    const EMAIL_COMPANY_INFO_VALIDATE_ADMIN = 'company-info-notice-admin';
    
    // 当公司信息未通过审核，email通知用户
    const EMAIL_COMPANY_INFO_REFUSED = 'company-info-refused-notice-user';
    
    // 当公司信息通过了审核，email通知用户
    const EMAIL_COMPANY_INFO_ACCEPTED = 'company-info-accepted-notice-user';
    
    // 发送给担保人的邮件
    const EMAIL_CANDIDATE_NOTICE = 'candidate-notice';
        
    // 担保候选人拒绝邀请通知公司信息创建者
    const CANDIDATE_REFUSE_INVITATION = 'candidate-refuse-invitation-notice-user';
    
    // 担保候选人接受了邀请通知公司信息创建者
    const CANDIDATE_ACCEPT_INVITATION = 'candidate-accept-invitation-notice-user';
    
    // 公司获得投资
    const COMPANY_GOT_FUND = 'company-got-fund-notice-user';
    
    // 通知创业者公司开始融资
    const EMAIL_STARTUP_COMPANY_START_FUNDING = 'startup-company-start-funding';
    
    // 通知担保人公司开始融资
    const EMAIL_GUARANTOR_COMPANY_START_FUNDING = 'guarantor-company-start-funding';
    
    static $company_name;
    
    public static function setCompanyName($val) {
        self::$company_name = $val;
    }
    
    public static function getSubject($template){
        switch($template){
            case self::EMAIL_NEW_USER_EMAIL_VALIDATION:
                return sprintf('%s帐号激活邮件', self::$company_name);
            case self::EMAIL_FORGOT_PASSWORD:
                return sprintf('你的%s登录密码', self::$company_name);
            case self::EMAIL_NEW_USER_EMAIL_VALIDATION:
                return '有用户提交了实名认证申请';
            case self::EMAIL_IDENTITY_INFO_REFUSED:
                return '你的实名认证申请未通过审核';
            case self::EMAIL_IDENTITY_INFO_ACCEPTED:
                return '你的实名认证申请通过了审核';
            case self::EMAIL_COMPANY_INFO_VALIDATE_ADMIN:
                return '有用户提交了公司信息，等待被审核';
            case self::EMAIL_COMPANY_INFO_REFUSED:
                return '你的公司信息申请未通过审核';
            case self::EMAIL_COMPANY_INFO_ACCEPTED:
                return '你的公司信息申请通过了审核';
        }
    }
    
    public static function sendEmail($emailService, $template, $to, $params, $subject=''){
        $subject = empty($subject) ? self::getSubject($template) : $subject;
        
        return $emailService->sendEmail($template, $to, $subject, $params);
    }
}
