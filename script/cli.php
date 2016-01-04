<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

require 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();
$system_application = new Zend_Application(
                            APPLICATION_ENV,
                            APPLICATION_PATH . '/configs/application.ini'
                        );        
$system_application->bootstrap();

$loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespace('Symfony', APPLICATION_PATH.'/../library');
$loader->registerNamespace('Angel', APPLICATION_PATH.'/../library');

$loader->register();
        
$application = new Symfony\Component\Console\Application();
$application->addCommands(array(
    new Angel\Command\LoadCity($system_application)
));
$application->run();

