<?php
namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document */
class Company extends AbstractDocument{
    
    const FUNDING_STATUS_READY = 'ready';   // 就绪，等待融资流程启动
    const FUNDING_STATUS_ING = 'ing';       // 融资进行时
    const FUNDING_STATUS_END = 'end';       // 融资结束
    
    /** @ODM\ReferenceOne(targetDocument="\Documents\User") */
    protected $owner;

    /** @ODM\String */
    protected $name;    // 公司名称
    
    /** @ODM\Boolean */
    protected $active_bln = true;
    
    /** @ODM\String */
    protected $logo;    // 公司logo
    
    /** @ODM\EmbedMany(targetDocument="\Documents\CompanyDoc") */
    protected $images = array();    // 公司图片
    
    /** @ODM\String */
    protected $company_video; // 公司视频链接

    /** @ODM\String */
    protected $additional_rights; // 附加权益
    
    /** @ODM\String */
    protected $brief_descr; // 20业务介绍
    
    /** @ODM\String */
    protected $highlight; // 项目亮点
    
    /** @ODM\String */
    protected $profit_model; // 盈利模式
    
    /** @ODM\String */
    protected $advantage; // 为什么是我，竞争优势
    
    /** @ODM\String */
    protected $descr; // 600字业务介绍
    
    /** @ODM\String */
    protected $history; // 发展历史和规划
    
    /** @ODM\String */
    protected $representative;  // 法人代表
    
    /** @ODM\String */
    protected $founded_date;    // 公司成立时间
    
    /** @ODM\Int */
    protected $size;   // 公司规模 
    
    /** @ODM\String */
    protected $website;     // 公司网站链接
    
    /** @ODM\String */
    protected $phone;   // 公司电话
    
    /** @ODM\String */
    protected $fax;   // 公司传真
    
    /** @ODM\String */
    protected $address;     // 公司地址

    /** @ODM\Int */
    protected $expected_funding_amt;    // 目标融资金额

    /** @ODM\Int */
    protected $funded_amt;    // 已经融资的额度
    
    /** @ODM\Int */
    protected $funding_period; // 融资时限
    
    /** @ODM\Int */
    protected $funding_stock_percent; // 目标融资出售股份

    /** @ODM\Int */
    protected $funding_upper_limit; // 融资金额上线 (暂时无效)
    
    /** @ODM\Int */
    protected $funding_mini_unit; // 最小投资单位
    
    /** @ODM\EmbedMany(targetDocument="\Documents\CompanyDoc") */
    protected $funding_specification = array(); // 融资需求说明书
    
    /** @ODM\EmbedOne(targetDocument="\Documents\CompanyDoc") */
    protected $business_licence; // 营业执照

    /** @ODM\EmbedOne(targetDocument="\Documents\CompanyDoc") */
    protected $funder_specification; // 公司现有股东组成情况证明
    
    /** @ODM\EmbedOne(targetDocument="\Documents\CompanyDoc") */
    protected $funder_decision; // 股东会决议
    
    /** @ODM\EmbedMany(targetDocument="\Documents\CompanyDoc") */
    protected $credit_reports = array(); // 公司信用报告
    
    /** @ODM\EmbedMany(targetDocument="\Documents\CompanyDoc") */
    protected $personal_credit_reports = array(); // 融资人信用报告
    
    /** @ODM\EmbedMany(targetDocument="\Documents\CompanyDoc") */
    protected $financial_reports = array(); // 财务报表
    
    /** @ODM\EmbedMany(targetDocument="\Documents\CompanyDoc") */
    protected $support_files = array(); // 其它支持文件
    
    /** @ODM\String */
    protected $startup_introduction_video; // 创业者自述的视频链接
    
    /** @ODM\String */
    protected $startup_introduction;   // 创业者自述
    
    /** @ODM\EmbedMany(targetDocument="\Documents\Candidate") */
    protected $guarantor_candidate = array();
    
    /** @ODM\EmbedMany(targetDocument="\Documents\Candidate") */
    protected $refused_guarantor_candidate = array();
    
    /** @ODM\Boolean */
    protected $wait_tobe_validate;  // 是否在等待信息被验证

    /** @ODM\EmbedMany(targetDocument="\Documents\Reason") */
    protected $validation_refused_reason = array(); // 公司信息未通过验证的原因
    
    /** @ODM\Boolean */
    protected $validated_bln;   // 是否信息通过了审核
    
    /** @ODM\Date */
    protected $validated_date;  // 审核通过的时间

    /** @ODM\String */
    protected $funding_status;  // 融资状态，就绪，进行时，结束
    
    /** @ODM\Date */
    protected $funding_startdate; // 融资开始时间

    /** @ODM\Date */
    protected $funding_enddate; // 融资结束时间
    
    /** @ODM\EmbedMany(targetDocument="\Documents\CompanyInvestor") */
    protected $company_investors = array(); // 公司的投资人
    
    public function getRequiredFields(){
        return array('name'=>'公司名称', 'logo'=>'公司LOGO', 'images'=>'公司图片', 'brief_descr'=>'20字业务介绍', 
                     'highlight'=>'项目亮点', 'profit_model'=>'怎样赚钱', 'advantage'=>'为什么是我，竞争优势', 'descr'=>'600字业务介绍', 
                     'history'=>'发展历史和规划', 'representative'=>'法人代表', 'founded_date'=>'公司成立时间', 'size'=>'公司规模', 'website'=>'公司网站链接',
                     'phone'=>'公司电话', 'fax'=>'公司传真', 'address'=>'公司地址', 'startup_introduction_video'=>'创业者自述视频链接', 'startup_introduction'=>'创业者自述文字描述',
                     'financial_reports'=>'财务报表', 'funding_specification'=>'融资需求计划书', 'business_licence'=>'营业执照', 'funder_specification'=>'公司现有股东组成情况证明', 'funder_decision'=>'股东会决议','credit_reports'=>'公司信用报告', 'personal_credit_reports'=>'融资个人信用报告', 
                     'expected_funding_amt'=>'目标融资金额', 'funding_period'=>'目标融资时限', 'funding_stock_percent'=>'目标融资金额出售股份', 'funding_mini_unit'=>'最小投资单位', 'guarantor_candidate'=>'担保人');
    }
    
    /**
     * 添加公司图片 
     */
    public function addCompanyImage($image_filename){
        $doc = new \Documents\CompanyDoc();
        $doc->angelname = $image_filename;
        
        $this->images[] = $doc;
        
        return $doc;
    }
    
    /**
     * 删除公司图片
     * @param string $image_id
     * @return boolean 
     */
    public function removeCompanyImage($image_id){
        return $this->removeCompanyDoc($this->images, $image_id);
    }
    
    /**
     * 添加营业执照
     * @param string $filename
     * @param string $angelname
     * @return \Documents\CompanyDoc
     */
    public function addBusinessLicenceDoc($filename, $angelname){
        $document = new \Documents\CompanyDoc();
        
        $document->filename = $filename;
        $document->angelname = $angelname;
        
        $this->business_licence = $document;
        
        return $document;
    }
    
    /**
     * 移除营业执照
     * @param type $document_id 
     */
    public function removeBusinessLicenceDoc($document_id){
        return $this->removeCompanyDoc($this->business_licence, $document_id);
    }
    
    /**
     * 添加信用报告
     * @param string $filename
     * @param string $angelname
     * @return \Documents\CompanyDoc
     */
    public function addCreditReportsDoc($filename, $angelname){
        $document = new \Documents\CompanyDoc();
        
        $document->filename = $filename;
        $document->angelname = $angelname;
        
        $this->credit_reports[] = $document;
        
        return $document;
    }
    
    /**
     * 移除信用报告
     * @param string $document_id
     * @return boolean 
     */
    public function removeCreditReportsDoc($document_id){
        return $this->removeCompanyDoc($this->credit_reports, $document_id);
    }
    
    /**
     * 添加融资人信用报告
     * @param string $filename
     * @param string $angelname
     * @return \Documents\CompanyDoc
     */
    public function addPersonalCreditReportsDoc($filename, $angelname){
        $document = new \Documents\CompanyDoc();
        
        $document->filename = $filename;
        $document->angelname = $angelname;
        
        $this->personal_credit_reports[] = $document;
        
        return $document;
    }
    
    /**
     * 移除融资人信用报告
     * @param string $document_id
     * @return boolean 
     */
    public function removePersonalCreditReportsDoc($document_id){
        return $this->removeCompanyDoc($this->personal_credit_reports, $document_id);
    }
        
    /**
     * 添加财务报告
     * @param string $filename
     * @param string $angelname
     * @return \Documents\CompanyDoc
     */
    public function addFinancialReportsDoc($filename, $angelname){
        $document = new \Documents\CompanyDoc();
        
        $document->filename = $filename;
        $document->angelname = $angelname;
        
        $this->financial_reports[] = $document;
        
        return $document;
    }
    
    /**
     * 移除财务报告
     * @param string $document_id
     * @return boolean 
     */
    public function removeFinancialReportsDoc($document_id){
        return $this->removeCompanyDoc($this->financial_reports, $document_id);
    }
    
    /**
     * 添加股东会决议
     * @param string $filename
     * @param string $angelname
     * @return \Documents\CompanyDoc
     */
    public function addFunderDecisionDoc($filename, $angelname){
        $document = new \Documents\CompanyDoc();
        
        $document->filename = $filename;
        $document->angelname = $angelname;
        
        $this->funder_decision = $document;
        
        return $document;
    }
    
    /**
     * 移除股东会决议
     * @param type $document_id 
     */
    public function removeFunderDecisionDoc($document_id){
        return $this->removeCompanyDoc($this->funder_decision, $document_id);
    }
    
    /**
     * 添加公司现有股东组成情况证明
     * @param string $filename
     * @param string $angelname
     * @return \Documents\CompanyDoc
     */
    public function addFunderSpecificationDoc($filename, $angelname){
        $document = new \Documents\CompanyDoc();
        
        $document->filename = $filename;
        $document->angelname = $angelname;
        
        $this->funder_specification = $document;
        
        return $document;
    }
    
    /**
     * 移除公司现有股东组成情况证明
     * @param string $document_id 
     */
    public function removeFunderSpecificationDoc($document_id){
        return $this->removeCompanyDoc($this->funder_specification, $document_id);
    }
    
    /**
     * 添加融资需求说明书
     * @param string $filename
     * @param string $angelname
     * @return \Documents\CompanyDoc
     */
    public function addFundingSpecificationDoc($filename, $angelname){
        $document = new \Documents\CompanyDoc();
        
        $document->filename = $filename;
        $document->angelname = $angelname;
        
        $this->funding_specification[] = $document;
        
        return $document;
    }
    
    /**
     * 移除融资需求说明书
     * @param string $document_id
     * @return boolean 
     */
    public function removeFundingSpecificationDoc($document_id){
        return $this->removeCompanyDoc($this->funding_specification, $document_id);
    }
    
    /**
     * 添加其它支持文件
     * @param string $filename
     * @param string $angelname
     * @return \Documents\CompanyDoc
     */
    public function addSupportFilesDoc($filename, $angelname){
        $document = new \Documents\CompanyDoc();
        
        $document->filename = $filename;
        $document->angelname = $angelname;
        
        $this->support_files[] = $document;
        
        return $document;
    }
    
    /**
     * 移除其它支持文件
     * @param string $document_id
     * @return boolean 
     */
    public function removeSupportFilesDoc($document_id){
        return $this->removeCompanyDoc($this->support_files, $document_id);
    }
    
    /**
     * 一个内部公共方法，所以定义为私有的. 移除一个doc，其实不是remove掉，而是把它的active_bln设为false
     * @param  $docs
     * @param string $document_id
     * @return boolean 
     */        
    private function removeCompanyDoc($docs, $document_id){
        $result = false;
        if($docs instanceof \Doctrine\ODM\MongoDB\PersistentCollection){
            foreach($docs as &$doc){
                if($doc->id == $document_id){
                    $doc->active_bln = false;
                    $result = true;
                }
            }
        }
        else{
            if(is_object($docs) && ($docs->id == $document_id)){
                $docs->active_bln = false;
                $result = true;
            }
        }
        
        return $result;
    }
    
    /**
     * 获得图片，需要过滤已经删除的文档
     * @return array 
     */
    public function getImages(){
        return $this->getCompanyDoc($this->images);
    }
   
     /**
     * 获得投资需求，需要过滤已经删除的文档
     * @return array 
     */
    public function getFunding_specification(){
        return $this->getCompanyDoc($this->funding_specification);
    }
    
    /**
     * 获得信用报告，需要过滤已经删除的文档
     * @return array 
     */
    public function getCredit_reports(){
        return $this->getCompanyDoc($this->credit_reports);
    }

    /**
     * 获得融资人信用报告，需要过滤已经删除的文档
     * @return array 
     */
    public function getPersonal_credit_reports(){
        return $this->getCompanyDoc($this->personal_credit_reports);
    }

    /**
     * 获得财务报表，需要过滤已经删除的文档
     * @return array 
     */
    public function getFinancial_reports(){
        return $this->getCompanyDoc($this->financial_reports);
    }
    
    /**
     * 获得支持文件，需要过滤已经删除的文档
     * @return array 
     */
    public function getSupport_files(){
        return $this->getCompanyDoc($this->support_files);
    }
    
    /**
     * 内部公共方法，过滤删除的文档
     * @param array $docs
     * @return array 
     */
    private function getCompanyDoc($docs){
        $arr = array();
        
        foreach($docs as $doc){
            if($doc->active_bln){
                $arr[] = $doc;
            }
        }
        return $arr;
    }

    /**
     * 获得某个担保人
     * @param string $guarantor_id
     * @return \Documents\Candidate 
     */
    public function getGuarantorCandidate($guarantor_id){
        $result = null;
        
        for($i=0; $i<count($this->guarantor_candidate); $i++){
           if($this->guarantor_candidate[$i]->id == $guarantor_id){
               $result = $this->guarantor_candidate[$i];
               break;
           }
        }
        
        return $result;
    }
    
    /**
     * 判断是否全部担保人同意担保
     * @return boolean
     */
    public function allGuarantorCandidateAccepted(){
        $result = true;
        foreach($this->guarantor_candidate as $candidate){
            if(!$candidate->accepted_bln) {
                $result = false;
                break;
            }
        }
        return $result;
    }
    
    /**
     * 验证担保人的email地址
     * @param \Documents\Candidate $guarantor
     * @param String $email
     * @return boolean 
     */
    public function isValidGuarantorEmail($guarantor, $email){
        $validate = true;
        
        if($this->owner->email == $email){  //不能用公司创建人的email地址作为担保人地址。
            $validate = false;
        }
        else{
            foreach($this->guarantor_candidate as $tmp){    //同一个公司的担保人email地址不能重复
                if($tmp->id != $guarantor->id){
                    if($email == $tmp->email){
                        $validate = false;
                        break;
                    }                    
                }
            }
        }
        
        return $validate;
    }
    
    /**
     * 添加担保后选人 
     */
    public function addGuarantorCandidate(){
        $guarantor = new \Documents\Candidate();
        $guarantor->id = new \MongoId();
        $this->guarantor_candidate[] = $guarantor;
        
        return $guarantor;
    }
    
    /**
     * 公司信息认证被拒绝的原因
     * @param String $reason 
     */
    public function addCompanyRefusedReason($reason){
        $reasonModel = new \Documents\Reason();
        $reasonModel->content = $reason;
        
        $this->validation_refused_reason[] = $reasonModel;
    }
    
    /**
     *  是否通过了审核
     */
    public function isValidated(){
        return $this->validated_bln;
    }

    public function isInFunding(){
        return $this->funding_status == self::FUNDING_STATUS_ING;
    }

    public function startFunding(){
        $this->funding_status = self::FUNDING_STATUS_ING;
        $this->funding_startdate = new \DateTime();
        
        $tmp = new \DateTime();
        $this->funding_enddate = $tmp->add(new \DateInterval('P'.$this->funding_period.'D'));
    }
    
    public function endFunding(){
        $this->funding_status = self::FUNDING_STATUS_END;
    }
    
//    public function getFundedPercent(){
//        return number_format($this->funded_amt/$this->expected_funding_amt * 100, 2).'%';
//    }
    
    public function getLeftDays($format = true){
        $date = new \DateTime();
        $interval = $date->diff($this->funding_enddate);
        
        $days = $interval->days;
        if($format === true){
            if($days >= 1){
                return ($days+1).'天';
            }
            else{
                return $interval->h.'小时 '.$interval->m.'分钟';
            }
        }
        else{
            return $days>1 ? $days : round(($interval->h*3600 + $interval->m*60 + $interval->s)/(24*3600), 2);
        }
    }
    
//    public function isValidUnit($unit){
//        $result = true;
//        
//        $unit * $this->funding_mini_unit;
//        if($percent > self::MAX_INVEST_PERCENT){
//            $result = false;
//        }
//        
//        return $result;
//    }
//    public function getOnePercentPrice(){
//        $target = $this->expected_funding_amt;
//        $stock = $this->expected_funding_amt;
//        
//        $result = 0;
//        if(!empty($target) && !empty($stock)){
//            $result = round(($target / ($stock * 100 * 100)), 2);
//        }
//        
//        return $result;
//    }    
    
    public function getExpectedFundingAmt() {
        return intval($this->expected_funding_amt / 10000);
    }
    
    public function getFundingMiniUnit() {
        return intval($this->funding_mini_unit / 1000);
    }
    
    public function setExpectedFundingAmt($val) {
        $result = $val * 10000;
        return $result;
    }
    
    public function setFundingMiniUnit($val) {
        return $val * 1000;
    }
    
    public function addInvestor($user, $percent, $amount){
        $investor = new \Documents\CompanyInvestor();
        
        $investor->user = $user;
        $investor->percent = $percent;
        $investor->amount = $amount;
        
        $this->company_investors[] = $investor;
        $tmp = intval($this->funded_amt);
        $this->funded_amt = $tmp + $amount;
    }
    public function getFundedAmt(){
        return intval($this->funded_amt / 10000);
    }
    public function getCompanyValueAfterInvesting() {
        return intval($this->expected_funding_amt / ($this->funding_stock_percent / 100));
    }
    
    public function getCompanyValueBeforeInvesting() {
        return intval($this->getCompanyValueAfterInvesting() - $this->expected_funding_amt);
    }
}