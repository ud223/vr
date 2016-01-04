<?php

class Angel_View_Helper_ImagePath extends Zend_View_Helper_Abstract{
    
    private $_bootstrap;
    
    public function __construct() {
        $front = Zend_Controller_Front::getInstance();
        $this->_bootstrap = $front->getParam('bootstrap');
    }

    public function imagePath($filename){
        $options = $this->_bootstrap->getOptions();
        $path = $options['path']['image'].'/'.$filename.'?v='.$options['version']['image'];
        
        return $path;
    }
}
