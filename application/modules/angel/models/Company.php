<?php
      
class Angel_Model_Company extends Angel_Model_AbstractModel{
    
    protected $_document_class = '\Documents\Company';
    
    // 关于公司的一些文件
    // 融资需求计划书
    const FILETYPE_FUND_SPEC = 'ffs';
    
    // 营业执照
    const FILETYPE_BUSINESS_LICENCE = 'fbl';
    
    // 公司现有股东组成情况证明
    const FILETYPE_FUNDER_SPEC = 'ffus';
    
    // 股东会决议
    const FILETYPE_FUNDER_DECISION = 'ffd';
    
    // 公司信用报告
    const FILETYPE_CREDIT_REPORTS = 'fcr';
    
    // 融资人信用报告
    const FILETYPE_PERSONAL_CREDIT_REPORTS = 'fpcr';
    
    // 公司财务报表
    const FILETYPE_FINANCIAL_REPORT = 'ffr';
    
    // 其它支持文件
    const FILETYPE_SUPPORT_FILES = 'fsf';
    
    // 公司LOGO
    const IMAGETYPE_LOGO = 'logo';
    
    // 公司图片
    const IMAGETYPE_IMAGE = 'image';
    
    /**
     * 根据id，获取company model
     * @param string $company_id
     * @return \Documents\Company
     */
    public function getCompanyById($company_id){
        $company = $this->_dm->createQueryBuilder($this->_document_class)
                             ->field('id')->equals($company_id)
                             ->getQuery()
                             ->getSingleResult();
        
        return $company;
    }
    
    /**
     * 获取用户的创建的公司
     * @param string $user_id
     * @return Company document
     * @throws Angel_Exception_Company 
     */
    public function getCompanyByUser($user_id){
        $company = $this->_dm->createQueryBuilder($this->_document_class)
                             ->field('owner.$id')->equals(new MongoId($user_id))
                             ->field('active_bln')->equals(true)
                             ->getQuery()
                             ->execute();
        
        return $company;
    }
    
    /**
     * 创建公司
     * @param \Documents\User $user 公司的创建者
     * @return \Documents\Company － 返回的是新创建的公司的对象
     * @throws \Angel_Exception_Company 
     */
    public function createCompany(\Documents\User $user){
        $result = null;
        
        if($user->isStartup()){
            $company = new $this->_document_class();
            $company->owner = $user;

            $this->_dm->persist($company);
            $this->_dm->flush();
            
            $result = $company;
        }
        else{
            // 只有创业者才能创建公司
            throw new \Angel_Exception_Company(Angel_Exception_Company::ONLY_STARTUP_CAN_CREATE_COMPANY);
        }
        
        return $result;
    }


    /**
     * 修改公司信息
     * @param string $company_id
     * @param array $data
     * @return boolean 
     */
    public function updateCompany($company, $data){
        $result = false;
        
        if(!(is_object($company) && ($company instanceof $this->_document_class))){
            $company = $this->getCompanyById($company);
        }
        
        if($company){
            try{
                foreach($data as $key=>$value){
                    $company->$key = $value;
                }
                
                $this->_dm->persist($company);
                $this->_dm->flush();
                
                $result = true;
            }
            catch(Exception $e){
                $this->_logger->info(__CLASS__, __FUNCTION__, $e->getMessage()."\n".$e->getTraceAsString());
            }
        }
        
        return $result;
    }
    
    /**
     * 上传了公司的logo，从tmp处copy到company logo目录，生成其resized version供将来裁剪使用，并修改company的logo field
     * @param type $company
     * @param type $image
     * @return boolean - when it is false, add profile image fails, when the image type is not correct, throw the exception. when it is ture, means success
     */
    public function addCompanyLogo(\Documents\Company $company, $image){
        $result = false;
        
        $imageService = $this->_container->get('image');
        if(!$imageService->isAcceptedImage($image)){
            throw new Angel_Exception_Common(Angel_Exception_Common::IMAGE_NOT_ACCEPTED);
        }
        else{
            $extension = $imageService->getImageTypeExtension($image);

            $utilService = $this->_container->get('util');
            $filename = $utilService->generateFilename($extension);
            $destination = $this->getImagePath(self::IMAGETYPE_LOGO, $filename);
            if(copy($image, $destination)){
                if($imageService->resizeImage($destination)){
                    $company->logo = $filename;

                    $this->_dm->persist($company);
                    $this->_dm->flush();

                    $result = true;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * 裁剪用户上传的公司logo
     * @param string $orig_image - 被裁图片的路径
     * @param array $coord - array(x, y, w, h) 
     * @return mixed 
     */
    public function generateCompanyLogo($orig_image, $coord){
        $imageService = $this->_container->get('image');
        return $imageService->generateThumbnail($orig_image, $this->_bootstrap_options['size']['logo'], $coord);
    }
    
    /**
     * 获取某种size的company logo
     * @param string $image － 基本文件名称
     * @param int $version - 文件size
     * @return string 
     */
    public function getCompanyLogo($image, $version){
        return $this->getImageByVersion(self::IMAGETYPE_LOGO, $image, $version);
    }
    
    /**
     * 上传了公司的图片，从tmp处copy到company logo目录，生成各种size的缩略图
     * @param \Documents\Company $company
     * @param String $image
     * @return \Document\CompanyDoc - when it is false, add profile image fails, when the image type is not correct, throw the exception. when it is \CompanyDoc, means success
     */
    public function addCompanyImage(\Documents\Company $company, $image){
        $result = false;
        
        $imageService = $this->_container->get('image');
        if(!$imageService->isAcceptedImage($image)){
            throw new Angel_Exception_Common(Angel_Exception_Common::IMAGE_NOT_ACCEPTED);
        }
        else{
            $extension = $imageService->getImageTypeExtension($image);

            $utilService = $this->_container->get('util');
            $filename = $utilService->generateFilename($extension);
            $destination = $this->getImagePath(self::IMAGETYPE_IMAGE, $filename);
            if(copy($image, $destination)){
                if($imageService->resizeImage($destination)){
                    if($imageService->generateThumbnail($destination, $this->_bootstrap_options['size']['company_image'])){
                        $image_document = $company->addCompanyImage($destination);
                        
                        $this->_dm->persist($company);
                        $this->_dm->flush();

                        $result = $image_document;
                    }
                }
            }
        }
        
        return $result;
    }
    
    /**
     * 检测上传的文件类型是不是指定的
     */
    public function isValidDoctype($doctype){
        $result = false;
        $doctype = strtolower($doctype);
        
        if(in_array($doctype, array(self::FILETYPE_BUSINESS_LICENCE, self::FILETYPE_CREDIT_REPORTS, self::FILETYPE_PERSONAL_CREDIT_REPORTS, self::FILETYPE_FINANCIAL_REPORT, self::FILETYPE_FUNDER_DECISION, self::FILETYPE_FUNDER_SPEC, self::FILETYPE_FUND_SPEC, self::FILETYPE_SUPPORT_FILES))){
            $result = true;
        }
        
        return $result;
    }
    
    /**
     * 添加公司的一些文档，比如融资需求计划书，营业执照等
     * @param \Documents\Company $company
     * @param string $filetype - 对应的angelhere的文件类型
     * @param string $filepath - 上传的文件地址
     * @param string $filename - 上传的文件名
     * @return mix, when the file is added success, return the \Documents\UserDoc document
     */
    public function addCompanyDoc(\Documents\Company $company, $filetype, $filepath, $filename, $mimetype=''){
        $utilService = $this->_container->get('util');
        
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        if(empty($extension)){
            $fileService = $this->_container->get('file');
            $extension = $fileService->getExtensionByMinetype($mimetype);
        }
        $angelname = $utilService->generateFilename($extension);
        
        $destination = APPLICATION_PATH.'/../public'.$this->_bootstrap_options['file']['company_doc'].DIRECTORY_SEPARATOR.$angelname;
        
        $result = false;
        if(copy($filepath, $destination)){
            switch ($filetype){
                case self::FILETYPE_BUSINESS_LICENCE:
                    $result = $company->addBusinessLicenceDoc($filename, $angelname);
                    break;
                case self::FILETYPE_CREDIT_REPORTS:
                    $result = $company->addCreditReportsDoc($filename, $angelname);
                    break;
                case self::FILETYPE_PERSONAL_CREDIT_REPORTS:
                    $result = $company->addPersonalCreditReportsDoc($filename, $angelname);
                    break;
                case self::FILETYPE_FINANCIAL_REPORT:
                    $result = $company->addFinancialReportsDoc($filename, $angelname);
                    break;
                case self::FILETYPE_FUNDER_DECISION:
                    $result = $company->addFunderDecisionDoc($filename, $angelname);
                    break;
                case self::FILETYPE_FUNDER_SPEC:
                    $result = $company->addFunderSpecificationDoc($filename, $angelname);
                    break;
                case self::FILETYPE_FUND_SPEC:
                    $result = $company->addFundingSpecificationDoc($filename, $angelname);
                    break;
                case self::FILETYPE_SUPPORT_FILES:
                    $result = $company->addSupportFilesDoc($filename, $angelname);
                    break;
            }
            

            $this->_dm->persist($company);
            $this->_dm->flush();
        }
        
        return $result;
    }
    
    /**
     * 移除公司的一些图片
     * @param \Documents\Company $company
     * @param string $image_id
     */
    public function removeCompanyImage(\Documents\Company $company, $image_id){
        $result = $company->removeCompanyImage($image_id);
        $this->_dm->persist($company);
        $this->_dm->flush();
        return $result;
    }
    
    /**
     * 移除公司的一些文档
     * @param \Documents\Company $company
     * @param type $filetype 
     */
    public function removeCompanyDoc(\Documents\Company $company, $filetype, $document_id){
        $result = false;
        
        switch ($filetype){
            case self::FILETYPE_BUSINESS_LICENCE:
                $result = $company->removeBusinessLicenceDoc($document_id);
                break;
            case self::FILETYPE_CREDIT_REPORTS:
                $result = $company->removeCreditReportsDoc($document_id);
                break;
            case self::FILETYPE_PERSONAL_CREDIT_REPORTS:
                $result = $company->removePersonalCreditReportsDoc($document_id);
                break;
            case self::FILETYPE_FINANCIAL_REPORT:
                $result = $company->removeFinancialReportsDoc($document_id);
                break;
            case self::FILETYPE_FUNDER_DECISION:
                $result = $company->removeFunderDecisionDoc($document_id);
                break;
            case self::FILETYPE_FUNDER_SPEC:
                $result = $company->removeFunderSpecificationDoc($document_id);
                break;
            case self::FILETYPE_FUND_SPEC:
                $result = $company->removeFundingSpecificationDoc($document_id);
                break;
            case self::FILETYPE_SUPPORT_FILES:
                $result = $company->removeSupportFilesDoc($document_id);
                break;
        }
        $this->_dm->persist($company);
        $this->_dm->flush();
        return $result;
    }
    
    /**
     * 获取某size图片名字
     * @param string $name - image type, could be company logo or company image
     * @param string $image － 基本文件名称
     * @param int $version - 文件size
     * @return string 
     */
    public function getImageByVersion($image_type, $image, $version){
        $imageService = $this->_container->get('image');
        return $imageService->generateImageFilename($this->getImagePath($image_type, $image), $version, false);
    }
    
    /**
     * 获取image的位置 (＊这个方法还需要根据environment不同做修改)
     * @param string $imagename
     * @return string 
     */
    public function getImagePath($image_type, $imagename){
        $dir = ($image_type == self::IMAGETYPE_LOGO) ? $this->_bootstrap_options['image']['company_logo'] : $this->_bootstrap_options['image']['company_image'];
        return APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.$dir.DIRECTORY_SEPARATOR.$imagename;
    }
    
    
    /**
     * 根据文档的类型，返回用户的此文档
     * @param string $company_id
     * @param string $doctype
     * @param string $doc_id － 此文档的ID
     * @return instance of \documents\companydoc 
     */
    public function getCompanyDoc($company_id, $doctype, $doc_id=null){
        $company = $this->getCompanyById($company_id);
        
        $doc = null;
        
        switch(strtolower($doctype)){
            case self::FILETYPE_BUSINESS_LICENCE:
                $doc = $company->business_licence;
                break;
            case self::FILETYPE_CREDIT_REPORTS:
                $doc = $company->credit_reports;
                break;
            case self::FILETYPE_PERSONAL_CREDIT_REPORTS:
                $doc = $company->personal_credit_reports;
                break;
            case self::FILETYPE_FINANCIAL_REPORT:
                $doc = $company->financial_reports;
                break;
            case self::FILETYPE_FUNDER_DECISION:
                $doc = $company->funder_decision;
                break;
            case self::FILETYPE_FUNDER_SPEC:
                $doc = $company->funder_specification;
                break;
            case self::FILETYPE_FUND_SPEC:
                $doc = $company->funding_specification;
                break;
            case self::FILETYPE_SUPPORT_FILES:
                $doc = $company->support_files;
                break;
        }
        //var_dump($company->funding_specification);exit;
        //var_dump($company->personal_credit_reports);
        
        if($doc && is_string($doc_id)){
            if(is_array($doc)){
                $target_doc = null;
                foreach($doc as &$temp){
                    if($temp->id == $doc_id){
                        $target_doc = $temp;
                        break;
                    }
                }
                $doc = $target_doc;
            }
            else{
                if($doc->id != $doc_id){
                    $doc = null;
                }
            }
            
            if($doc){
                $doc->path = APPLICATION_PATH.'/../public'.$this->_bootstrap_options['file']['company_doc'].DIRECTORY_SEPARATOR.$doc->angelname;
            }
        }
        
        return $doc;
    }
    
    /**
     * 添加或修改担保人的信息
     * @param string $company_id
     * @param string $guarantor_id
     * @param array $data =  array('name'=>'', 'phone'=>'', 'email'=>, 'relationship'=>'')
     * @return Array
     * @throws Angel_Exception_Company 
     */
    public function addUpdateGuarantor($company_id, $guarantor_id, $data){
        $resultarr = array('result'=>false);
        
        $company = $this->getCompanyById($company_id);
        if($company){
            $guarantor = null;
            
            if(!empty($guarantor_id)){
                $guarantor = $company->getGuarantorCandidate($guarantor_id);
            }
            else{
                if(count($company->guarantor_candidate) < $this->_bootstrap_options['num']['guarantor']['candidate']){
                    $guarantor = $company->addGuarantorCandidate();
                }
                else{
                    throw new Angel_Exception_Company(Angel_Exception_Company::GUARANTOR_CANDIDATE_LIMITED);
                }
            }
            
            if($guarantor){
                if(isset($data['email']) && !$company->isValidGuarantorEmail($guarantor, $data['email'])){
                    throw new Angel_Exception_Company(Angel_Exception_Company::GUARANTOR_CANDIDATE_EMAIL_NOT_VALID);
                }
                
                foreach($data as $field=>$value){
                    $guarantor->$field = $value;
                }
                
                $resultarr->result = $guarantor->validateInfo();
                
                if(empty($resultarr->result)){
                    $this->_dm->persist($company);
                    $this->_dm->flush();

                    $resultarr['result'] = true;
                    $resultarr['id'] = strval($guarantor->id);
                }
                else{
                    throw new Angel_Exception_Company(Angel_Exception_Company::GUARANTOR_CANDIDATE_INFO_MISSED);
                }
            }
        }
        
        return $resultarr;
    }
    
    /**
     * 验证公司信息是否完整
     * @param string or \Documents\Company $company
     * @return array array('status'=>'', 'msg'=>'');
     * status 0 - 公司信息未通过验证，
     * status 1 - 验证通过
     * status 2 - 公司信息输入者未进行实名认证
     * status 3 - 公司信息有误
     */
    public function validateCompany($company){
        $result = array('status'=>0, 'msg'=>'');
        if(!($company instanceof \Documents\Company)){
            $company = $this->getCompanyById($company);
        }
        
        if($company){
            $owner = $company->owner;
            if(!$owner->isValidated() && !$owner->isInValidatedList()){
                // 公司创建者未通过实名认证，而且也没有提交实名认证信息
                $result['status'] = 2;
                $result['msg'] = $owner->username;
            }
            else{
                $fields = $company->getRequiredFields();
                $error_fields = array();
                $msgs = array();
                foreach($fields as $field=>$lable){
                    $value = $company->$field;
                    
                    if($field == 'logo'){
                        if(empty($value)){
                            $msgs[] = '你必须上传公司的标识';
                        }
                    }
                    else if($field == 'images'){
                        if(empty($value)){
                            $msgs[] = '你必须至少上传一张公司的图片';
                        }
                    }
                    else if($field == 'funding_specification'){
                        if(empty($value)){
                            $msgs[] = '你必须上传融资需求计划书';
                        }
                    }
                    else if($field == 'business_licence'){
                        if(empty($value)){
                            $msgs[] = '你必须上传营业执照';
                        }
                    }
                    else if($field == 'funder_specification'){
                        if(empty($value)){
                            $msgs[] = '你必须上传公司现有股东组成情况证明';
                        }
                    }
                    else if($field == 'funder_decision'){
                        if(empty($value)){
                            $msgs[] = '你必须上传股东会决议';
                        }
                    }
                    else if($field == 'credit_reports'){
                        if(empty($value)){
                            $msgs[] = '你必须至少上传一份公司信用报告';
                        }
                    }
                    else if($field == 'personal_credit_reports'){
                        if(empty($value)){
                            $msgs[] = '你必须至少上传一份融资人信用报告';
                        }
                    }
                    else if($field == 'financial_reports'){
                        if(empty($value)){
                            $msgs[] = '你必须至少上传一份财务报表';
                        }
                    }
                    else if($field == 'guarantor_candidate'){
                        $guarantor_counter = 0;
                        foreach($value as $candidate){
                            $tmp = $candidate->validateInfo();
                            if(empty($tmp)){
                                $guarantor_counter++;
                            }
                        }
                        if($guarantor_counter < 3){
                            $msgs[] = '你必须至少提供三位担保人的信息';
                        }
                    }
                    else{
                        if(empty($value)){
                            $error_fields[] = $lable;
                        }
                    }                    
                }
                if(!empty($error_fields)){
                    array_unshift($msgs, '你必须填写完整这些信息: '. implode(', ', $error_fields));
                }
                
                if(empty($msgs)){
                    $result['status'] = 1;
                    $result['msg'] = '公司信息完整';
                }
                else{
                    $result['status'] = 0;
                    $result['msg'] = $msgs;
                }
            }
        }
        else{
            $result['status'] = 3;
            $result['msg'] = '公司不存在';
        }
        
        return $result;
    }
    
    /**
     * 提交公司信息供审核
     * @param string $company_id 
     */
    public function submitCompany($company_id, $validate_company_info = true){
        $result = false;
        $validation_success = true;
        
        $company = $this->getCompanyById($company_id);        
        if($company){
            
            if($validate_company_info){
                $validation_result = $this->validateCompany($company);
                if($validation_result['status'] != 1){
                    $validation_success = false;
                }
            }

            if($validation_success){
                $company->wait_tobe_validate = true;

                $this->_dm->persist($company);
                $this->_dm->flush();
                $result = true;

                Angel_Model_Email::sendEmail($this->_container->get('email'), Angel_Model_Email::EMAIL_COMPANY_INFO_VALIDATE_ADMIN, $this->_bootstrap_options['mail']['admin'], array('name'=>$company->name));
            }
        }
        
        return $result;
    }
    
    /**
     * 验证公司ID是否有效，这个方法算很多方法的 preValidation
     * @param string $company_id
     * @return company model
     * @throws Angel_Exception_Company
     */
    public function validateCompanyId($company_id){
        $company = $this->getCompanyById($company_id);
        if(!$company){
            throw new Angel_Exception_User(Angel_Exception_Company::COMPANY_NOT_FOUND);
        }
        
        return $company;
    }
    
    /**
     * 返回等待信息需验证的公司
     * @return zend paginator or query dataset
     */
    public function getAllCompanyInWaitTobeValidatedList($return_as_paginator = true){
        $query = $this->_dm->createQueryBuilder($this->_document_class)
                           ->field('wait_tobe_validate')->equals(true)
                           ->field('active_bln')->equals(true)
                           ->sort('createdAt', 'asc');
        
        $result = null;
        if($return_as_paginator){
            $result = $this->paginator($query);
        }
        else{
            $result = $query->getQuery()->execute();
        }
        
        return $result;
    }
    
    /**
     * 审核拒绝公司信息认证
     * @param string $company_id
     * @param string $reason － 拒绝的原因
     * @return boolean - actually if no exception was thrown, it always return true
     * @throws Angel_Exception_Company
     * @throws Angel_Exception_Admin 
     */
    public function refuseCompanyInfo($company_id, $reason){
        $company = $this->validateCompanyId($company_id);
        
        // 必须提供拒绝的原因
        if(empty($reason)){
            throw new Angel_Exception_Admin(Angel_Exception_Admin::REFUSE_COMPANY_INFO_REASON_REQUIRED);
        }
        
        $company->wait_tobe_validate = false;
        $company->addCompanyRefusedReason($reason);
        
        $this->_dm->persist($company);
        $this->_dm->flush();
        
        $params = array(
                    'username' => $company->owner->username,
                    'name' => $company->name,
                    'reason' => $reason
                  );
        
        Angel_Model_Email::sendEmail($this->_container->get('email'), Angel_Model_Email::EMAIL_COMPANY_INFO_REFUSED, $company->owner->email, $params);
        
        return true;
    }
    
    /**
     * 审核通过公司信息认证
     * @param string $company_id
     * @return boolean - actually if no exception was thrown, it always return true
     */
    public function acceptCompanyInfo($company_id){
        $company = $this->validateCompanyId($company_id);
        $company->validated_bln = true;
        $company->wait_tobe_validate = false;
        
        foreach($company->guarantor_candidate as $candidate) {
            $this->emailGuarantorCandidate($company, $candidate);
        }
        
        $this->_dm->persist($company);
        $this->_dm->flush();

        $params = array(
                    'name' => $company->name,
                    'owner' => $company->owner->username
                  );
        Angel_Model_Email::sendEmail($this->_container->get('email'), Angel_Model_Email::EMAIL_COMPANY_INFO_ACCEPTED, $company->owner->email, $params);
        
        
        return true;
    }
    
    
    public function emailGuarantorCandidate($company, $candidate){
        $result = false;

        $token = crypt(md5(uniqid(time(), true)), $company->id);
        $token_param = array('company'=>$company->id, 'candidate' => $candidate->id, 'email'=>$candidate->email);
        $tokenModel = new Angel_Model_Token($this->_bootstrap);
        $token = $tokenModel->generateToken($token, $this->_bootstrap_options['token']['expiry']['guarantor_candidate'], Zend_Json::encode($token_param));

        if($token){
            $params = array();
            $params['guarantor'] = $candidate->name;
            $params['applicant'] = $company->owner->username;
            $params['company'] = $company->name;

            $router = Zend_Controller_Front::getInstance()->getRouter();
            $params['url'] = $this->_bootstrap_options['site']['domainurl'].$router->assemble(array('token'=>$token->token), 'guarantor-candidate');

            $subject = $company->owner->username.'申请你作为他公司的担保人';
            Angel_Model_Email::sendEmail($this->_container->get('email'), Angel_Model_Email::EMAIL_CANDIDATE_NOTICE, $candidate->email, $params, $subject);
            //Angel_Model_Email::sendEmail($this->_container->get('email'), Angel_Model_Email::EMAIL_CANDIDATE_NOTICE, $candidate->email, $params, $subject);
            $result = true;
        }

        return $result;
    }
    
        /**
     * 担保候选人拒绝担保邀请
     * @param user model $user
     * @param string $company_id
     * @param string $candidate_id
     * @param string $reason 
     */
    public function candidateRefuseInvitation($user, $company_id, $candidate_id, $reason=''){
        $result = false;
        
        $company = $this->getCompanyById($company_id);
        if($company){
            $candidate = $company->getGuarantorCandidate($candidate_id);
            if($candidate && ($candidate->email == $user->email)){
                $candidate->refused_bln = true;
                $candidate->refused_reason = $reason;
                
                $owner = $company->owner;
                $subject = $user->username.'拒绝了你的担保人邀请';
                $params = array();
                $params['name'] = $owner->username;
                $params['company'] = $company->name;
                $params['candidate'] = $user->username;
                $params['reason'] = $reason;
                $router = Zend_Controller_Front::getInstance()->getRouter();
                $params['url'] = $this->_bootstrap_options['site']['domainurl'].$router->assemble(array('company'=>$company_id, 'candidate'=>$candidate_id), 'update-candidate');
                Angel_Model_Email::sendEmail($this->_container->get('email'), Angel_Model_Email::CANDIDATE_REFUSE_INVITATION, $owner->email, $params, $subject);
                
                $this->_dm->persist($candidate);
                $this->_dm->flush();
                
                $result = true;
            }
        }
        
        return $result;
    }
    
    /**
     * 担保候选人接受邀请
     * @param user model $user
     * @param string $company_id
     * @param string $candidate_id
     * @return boolean 
     */
    public function candidateAcceptInvitation($user, $company_id, $candidate_id){
        $result = false;
        
        $company = $this->getCompanyById($company_id);
        if($company){
            $candidate = $company->getGuarantorCandidate($candidate_id);
            if($candidate && ($candidate->email == $user->email)){
                $user->addGuarantors($company, $candidate_id);
                $this->_dm->persist($user);
                
                $candidate->user = $user;
                if($user->isValidated()){
                    $candidate->accepted_bln = true;
                }
                
                if($candidate->accepted_bln){
                    $owner = $company->owner;
                    $subject = $user->username.'接受了你的担保人邀请';
                    Angel_Model_Email::sendEmail($this->_container->get('email'), Angel_Model_Email::CANDIDATE_ACCEPT_INVITATION, $owner->email, array('name'=>$owner->username, 'company'=>$company->name, 'candidate'=>$user->username), $subject);
                    
                    // 如果所有担保人接受邀请，将公司设置为“Ready”状态
                    $isReady = $company->allGuarantorCandidateAccepted();
                    if($isReady) {
                        $company->funding_status = \Documents\Company::FUNDING_STATUS_READY;
                    }
                }
                $this->_dm->persist($company);
                $this->_dm->flush();
                $result = true;
            }
        }
        
        return $result;
    }
    
    public function updateCandidate($user_id, $company_id, $candidate_id, $data){
        $result = false;
        
        $company = $this->getCompanyById($company_id);
        if($company && ($company->owner->id == $user_id)){
            $candidate = $company->getGuarantorCandidate($candidate_id);
            
            if($candidate && !$candidate->accepted_bln){
                $email_changed = false;
                if(isset($data['email']) && ($candidate->email != $data['email'])){
                    if($company->isValidGuarantorEmail($candidate, $data['email'])){
                        $email_changed = true;
                    }
                    else{
                        throw new Angel_Exception_Company(Angel_Exception_Company::GUARANTOR_CANDIDATE_EMAIL_NOT_VALID);
                    }
                }
                
                $clone_candidate = null;
                if($email_changed){
                    if($candidate->refused_bln){
                        $clone_candidate = clone $candidate;
                        $clone_candidate->id = new MongoId();

                        $candidate->refused_bln = false;
                        $candidate->refused_reason = '';
                    }
                    
                    $user = $candidate->user;
                    if($user){
                        $result = $user->removeCandidate($candidate->id);
                        
                        if(!$result){
                            throw new Angel_Exception_Company(Angel_Exception_Company::REVOKE_GUARANTOR_CANDIDATE_FAIL);
                        }
                        
                        $this->_dm->persist($user);
                        $candidate->user = null;
                    }
                }
            
                foreach($data as $field=>$value){
                    $candidate->$field = $value;
                }
                
                
                // 当公司信息已经审核通过后，更改担保人（email地址改变了），需要email通知新的担保候选人
                if($email_changed && $company->isValidated()){
                    $this->emailGuarantorCandidate($company, $candidate);
                    
                    // 把拒绝过的担保人加入“历史纪录”
                    if($clone_candidate){
                        $company->refused_guarantor_candidate[] = $clone_candidate;
                    }
                }
                $this->_dm->persist($company);
                
                $this->_dm->flush();
                $result = true;
            }
        }
        
        return $result;
    }
    
    
    /**
     * 投资公司
     * @param user document $user
     * @param string $company_id
     * @param array $contract
     * @param int $unit
     */
    public function investCompany($user, $company_id, $amount, $contract){
        $result = false;
        
//        if(!$user->isInvestor()){
//            throw new Angel_Exception_Company(Angel_Exception_Company::ONLY_INVESTOR_CAN_INVEST_COMPANY);
//        }
        
        $company = $this->getCompanyById($company_id);
        if($company){
            if($company->isInFunding() && $company->isValidated()){
                
                $tmp = $company->getLeftDays(false);
                if($tmp > 0){
                    if(!$user->hasAlreadyInvestedCompany($company_id)){
                        $amount = intval($amount);

                        if(empty($amount)){
                            // $unit is 0
                            throw new Angel_Exception_Company(Angel_Exception_Company::INVALID_INVEST_PERCENT);
                        }
//                        else if(!$company->isValidUnit($unit)){
//                            // 输入的投资额超过了天使圈规定的上线
//                            throw new Angel_Exception_Company(Angel_Exception_Company::INVEST_PERCENT_OVER_MAXIMUM);
//                        }
                        else {
                            //$price = $company->getOnePercentPrice();
                            $percent = ($amount / $company->getCompanyValueAfterInvesting()) * 100;
                            if(($company->funded_amt + $amount) > $company->expected_funding_amt){
                                $result = '你的投资将使得公司"'.$company->name.'"的融资金额超过融资目标';
                            }
                            else{
                                $user->addInvestedCompanies($company, $percent, $amount);
                                $this->_dm->persist($user);
                                
                                $company->addInvestor($user, $percent, $amount);
                                
                                // 如果超过融资目标，结束融资
                                if($company->funded_amt >= $company->expected_funding_amt) {
                                    $company->endFunding();
                                }
                                
                                $this->_dm->persist($company);

                                $fundStat = new \Documents\FundStat();
                                $fundStat->user = $user;
                                $fundStat->company = $company;
                                $fundStat->percent = $percent;
                                $fundStat->amount = $amount;
                                // 保存合同收件地址
                                $fundStat->contract_address = $contract['contract_address'];
                                $fundStat->contract_receiver = $contract['contract_receiver'];
                                $fundStat->contract_phone = $contract['contract_phone'];
                                $this->_dm->persist($fundStat);

                                $subject = '你的公司获得了￥'.$amount.'投资';
                                $params = array('name'=>$company->owner->username,
                                                'investor'=>$user->username,
                                                'amount'=>$amount,
                                                'percent'=>$percent.'%');


                                // Email通知公司信息创建者
                                Angel_Model_Email::sendEmail($this->_container->get('email'), Angel_Model_Email::COMPANY_GOT_FUND, $company->owner->email, $params, $subject);
                                
                                $this->_dm->flush();
                                $result = true;
                            }
                        }
                    }
                    else{
                        // 一个投资人只能对一个公司投资一次
                        throw new Angel_Exception_Company(Angel_Exception_Company::INVESTOER_FOR_ONE_COMPANY_ONLY_ONCE);
                    }
                }
                else{
                    // 公司已经过了融资阶段，不能接受投资
                    throw new Angel_Exception_Company(Angel_Exception_Company::COMPANY_ALREADY_STOP_FUNDING);
                }
            }
            else{
                // 公司没有处于融资阶段，不能接受投资
                throw new Angel_Exception_Company(Angel_Exception_Company::COMPANY_NOT_IN_FUNDING);
            }
        }
        
        return $result;
    }
    
    public function getFundStat($user_id, $company_id){
        $stat = $this->_dm->createQueryBuilder('\Documents\FundStat')
                          ->field('user.$id')->equals(new MongoId($user_id))
                          ->field('company.$id')->equals(new MongoId($company_id))
                          ->getQuery()
                          ->getSingleResult();
        
        return $stat;
    }

    public function triggerFunding($company_id){
        $result = false;
        
        $company = $this->getCompanyById($company_id);
        $email = $company->owner->email;
        if($company){
            $company->startFunding();

            //email 通知担保人，项目进入了融资阶段
            $guarantors = $company->guarantor_candidate;

            $subject = "公司".$company->name."已经启动来融资流程";
            $params = array('company'=>$company->name);
            foreach($guarantors as $guarantor){
                if($guarantor->user){
                    $params['name'] = $guarantor->user->username;
                    Angel_Model_Email::sendEmail($this->_container->get('email'), Angel_Model_Email::EMAIL_GUARANTOR_COMPANY_START_FUNDING, $guarantor->email, $params, $subject);
                }
            }

            //email 创业者，项目进入了融资阶段
            Angel_Model_Email::sendEmail($this->_container->get('email'), Angel_Model_Email::EMAIL_STARTUP_COMPANY_START_FUNDING, $email, $params, $subject);
            $this->_dm->persist($company);
            $this->_dm->flush();

            $result = true;
        }
        
        return $result;
    }
    
    
    /**
     * 获得所有就绪的公司
     */
    public function getCompanyInReady($return_as_paginator = false){
        return $this->getCompanyInStatus($return_as_paginator, \Documents\Company::FUNDING_STATUS_READY);
    }
        /**
     * 获得所有正在筹资的公司
     */
    public function getCompanyInRaising($return_as_paginator = false){
        return $this->getCompanyInStatus($return_as_paginator, \Documents\Company::FUNDING_STATUS_ING);
    }
    
       /**
     * 获得所有正在筹资的公司
     */
    public function getCompanyEnded($return_as_paginator = false){
        return $this->getCompanyInStatus($return_as_paginator, \Documents\Company::FUNDING_STATUS_END);
    }
    
    private function getCompanyInStatus($return_as_paginator = false, $status) {
        $query = $this->_dm->createQueryBuilder($this->_document_class)
                           ->field('funding_status')->equals($status)
                           ->sort('funding_enddate', 'asc');
        
        $result = null;
        if($return_as_paginator){
            $result = $this->paginator($query);
        }
        else{
            $result = $query->getQuery()->execute();
        }
        
        return $result;
    }
}
