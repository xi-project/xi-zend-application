<?php
namespace Xi\Zend\Application\Resource;

class ModuleAutoloaders extends \Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        $bootstrap = $this->getBootstrap();
        $bootstrap->bootstrap('FrontController');
        
        $autoloader = $bootstrap->getApplication()->getAutoloader();
        $modules = $bootstrap->getResource('FrontController')->getControllerDirectory();;
    }
}