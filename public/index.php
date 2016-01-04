<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

if(APPLICATION_ENV != 'production'){
    define('ALI_LOG', true);
}
define('ALI_LOG_PATH', realpath(dirname(__FILE__) . '/../data/log/'));
        
// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';
require_once 'Zend/Config/Ini.php';

$system_ini_path = 'angelhere.ini';
if(file_exists($system_ini_path)){
    $system_ini = new \Zend_Config_Ini($system_ini_path);
}
else{
    echo "Cannot find the system ini file.";
    exit;
}

$config = new \Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', null, true);
$config->merge($system_ini);

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    $config->get(APPLICATION_ENV)
);
$application->bootstrap()
            ->run();