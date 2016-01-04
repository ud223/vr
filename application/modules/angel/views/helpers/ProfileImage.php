<?php

class Angel_View_Helper_ProfileImage extends Zend_View_Helper_Abstract{
    
    private $_bootstrap;
    
    public function __construct() {
        $front = Zend_Controller_Front::getInstance();
        $this->_bootstrap = $front->getParam('bootstrap');
    }

    public function profileImage($profileImage, $version='normal'){
        $path = '';
        $options = $this->_bootstrap->getOptions();
        
        if(!empty($profileImage)){
            $user = new Angel_Model_User($this->_bootstrap);
            $path = $this->view->url(array('image'=>$user->getProfileImage($profileImage, $options['size'][$version])), 'profile-image');
        }
        else{
            $path = '/user/image/profile_'.$options['size'][$version].'.jpg';
        }
        
        return $path;
    }
}
