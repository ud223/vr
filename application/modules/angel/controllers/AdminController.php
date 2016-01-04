<?php

class Angel_AdminController extends Angel_Controller_Action
{
    public function init()
    {
        parent::init();
        
        $this->_helper->layout->setLayout('admin');
    }
    public function personalInfoListAction() {
        $userModel = $this->getModel('user');
        $paginator = $userModel->getAllUsersInWaitTobeValidatedList();
        $paginator->setItemCountPerPage(100);
        $paginator->setCurrentPageNumber(1);
        $this->view->users = $paginator;
        $this->view->userModel = $userModel;
        $this->view->type_identity_front = \Angel_Model_User::FILETYPE_IDENTITY_FRONT;
        $this->view->type_identify_back = \Angel_Model_User::FILETYPE_IDENTITY_BACK;
    }
    
    public function companyInfoListAction() {
        $companyModel = $this->getModel('company');
        $paginator = $companyModel->getAllCompanyInWaitTobeValidatedList();
        $paginator->setItemCountPerPage(100);
        $paginator->setCurrentPageNumber(1);
        $this->view->companies = $paginator;
        $this->view->companyModel = $companyModel;
//        $this->view->type_funding_specification = \Angel_Model_Company::FILETYPE_FUND_SPEC;
//        $this->view->type_business_licence = \Angel_Model_Company::FILETYPE_BUSINESS_LICENCE;
//        $this->view->type_funder_specification = \Angel_Model_Company::FILETYPE_FUNDER_SPEC;
//        $this->view->type_funder_decision = \Angel_Model_Company::FILETYPE_FUNDER_DECISION;
//        $this->view->type_credit_reports = \Angel_Model_Company::FILETYPE_CREDIT_REPORTS;
//        $this->view->type_personal_credit_reports = \Angel_Model_Company::FILETYPE_PERSONAL_CREDIT_REPORTS;
//        $this->view->type_financial_reports = \Angel_Model_Company::FILETYPE_FINANCIAL_REPORT;
//        $this->view->type_support_files = \Angel_Model_Company::FILETYPE_SUPPORT_FILES;
    }
    
    public function readyCompanyInfoListAction() {
        $companyModel = $this->getModel('company');
        $paginator = $companyModel->getCompanyInReady(true);
        
        $paginator->setItemCountPerPage(100);
        $paginator->setCurrentPageNumber(1);
        $this->view->companies = $paginator;
    }
    
    public function refusedIdentityInfoAction() {
        if($this->request->isPost()){
            $userModel = $this->getModel('user');
            $user_id = $this->request->getParam('user_id');
            $reason = $this->request->getParam('reason');
            $result = $userModel->refuseIdentityInfo($user_id, $reason);

            $this->_helper->json($result ? 1 : 0);
        }
    }
    
    public function acceptIdentityInfoAction() {
        if($this->request->isPost()){
            $userModel = $this->getModel('user');
            $user_id = $this->request->getParam('user_id');
            $result = $userModel->acceptIdentityInfo($user_id);

            $this->_helper->json($result ? 1 : 0);
        }
    }
    
    public function refusedCompanyInfoAction() {
        if($this->request->isPost()){
            $companyModel = $this->getModel('company');
            $company_id = $this->request->getParam('company_id');
            $reason = $this->request->getParam('reason');
            $result = $companyModel->refuseCompanyInfo($company_id, $reason);

            $this->_helper->json($result ? 1 : 0);
        }
    }
    
    public function acceptCompanyInfoAction() {
        if($this->request->isPost()) {
            $companyModel = $this->getModel('company');
            $company_id = $this->request->getParam('company_id');
            $result = $companyModel->acceptCompanyInfo($company_id);
            
            $this->_helper->json($result ? 1 : 0);
        }
    }
    
    public function triggerFundingAction(){
        $company_id = $this->request->getParam('id');
        
        $result = false;
        try{
            $result = $this->getModel('company')->triggerFunding($company_id);
        }
        catch(Angel_Exception_Common $e){
            $this->_logger->info(__CLASS__, __FUNCTION__, $e->getMessage()."\n".$e->getTraceAsString());
        }
        
        $this->_helper->json(($result===true) ? 1 : 0);
    }
}