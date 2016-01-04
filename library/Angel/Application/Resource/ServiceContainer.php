<?php
/**
 * Description of MongoDocumentManager
 *
 * @author powerdream5
 */

use Symfony\Component\DependencyInjection;

class Angel_Application_Resource_ServiceContainer extends Zend_Application_Resource_ResourceAbstract{
    
    protected $_container;
    
    // 不要命名为$_options, 因为那样就覆盖了父类中的$_options
    protected $_bootstrap_options;
    
    // angel module bootstrap
    protected $_angel_bootstrap;

    /**
     * 
     */
    public function init(){
        $this->_container = new DependencyInjection\ContainerBuilder();
        
        $bootstrap = $this->getBootstrap();
        $this->_bootstrap_options = $bootstrap->getOptions();
        $this->_angel_bootstrap = $bootstrap->getResource('modules')->offsetGet('angel');
        
        $this->registerService();
        return $this->_container;
    }
    
    protected function registerService(){

        $this->_container->register('util', 'Angel_Service_Util')
                            ->addArgument($this->_bootstrap_options);
        
        $this->_container->register('email', 'Angel_Service_Email')
                            ->addArgument($this->_bootstrap_options);
        
        $this->_container->register('image', 'Angel_Service_Image')
                            ->addArgument($this->_bootstrap_options)
                            ->addArgument($this->_container->get('util'));
        
        $this->_container->register('file', 'Angel_Service_File')
                            ->addArgument($this->_bootstrap_options);
        
        $this->_container->register('oss', 'Angel_Service_Oss')
                            ->addArgument($this->_bootstrap_options);
    }
}

?>
