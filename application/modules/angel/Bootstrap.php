<?php

class Angel_Bootstrap extends Zend_Application_Module_Bootstrap
{

    /**
     * load the module configuration file
     * @return type
     */
    protected function _initModuleConfig(){
        $config = new Zend_Config_Ini(APPLICATION_PATH.'/modules/'.strtolower($this->getModuleName()).'/configs/module.ini', APPLICATION_ENV);
        $this->setOptions($config->toArray());
        
        return $this->_options;
    }
}