<?php

class Angel_View_Helper_PhotoImage extends Zend_View_Helper_Abstract{
    
    private $_bootstrap;
    
    public function __construct() {
        $front = Zend_Controller_Front::getInstance();
        $this->_bootstrap = $front->getParam('bootstrap');
    }
    
    /**
     * 
     * @param string $photoImage - the angelname of photo image
     * @param string $version
     * @return string
     */
    public function photoImage($photoImage, $version){
        $path = '';
        $options = $this->_bootstrap->getOptions();
        if(!empty($photoImage)){
            $photo = new Angel_Model_Photo($this->_bootstrap);
            $path = $this->view->url(array('image'=>$photo->getPhotoByVersion($photoImage, $options['size'][$version])), 'manage-photo-image');
        }
        else{
            $path = '/photo/image/default_'.$options['size'][$version].'.jpg';
        }
        
        return $path;
    }
}
