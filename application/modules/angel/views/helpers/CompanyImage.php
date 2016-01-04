<?php

class Angel_View_Helper_CompanyImage extends Zend_View_Helper_Abstract{
    
    private $_bootstrap;
    
    public function __construct() {
        $front = Zend_Controller_Front::getInstance();
        $this->_bootstrap = $front->getParam('bootstrap');
    }
    
    /**
     * 
     * @param string $companyimage - the angelname of companydoc
     * @param string $version
     * @return string
     */
    public function companyImage($companyimage, $version='normal'){
        $path = '';
        $options = $this->_bootstrap->getOptions();
        
        if(!empty($companyimage)){
            $company = new Angel_Model_Company($this->_bootstrap);
            $path = $this->view->url(array('image'=>$company->getImageByVersion(Angel_Model_Company::IMAGETYPE_IMAGE, $companyimage, $options['size'][$version])), 'company-image');
        }
        else{
            $path = '/images/company_image_'.$options['size'][$version].'.jpg';
        }
        
        return $path;
    }
}
