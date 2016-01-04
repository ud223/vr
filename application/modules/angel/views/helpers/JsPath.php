<?php

class Angel_View_Helper_JsPath extends Zend_View_Helper_Abstract{
    
    private $_bootstrap;
    
    public function __construct() {
        $front = Zend_Controller_Front::getInstance();
        $this->_bootstrap = $front->getParam('bootstrap');
    }

    public function jsPath($filename){
        $options = $this->_bootstrap->getOptions();
        $path = $options['path']['js'].'/'.$filename.'?v='.$options['version']['js'];
        
        return $path;
    }
}
