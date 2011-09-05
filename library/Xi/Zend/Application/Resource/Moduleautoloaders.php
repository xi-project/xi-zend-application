<?php
namespace Xi\Zend\Application\Resource;

use \Xi\Zend\Application\ClassLoader;

/**
 * Adds a \Xi\Zend\Application\ClassLoader to the autoloader used for each enabled module.
 */
class Moduleautoloaders extends AbstractResource
{
    public function init()
    {
        $this->getBootstrap()->bootstrap('FrontController');
        $autoloader = $this->getAutoloader();
        $autoloader->pushAutoloader($this->getLibraryAutoloader());
        $autoloader->pushAutoloader($this->getApplicationLibraryAutoloader());
        foreach ($this->getModuleRoots() as $moduleName => $moduleRoot) {
            $autoloader->pushAutoloader($this->getNamespaceAutoloader($moduleName, $moduleRoot), "$moduleName\\");
        }
    }
    
    /**
     * @return callback
     */
    protected function getLibraryAutoloader()
    {
        return array(new ClassLoader(null, realpath(APPLICATION_PATH . '/../library')), 'loadClass');
    }
    
    /**
     * @return callback
     */
    protected function getApplicationLibraryAutoloader()
    {
        return array(new ClassLoader(null, APPLICATION_PATH . '/library'), 'loadClass');
    }
    
    /**
     * @param string $namespace
     * @param string $root path
     * @return callback
     */
    protected function getNamespaceAutoloader($namespace, $root)
    {
        return array(new ClassLoader($namespace, $root), 'loadClass');
    }
    
    /**
     * @return \Zend_Controller_Front
     */
    protected function getFrontController()
    {
        return $this->getBootstrap()->getResource('FrontController');
    }
    
    /**
     * @return array<ModuleName => root>
     */
    protected function getModuleRoots()
    {
        $result = array();
        foreach ($this->getFrontController()->getControllerDirectory() as $controllerDirectory) {
            $moduleDirectory = dirname($controllerDirectory);
            $moduleName = basename($moduleDirectory);
            $moduleRoot = dirname($moduleDirectory);
            $result[$moduleName] = $moduleRoot;
        }
        return $result;
    }
}