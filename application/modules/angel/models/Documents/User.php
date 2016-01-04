<?php
/**
 *  @author powerdream5
 *  用户document，包含创业者，投资人和担保人 
 */

namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class User extends AbstractDocument{
    
    const USER_TYPE_INVESTOR = 'investor';
    const USER_TYPE_STARTUP = 'startup';
    const USER_TYPE_GUARANTEE = 'guarantee';

    const IDENTITY_TYPE_ID = 'identity';
    const IDENTITY_TYPE_PASSPORT = 'passport';
    const IDENTITY_TYPE_DRIVE_LICENSE = 'drive_license';
    
    /** @ODM\String */
    protected $email;
    
    /** @ODM\String */
    protected $user_type;

    /** @ODM\String */
    protected $username;
    
    /** @ODM\String */
    protected $name;
    
     /** @ODM\Int */
    protected $author = 0; 
    
    /** @ODM\String */
    protected $identity_type;
    
    /** @ODM\String */
    protected $identity_id;
    
    /** @ODM\String */
    protected $password;
    
    /** @ODM\String */
    protected $password_src;
    
    /** @ODM\Int */
    protected $age;
    
    /** @ODM\String */
    protected $gender;
    
    /** @ODM\String */
    protected $salt;
    
    /** @ODM\Boolean */
    protected $active_bln = true;
    
    /** @ODM\Boolean */
    protected $email_validated_bln = false;
    
    /** @ODM\Boolean */
    protected $validated_bln;   //用户是否通过了验证
    
    /** @ODM\Boolean */
    protected $admin_bln;
    
    /** @ODM\Boolean */
    protected $wait_tobe_validate;  // 是否在等待信息被验证
    
    /** @ODM\String */
    protected $profile_image;
    
    /** @ODM\String */
    protected $phone;
    
    /** @ODM\String */
    protected $address;
    
    /** @ODM\String */
    protected $ip;  // 用户最后一次登录的ip
    
    /** @ODM\Date */
    protected $last_login;  // 用户最后一次登录的时间
    
    /** @ODM\EmbedOne(targetDocument="\Documents\UserDoc") */
    protected $identity_front_doc; // 身份照正面
    
    /** @ODM\EmbedOne(targetDocument="\Documents\UserDoc") */
    protected $identity_back_doc; // 身份照背面
        
    /** @ODM\EmbedMany(targetDocument="\Documents\Reason") */
    protected $identity_refused_reason = array(); // 用户身份信息被拒绝的原因
    
    /** @ODM\EmbedMany(targetDocument="\Documents\InvestedCompany") */
    protected $invested_companies = array(); // 投资过的公司
    
    /** @ODM\ReferenceMany(targetDocument="\Documents\Category") */
    protected $category = array();
    
    /** @ODM\Int */
    protected $count = 0; //推荐数量
    
    /** @ODM\Int */
    protected $like = 1; //1为喜好0为随机

    /** @ODM\Hash */
    protected $attribute = array();    // 用户属性，各种设置存放的地方
    
    /**
     * 验证身份是否正确 
     */
    public function isUsertypeValid($type){
        $valid = false;
        
        if($type == self::USER_TYPE_INVESTOR || $type == self::USER_TYPE_STARTUP || $type == self::USER_TYPE_GUARANTEE){
            $valid = true;
        }
        
        return $valid;
    }
    
    /**
     * set method for password
     * @param string $value
     * @throws Angel_Exception_User 
     */
    public function setPassword($value){
        if(strlen($value) < 1){
            throw new Angel_Exception_User(Angel_Exception_User::PASSWORD_TOO_SHORT);
        }
        else if(strlen($value) > 18){
            throw new Angel_Exception_User(Angel_Exception_User::PASSWORD_TOO_LONG);
        }
        
        $this->password = crypt($value, $this->salt);
        $this->password_src = $value;
    }
    
    /**
     * 用户的email地址是否验证过了－用户是否激活了
     * @return Boolean 
     */
    public function isEmailValidated(){
        return $this->email_validated_bln;
    }
    
    /**
     * 用户是否通过审核[实名认证]
     * @return type 
     */
    public function isValidated(){
        return $this->validated_bln;
    }
    
    /**
     * 用户信息是否正在审核中 
     */
    public function isInValidatedList(){
        return $this->wait_tobe_validate;
    }
    
    /**
     * 添加用户的身份证正面文件
     * @param type $filename
     * @param type $angelname 
     */
    public function addIdentityFrontDoc($filename, $angelname){
        $document = new \Documents\UserDoc();
        
        $document->filename = $filename;
        $document->angelname = $angelname;
        
        $this->identity_front_doc = $document;
        
        return $document;
    }
    
    /**
     * 添加用户的身份证背面文件
     * @param type $filename
     * @param type $angelname 
     */
    public function addIdentityBackDoc($filename, $angelname){
        $document = new \Documents\UserDoc();
        
        $document->filename = $filename;
        $document->angelname = $angelname;
        
        $this->identity_back_doc = $document;
        
        return $document;
    }
        
    /*
     * 获取用户usertype的中文名
     * @return String 
     */
    public function getUserTypeInChinese(){
        switch($this->user_type){
            case self::USER_TYPE_INVESTOR:
                return '投资人';
            case self::USER_TYPE_STARTUP:
                return '创业者';
            case self::USER_TYPE_GUARANTEE:
                return '担保人';
        }
    }
    
    /**
     * 是创业者
     * @return Boolean
     */
    public function isStartup(){
        return $this->user_type == self::USER_TYPE_STARTUP;
    }
    
    /**
     * 是投资人
     * @return Boolean
     */
    public function isInvestor(){
        return $this->user_type == self::USER_TYPE_INVESTOR;
    }
    
    /**
     * 实名认证被拒绝的原因
     * @param String $reason 
     */
    public function addIdentityRefusedReason($reason){
        $reasonModel = new \Documents\Reason();
        $reasonModel->content = $reason;
        
        $this->identity_refused_reason[] = $reasonModel;
    }
    
    
        public function getGuarantors(){
        $arr = array();
        
        foreach($this->guarantors as $guarantor){
            if($guarantor->active_bln){
                $arr[] = $guarantor;
            }
        }
        
        return $arr;
    }
    
    public function addGuarantors(\Documents\Company $company, $guarantor_id){
        $orig = null;
        foreach($this->guarantors as &$guarantor){
            if($guarantor->guarantor_id == $guarantor_id){
                $orig = $guarantor;
            }
        }
        
        if(!$orig){
            $guarantor = new \Documents\Guarantor();
            
            $guarantor->company = $company;
            $guarantor->guarantor_id = $guarantor_id;
            $guarantor->active_bln = true;

            $this->guarantors[] = $guarantor;
        }
        else{
            $orig->active_bln = true;
        }
    }
    
    public function removeGuarantor($guarantor_id){
        $result = false;
        
        foreach($this->guarantors as &$guarantor){
            if($guarantor->guarantor_id == $guarantor_id){
                $guarantor->active_bln = false;
                
                $result = true;
                break;
            }
        }
        
        return $result;
    }
    
    public function addInvestedCompanies($company, $percent, $amount){
        $invested = new \Documents\InvestedCompany();
        
        $invested->company = $company;
        $invested->percent = $percent;
        $invested->amount = $amount;
        
        $this->invested_companies[] = $invested;
    }
    
    public function hasAlreadyInvestedCompany($company_id){
        $result = false;
        
        foreach($this->invested_companies as $company){
            if($company->company->id == $company_id){
                $result = true;
                break;
            }
        }
        
        return $result;
    }
    
    public function addCategory(\Documents\Category $p) { 
        $this->category[] = $p;
    }
    
    public function clearCategory() {
        $this->category = array();
    }
}
