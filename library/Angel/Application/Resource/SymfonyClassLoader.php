<?php
/**
 * Description of MongoDocumentManager
 *
 * @author powerdream5
 */
require APPLICATION_PATH.'/../library/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

class Angel_Application_Resource_SymfonyClassLoader extends Zend_Application_Resource_ResourceAbstract{
    
    /**
     * 
     */
    public function init(){
        $loader = new UniversalClassLoader();
        $loader->registerNamespace('Symfony', APPLICATION_PATH.'/../library');
        
        $loader->register();
    }
}

?>
