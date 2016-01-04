<?php

class AngelTestAbstract extends PHPUnit_Framework_Testcase{
    
    protected $_bootstrap;
    
    protected $_angel_bootstrap;

    protected $_container;
    
    protected $_documentManager;
    
    protected $_logger;

    public function setUp(){
        parent::setUp();
        
        $config = new \Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', null, true);
        $system_ini = new \Zend_Config_Ini('/var/angelhere.ini');
        $config->merge($system_ini);
        
        $application = new Zend_Application(
                            APPLICATION_ENV,
                            $config->get(APPLICATION_ENV)
                        );        
        $application->bootstrap();
        
        $this->_bootstrap = $application->getBootstrap();
        
        // manually init Zend_Controller_Front, otherwise, it is not inited in testing environment 
        $this->_bootstrap->getResource('FrontController')->setParam('bootstrap', $this->_bootstrap);
        
        $this->_angel_bootstrap = $this->_bootstrap->getResource('modules')->offsetGet('angel');
        $this->_container = $this->_bootstrap->getResource('serviceContainer');
        $this->_documentManager = $this->_angel_bootstrap->getResource('mongoDocumentManager');
        $this->_logger = $this->_bootstrap->getResource('logger');
    }
    
}
