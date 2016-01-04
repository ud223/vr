<?php

class Angel_View_Helper_CssPath extends Zend_View_Helper_Abstract{
    
    private $_bootstrap;
    
    public function __construct() {
        $front = Zend_Controller_Front::getInstance();
        $this->_bootstrap = $front->getParam('bootstrap');
    }

    /**
     * 
     * @param string or \datetime $date
     * @return string 
     */
    public function localDate($date, $format='Y-m-d H:i:s'){
        $this->_container = $this->_bootstrap->getResource('serviceContainer');
        
        $date = $this->_container->get('util')->localDate($date);
        
        $result = '';
        if($date){
            $result = $date->format($format);
        }
        
        return $result;
    }
}
