<?php

class Angel_View_Helper_CssPath extends Zend_View_Helper_Abstract{
    
    private $_bootstrap;
    
    public function __construct() {
        $front = Zend_Controller_Front::getInstance();
        $this->_bootstrap = $front->getParam('bootstrap');
    }

    public function cssPath($filename){
        $options = $this->_bootstrap->getOptions();
        $path = $options['path']['css'].'/'.$filename.'?v='.$options['version']['css'];
        
        return $path;
    }
}
