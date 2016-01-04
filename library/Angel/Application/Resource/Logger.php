<?php
/**
 * The logger resource
 *
 * @author powerdream5
 */
class Angel_Application_Resource_Logger extends Zend_Application_Resource_ResourceAbstract{
    
    protected $_logger;
    
    // 不要命名为$_options, 因为那样就覆盖了父类中的$_options
    protected $_bootstrap_options;
    
    // angel module bootstrap
    protected $_angel_bootstrap;

    /**
     * 
     */
    public function init(){
        $adapter = null;
        
        $bootstrap = $this->getBootstrap();
        $this->_bootstrap_options = $bootstrap->getOptions();
        
        if(APPLICATION_ENV == 'production'){
            $adapter = new Angel_Log_Writer_Mongo($this->_options['server'], $this->_options['dbname']);
        }
        else{
            $adapter = new Zend_Log_Writer_Stream($this->_bootstrap_options['path']['logger']);
        }
        
        if($adapter){
            $this->_logger = new Zend_Log($adapter);
        }
        else{
            echo 'System logger file is not configured. environment: '.APPLICATION_ENV;
            exit;
        }
        
        return $this;
    }
    
    public function info($class, $func, $msg){
        $this->_logger->setEventItem('class', $class);
        $this->_logger->setEventItem('class', $func);
        $this->_logger->info($msg);
    }
    
}

?>
