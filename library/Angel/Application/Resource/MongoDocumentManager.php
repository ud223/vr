<?php
/**
 * Description of MongoDocumentManager
 *
 * @author powerdream5
 */
require APPLICATION_PATH.'/../library/Doctrine/Common/ClassLoader.php'; 

use Doctrine\Common\ClassLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

class Angel_Application_Resource_MongoDocumentManager extends Zend_Application_Resource_ResourceAbstract{
    
    protected $_documentManager;

    /**
     * 
     */
    public function init(){
        return $this->getDocumentManager();
    }
    
    protected function getDocumentManager(){
        if(is_null($this->_documentManager)){
            $options = $this->getOptions();
            
            // ODM Class
            $classLoader = new ClassLoader('Doctrine\ODM\MongoDB', APPLICATION_PATH.'/../library');
            $classLoader->register();

            // Common Class
            $classLoader = new ClassLoader('Doctrine\Common', APPLICATION_PATH.'/../library');
            $classLoader->register();
            
            // MongoDB Class
            $classLoader = new ClassLoader('Doctrine\MongoDB', APPLICATION_PATH.'/../library');
            $classLoader->register();
            
            $classLoader = new ClassLoader('Documents', $options['documentPath']);
            $classLoader->register();
            
            $config = new Configuration();
            $config->setProxyDir($options['proxyDir']);
            $config->setProxyNamespace($options['proxyNamespace']);
            
            $config->setHydratorDir($options['hydratorDir']);
            $config->setHydratorNamespace($options['hydratorNamespace']);
            
            $reader = new AnnotationReader();
            AnnotationDriver::registerAnnotationClasses();
            $config->setMetadataDriverImpl(new AnnotationDriver($reader, $options['documentPath']));
            $config->setDefaultDB($options['dbname']);
            
            $this->_documentManager = DocumentManager::create(new Connection($options['server']), $config);
        }
        
        return $this->_documentManager;
    }
}

?>
