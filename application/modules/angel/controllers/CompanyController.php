<?php

class Angel_CompanyController extends Angel_Controller_Action
{
    public function init()
    {
        parent::init();
    }
    
    protected $login_not_required = array('guarantor-candidate');
    
    public function preDispatch() {
        parent::preDispatch();
    }
    
    public function companyCreateAction(){
        if($this->request->isPost()){
            $user = $this->me->getUser();
            $companyModel = $this->getModel('company');
            $result = $companyModel->createCompany($user);
            echo $this->view->url(array('id'=>$result->id), 'company-info');
            exit;
        }
    }
    public function companyCheckoutAction() {
        $companyId = $this->_request->getParam('id');
        $companyModel = $this->getModel('company');
        $company = $companyModel->getCompanyById($companyId);
        $this->view->company = $company;
        $this->view->logoUrl = $this->view->url(array('image'=>$companyModel->getCompanyLogo($company->logo, $this->bootstrap_options['size']['medium'])), 'company-logo');
        
        $calResult = $this->calculateFundingStock($company);
        $this->view->total = $calResult['total'];
        $this->view->perv_total = $calResult['perv_total'];
        $this->view->funding_mini_unit_perc = $calResult['funding_mini_unit_perc'];
        $this->view->owner = $company->owner;

        $pass = true;
        if($_COOKIE['investcompany'] != $companyId) {
            $pass = false;
        }
        $amount = $_COOKIE['investamount'];
        if(!is_numeric($amount)) {
            $pass = false;
        }
        if(!$pass) {
            $this->_redirect($this->view->url(array(), 'not-found'));
        }
        
        $this->view->amount = $amount;
        $this->view->perc = ($amount / $company->getCompanyValueAfterInvesting()) * 100;
    }
    public function companyCheckoutPayAction(){
        $companyId = $this->_request->getParam('id');
        $companyModel = $this->getModel('company');
        $company = $companyModel->getCompanyById($companyId);
        $this->view->company = $company;
        $this->view->logoUrl = $this->view->url(array('image'=>$companyModel->getCompanyLogo($company->logo, $this->bootstrap_options['size']['medium'])), 'company-logo');
        
        $calResult = $this->calculateFundingStock($company);
        $this->view->total = $calResult['total'];
        $this->view->perv_total = $calResult['perv_total'];
        $this->view->funding_mini_unit_perc = $calResult['funding_mini_unit_perc'];
        $this->view->owner = $company->owner;

        $pass = true;
        if($_COOKIE['investcompany'] != $companyId) {
            $pass = false;
        }
        $amount = $_COOKIE['investamount'];
        if(!is_numeric($amount)) {
            $pass = false;
        }
        
        $this->view->contract_receiver = $_COOKIE['contract_receiver'];
        $this->view->contract_phone = $_COOKIE['contract_phone'];
        $this->view->contract_address = $_COOKIE['contract_address'];
        
        if(!$pass) {
            $this->_redirect($this->view->url(array(), 'not-found'));
        }
        $this->view->amount = $amount;
        $this->view->perc = ($amount / $company->getCompanyValueAfterInvesting()) * 100;
    }
    
    public function companyCheckoutSuccessAction() {
        $companyId = $this->_request->getParam('id');
        $companyModel = $this->getModel('company');
        $company = $companyModel->getCompanyById($companyId);
        $this->view->company = $company;
    }
    public function companyListAction() {
        $companyModel = $this->getModel('company');
        $list = $companyModel->getCompanyInRaising(true);
        $this->view->list = $list;
    }
    public function companyDetailAction(){
        $companyId = $this->_request->getParam('id');
        $companyModel = $this->getModel('company');
        $company = $companyModel->getCompanyById($companyId);
        $this->view->company = $company;
        $this->view->logoUrl = $this->view->url(array('image'=>$companyModel->getCompanyLogo($company->logo, $this->bootstrap_options['size']['medium'])), 'company-logo');
        
        $calResult = $this->calculateFundingStock($company);
        $this->view->total = $calResult['total'];
        $this->view->perv_total = $calResult['perv_total'];
        $this->view->funding_mini_unit_perc = $calResult['funding_mini_unit_perc'];
        $this->view->owner = $company->owner;
        
        // 融资需求
        $this->view->type_fund_spec = \Angel_Model_Company::FILETYPE_FUND_SPEC;
        // 营业执照
        $this->view->type_business_licence = \Angel_Model_Company::FILETYPE_BUSINESS_LICENCE;
        // 公司现有股东组成情况证明
        $this->view->type_funder_spec = \Angel_Model_Company::FILETYPE_FUNDER_SPEC;
        // 股东会决议
        $this->view->type_funder_decision = \Angel_Model_Company::FILETYPE_FUNDER_DECISION;
        // 公司信用报告
        $this->view->type_credit_report = \Angel_Model_Company::FILETYPE_CREDIT_REPORTS;
        // 融资人信用报告
        $this->view->type_personal_credit_report = \Angel_Model_Company::FILETYPE_PERSONAL_CREDIT_REPORTS;
        // 公司财务报表
        $this->view->type_financial_report = \Angel_Model_Company::FILETYPE_FINANCIAL_REPORT;
        // 其它支持文件
        $this->view->type_support_files = \Angel_Model_Company::FILETYPE_SUPPORT_FILES;
        
    }

    public function companyInfoAction(){
        $companyId = $this->_request->getParam('id');
        $companyModel = $this->getModel('company');
        $company = $companyModel->getCompanyById($companyId);

        if($this->request->isXmlHttpRequest() && $this->request->isPost()){
           
            $validation = $this->request->getParam('validation');
            if ($validation == 0) {
                // update
                $key = $this->request->getParam('key');
                $val = $this->request->getParam('val');
                
                switch ($key) {
                    case "expected_funding_amt": 
                        $val = $company->setExpectedFundingAmt($val);
                        break;
                    case "funding_mini_unit":
                        $val = $company->setFundingMiniUnit($val);
                        break;
                }
                $result = $companyModel->updateCompany($companyId, array($key=>$val));
                $this->_helper->json($result ? 1 : 0);
            } else {
                // submit
            }
        } else {
            // 融资需求
            $this->view->type_fund_spec = \Angel_Model_Company::FILETYPE_FUND_SPEC;
            // 营业执照
            $this->view->type_business_licence = \Angel_Model_Company::FILETYPE_BUSINESS_LICENCE;
            // 公司现有股东组成情况证明
            $this->view->type_funder_spec = \Angel_Model_Company::FILETYPE_FUNDER_SPEC;
            // 股东会决议
            $this->view->type_funder_decision = \Angel_Model_Company::FILETYPE_FUNDER_DECISION;
            // 公司信用报告
            $this->view->type_credit_report = \Angel_Model_Company::FILETYPE_CREDIT_REPORTS;
            // 公司融资人信用报告
            $this->view->type_personal_credit_report = \Angel_Model_Company::FILETYPE_PERSONAL_CREDIT_REPORTS;
            // 公司财务报表
            $this->view->type_financial_report = \Angel_Model_Company::FILETYPE_FINANCIAL_REPORT;
            // 其它支持文件
            $this->view->type_support_files = \Angel_Model_Company::FILETYPE_SUPPORT_FILES;

            $this->view->company = $company;
            $this->view->id = $companyId;
            $this->view->logoUrl = $this->view->url(array('image'=>$companyModel->getCompanyLogo($company->logo, $this->bootstrap_options['size']['medium'])), 'company-logo');
        }
    }
    
    public function companyThumbnailAction(){
        $this->_helper->layout->disableLayout();
        $company_id = $this->_request->getParam('company_id');
        $result = 0;
        if($this->request->isPost()){
            $upload = new Zend_File_Transfer();
            
            $upload->addValidator('Size', false, 5120000); //5M
            
            $utilService = $this->_container->get('util');
            $destination = $utilService->getTmpDirectory().'/'.uniqid();
            
            $upload->addFilter('Rename', $destination);
            
            if($upload->isValid()){
                if($upload->receive()){
                    $companyModel = $this->getModel('company');
                    try{
                        $company = $companyModel->getCompanyById($company_id);
                        if($company){
                            $result = $companyModel->addCompanyLogo($company, $destination);
                            if($result){
                                $result = 1;
                                $this->view->path = $this->view->url(array('image'=>$company->logo), 'company-logo');
                            }
                        }
                    }
                    catch(Exception $e){
                        // image is not accepted
                        $result = 2;
                    }
                }
            }
        }        
        $this->view->company_id = $company_id;
        $this->view->result = $result;
    }
    
    public function cropCompanyLogoAction(){
        $companyModel = $this->getModel('company');
        $company = $companyModel->getCompanyById($this->_request->getParam('company_id'));
        
        $orig = $companyModel->getImagePath(\Angel_Model_Company::IMAGETYPE_LOGO, $company->logo);

        $x = $this->request->getParam('x', 0);
        $y = $this->request->getParam('y', 0);
        $w = $this->request->getParam('w', 180);
        $h = $this->request->getParam('h', 180);
        
        $coord = array($x, $y, $w, $h);
        $companyModel->generateCompanyLogo($orig, $coord);

        $imageurl = $this->view->url(array('image'=>$companyModel->getCompanyLogo($company->logo, $this->bootstrap_options['size']['medium'])), 'company-logo');
        
        $this->_helper->json(array('image'=>$imageurl));
    }
    
    public function uploadCompanyImageAction(){
        $this->_helper->layout->disableLayout();
        $company_id = $this->_request->getParam('company_id');
        $result = 0;
        if($this->request->isPost()){
            $upload = new Zend_File_Transfer();
            
            $upload->addValidator('Size', false, 5120000); //5M
            
            $utilService = $this->_container->get('util');
            $destination = $utilService->getTmpDirectory().'/'.uniqid();
            
            $upload->addFilter('Rename', $destination);
            
            if($upload->isValid()){
                if($upload->receive()){
                    
                    $companyModel = $this->getModel('company');
                    try{
                        $company = $companyModel->getCompanyById($company_id);
                        if($company){
                            $result = $companyModel->addCompanyImage($company, $destination);
                            if($result){
                                $this->view->doc_id = $result->id;
                                $this->view->path = $this->view->companyImage($result->angelname, 'medium');
                                $this->view->orig = $this->view->companyImage($result->angelname, 'orig');
                                $result = 1;
                            }
                        }
                    }
                    catch(Exception $e){
                        // image is not accepted
                        $result = 2;
                    }
                }
            }
        }
        $this->view->company_id = $company_id;
        $this->view->result = $result;
    }
    
    /**
     * 上传公司文件的action 
     */
    public function uploadCompanyDocAction(){
        $this->_helper->layout->disableLayout();
        $doctype = $this->request->getParam('doctype');
        $this->view->doctype = $doctype;
        
        $result = 0;
        if($this->request->isPost()){
            $upload = new Zend_File_Transfer();
            
            $upload->addValidator('Size', false, 10240000); //10M
            
            $utilService = $this->_container->get('util');
            $fileService = $this->_container->get('file');
            
            $filename = $utilService->getFilename($upload->getFileName());
            $extension = $fileService->getExtensionByFilename($filename);
            
            $destination = $utilService->getTmpDirectory().DIRECTORY_SEPARATOR.uniqid();
            
            $upload->addFilter('Rename', $destination);
            
            if($upload->isValid()){
                if($upload->receive()){
                    $companyModel = $this->getModel('company');
                    
                    $mimetype = $upload->getMimeType();
                    if($fileService->isAcceptedDocument($mimetype, $extension)){
                        $company_id = $this->_request->getParam('company_id');
                        $company = $companyModel->getCompanyById($company_id);
                        if($company){
                            $doc = null;
                            
                            if($companyModel->isValidDoctype($doctype)){
                                $doc = $companyModel->addCompanyDoc($company, $doctype, $destination, $filename, $mimetype);
                            }
                            
                            if($doc){
                                $result = 1;
                                $this->view->doc_id = $doc->id;
                                $this->view->filename = $doc->filename;
                                $this->view->path = $this->view->url(array('doctype'=>$doctype, 'company_id'=>$company_id, 'doc_id'=>$doc->id), 'company-doc');
                            }
                        }
                    }
                    else{
                        // 上传的文件格式不接受
                        $result = 2;
                    }
                }
            }
        }
        
        $this->view->result = $result;
    }
    
    /**
     * 当用户查看公司的文档 
     */
    public function companyDocAction(){
        
        $doctype = $this->request->getParam('doctype');
        $company_id = $this->request->getParam('company_id');
        $doc_id = $this->request->getParam('doc_id');
        
        $doc = $this->getModel('company')->getCompanyDoc($company_id, $doctype, $doc_id);
        if($doc && ($fd = fopen($doc->path, 'r'))){
            $filepath = $doc->path;
            $filename = $doc->filename;
            
            $this->outputFile($fd, $filepath, $filename);
            
            fclose($fd);
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
        }
        else{
            $this->_redirect($this->view->url(array(), 'not-found'));
        }
    }
    
    /**
     * 删除公司文档
     */
    public function removeCompanyDocAction(){
        if($this->request->isPost()){
            $company_id = $this->request->getParam('company_id');
            $doctype = $this->request->getParam('doctype');
            $doc_id = $this->request->getParam('doc_id');
            $companyModel = $this->getModel('company');
            $company = $companyModel->getCompanyById($company_id);
            $result = $companyModel->removeCompanyDoc($company, $doctype, $doc_id);

            $this->_helper->json($result ? 1 : 0);
        }
    }
    
    /**
     * 删除公司图片
     */
    public function removeCompanyImageAction(){
        if($this->request->isPost()){
            $company_id = $this->request->getParam('company_id');
            $doc_id = $this->request->getParam('doc_id');
            $companyModel = $this->getModel('company');
            $company = $companyModel->getCompanyById($company_id);
            $result = $companyModel->removeCompanyImage($company, $doc_id);
            $this->_helper->json($result ? 1 : 0);
        }
    }
    
    /*
     * 保存担保人信息（guarantor_id为空时，添加担保人）
     */
    public function saveGuaranteeAction(){
        if($this->request->isPost()){
            $company_id = $this->request->getParam('company_id');
            $guarantor_id = $this->request->getParam('guarantor_id');
            $name = $this->request->getParam('name');
            $phone = $this->request->getParam('phone');
            $email = $this->request->getParam('email');
            $relationship = $this->request->getParam('relationship');
            $data = array('name'=>$name, 'phone'=>$phone, 'email'=>$email, 'relationship'=>$relationship);
            
            $companyModel = $this->getModel('company');
            $result = $companyModel->addUpdateGuarantor($company_id, $guarantor_id, $data);
            
            $this->_helper->json($result);
        }
    }
    
    public function submitCompanyAction() {
        if($this->request->isPost()){
            $company_id = $this->request->getParam('company_id');
            $companyModel = $this->getModel('company');
            $result = $companyModel->submitCompany($company_id);
            $this->_helper->json($result);
        }
    }
    
    public function getGuaranteesByCompanyIdAction() {
        if($this->request->isPost()){
            $company_id = $this->request->getParam('company_id');
            $companyModel = $this->getModel('company');
            $company = $companyModel->getCompanyById($company_id);
            $guarantors = $company->guarantor_candidate;
            
            $result = array();
            foreach($guarantors as $guarantor) {
                array_push($result, array('id'=>$guarantor->id, 'name'=>$guarantor->name, 'phone'=>$guarantor->phone, 'email'=>$guarantor->email, 'relationship'=>$guarantor->relationship));
            }
            $this->_helper->json($result);
        }
    }
    
    public function unitPercAction() {
        if($this->request->isPost()){
            $company_id = $this->request->getParam('company_id');
            $companyModel = $this->getModel('company');
            $company = $companyModel->getCompanyById($company_id);
            $result = $this->calculateFundingStock($company);
            if(count(result) == 0) {
                $result = false;
            }
            $this->_helper->json($result);
        }
    }
    
    public function getInvestPercAction(){
        if($this->request->isPost()){
            $company_id = $this->request->getParam('company_id');
            $invest_amount = $this->request->getParam('invest_amount');
            $companyModel = $this->getModel('company');
            $company = $companyModel->getCompanyById($company_id);
            $total = $company->getCompanyValueAfterInvesting();
            $result = $funding_mini_unit_perc = ($invest_amount / $total) * 100;
            $this->_helper->json($result);
        }
    }
    
    private function calculateFundingStock($company){
        $final_total = $company->getCompanyValueAfterInvesting();
        $prev_total = $company->getCompanyValueBeforeInvesting();
        $funding_mini_unit_perc = ($company->funding_mini_unit / $final_total) * 100;
        $result = array('total'=>$final_total, 'prev_total'=>$prev_total, 'funding_mini_unit_perc'=>$funding_mini_unit_perc);
        return $result;
    }
    
    public function guarantorCandidateAction(){
        $token = $this->request->getParam('token');
        $companyModel = $this->getModel('company');
        $tokenDocument = $this->getModel('token')->getTokenByToken($token);
        if($tokenDocument && !$tokenDocument->isExpired()){
            $company_id = $tokenDocument->params['company'];
            $candidate_id = $tokenDocument->params['candidate'];
            
            $company = $companyModel->getCompanyById($company_id);
            if($company){
                $candidate = $company->getGuarantorCandidate($candidate_id);
                if($candidate){
                    if($candidate->email != $tokenDocument->params['email']){
                        $candidate = null;
                    }
                    else{
                        if($this->me){
                            $user = $this->me->getUser();
                            if($user->email != $candidate->email){
                                $this->_redirect($this->view->url(array(), 'not-found'));
                            }
                        }
                    }
                    $this->view->token = $token;
                    $this->view->company = $company;
                    $this->view->candidate = $candidate;
                    $this->view->logoUrl = $this->view->url(array('image'=>$companyModel->getCompanyLogo($company->logo, $this->bootstrap_options['size']['medium'])), 'company-logo');
                    
                    $calResult = $this->calculateFundingStock($company);
                    $this->view->total = $calResult['total'];
                    $this->view->perv_total = $calResult['perv_total'];
                    $this->view->funding_mini_unit_perc = $calResult['funding_mini_unit_perc'];
                    
                    // 融资需求
                    $this->view->type_fund_spec = \Angel_Model_Company::FILETYPE_FUND_SPEC;
                    // 营业执照
                    $this->view->type_business_licence = \Angel_Model_Company::FILETYPE_BUSINESS_LICENCE;
                    // 公司现有股东组成情况证明
                    $this->view->type_funder_spec = \Angel_Model_Company::FILETYPE_FUNDER_SPEC;
                    // 股东会决议
                    $this->view->type_funder_decision = \Angel_Model_Company::FILETYPE_FUNDER_DECISION;
                    // 公司信用报告
                    $this->view->type_credit_report = \Angel_Model_Company::FILETYPE_CREDIT_REPORTS;
                    // 公司融资人信用报告
                    $this->view->type_personal_credit_report = \Angel_Model_Company::FILETYPE_PERSONAL_CREDIT_REPORTS;
                    // 公司财务报表
                    $this->view->type_financial_report = \Angel_Model_Company::FILETYPE_FINANCIAL_REPORT;
                    // 其它支持文件
                    $this->view->type_support_files = \Angel_Model_Company::FILETYPE_SUPPORT_FILES;

                    
                }
                else{
                    $this->_redirect($this->view->url(array(), 'not-found'));
                }
            }
            else{
                $this->_redirect($this->view->url(array(), 'not-found'));
            }
        }
        else{
            $this->_redirect($this->view->url(array(), 'not-found'));
        }
    }
    
    public function candidateRefuseInvitationAction() {
        if($this->request->isPost()){
            $user = $this->me->getUser();
            $companyModel = $this->getModel('company');
            $company_id = $this->_request->getParam('company');
            $candidate_id = $this->_request->getParam('candidate');
            $reason = $this->_request->getParam('reason');
            $result = $companyModel->candidateRefuseInvitation($user, $company_id, $candidate_id, $reason);
            $this->_helper->json($result ? 1 : 0);
        }
    }
    
    public function candidateAcceptInvitationAction() {
        if($this->request->isPost()){
            $user = $this->me->getUser();
            $companyModel = $this->getModel('company');
            $company_id = $this->_request->getParam('company');
            $candidate_id = $this->_request->getParam('candidate');
            $result = $companyModel->candidateAcceptInvitation($user, $company_id, $candidate_id);
            $this->_helper->json($result ? 1 : 0);
        }
    }
    
    public function updateCandidateAction(){
        $companyModel = $this->getModel('company');
        $company_id = $this->_request->getParam('company');
        $candidate_id = $this->_request->getParam('candidate');
        $this->view->company_id = $company_id;
        $this->view->candidate_id = $candidate_id;
        if($this->request->isPost()){
            $user = $this->me->getUser();
            $name = $this->_request->getParam('name');
            $phone = $this->_request->getParam('phone');
            $email = $this->_request->getParam('email');
            $relationship = $this->_request->getParam('relationship');
            $data = array('name'=>$name, 'email'=>$email, 'phone'=>$phone, 'relationship'=>$relationship);
            $result = $companyModel->updateCandidate($user->id, $company_id, $candidate_id, $data);
            $this->_helper->json($result ? 1 : 0);
        }
    }
    
    public function investCompanyAction(){
        if($this->request->isPost()){
            $company_id = $this->_request->getParam('company_id');
            $amount = intval($_COOKIE["investamount"]);
            if($amount > 0) {
                $user = $this->me->getUser();
                $companyModel = $this->getModel('company');
                $contract_receiver = $_COOKIE['contract_receiver'];
                $contract_phone = $_COOKIE['contract_phone'];
                $contract_address = $_COOKIE['contract_address'];
                $contract = array('contract_receiver'=>$contract_receiver, 'contract_phone'=>$contract_phone, 'contract_address'=>$contract_address);
                $result = $companyModel->investCompany($user, $company_id, $amount, $contract);
            } else {
                $result = false;
            }
            $this->_helper->json($result ? 1 : 0);
            
        }
    }
}