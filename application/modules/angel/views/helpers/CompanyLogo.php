<?php

class Angel_View_Helper_CompanyLogo extends Zend_View_Helper_Abstract{
    
    private $_bootstrap;
    
    public function __construct() {
        $front = Zend_Controller_Front::getInstance();
        $this->_bootstrap = $front->getParam('bootstrap');
    }

    public function companyLogo($companylogo, $version='normal'){
        $path = '';
        $options = $this->_bootstrap->getOptions();
        
        if(!empty($companylogo)){
            $company = new Angel_Model_Company($this->_bootstrap);
            $path = $this->view->url(array('image'=>$company->getImageByVersion(Angel_Model_Company::IMAGETYPE_LOGO, $companylogo, $options['size'][$version])), 'company-logo');
        }
        else{
            $path = '/images/company_logo_'.$options['size'][$version].'.jpg';
        }
        
        return $path;
    }
}
