<?php

namespace Angel\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AngelCommand extends Command{
    
    protected $_application = null;
    protected $_bootstrap = null;
    protected $_angel_bootstrap = null;
    protected $_container = null;
    protected $_documentManager = null;
    protected $_logger = null;

    public function __construct(\Zend_Application $application){
        parent::__construct();
        
        $this->_application = $application;
        $this->_bootstrap = $application->getBootstrap();
        
        $this->_angel_bootstrap = $this->_bootstrap->getResource('modules')->offsetGet('angel');
        $this->_container = $this->_bootstrap->getResource('serviceContainer');
        $this->_documentManager = $this->_angel_bootstrap->getResource('mongoDocumentManager');
        $this->_logger = $this->_bootstrap->getResource('logger');
    }
}
