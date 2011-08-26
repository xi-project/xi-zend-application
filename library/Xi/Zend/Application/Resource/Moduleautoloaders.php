<?php
namespace Xi\Zend\Application\Resource;

use Doctrine\Common\ClassLoader as DoctrineClassLoader;

/**
 * Adds a DoctrineClassLoader to the autoloader used for each enabled module.
 */
class Moduleautoloaders extends AbstractResource
{
    public function init()
    {
        $this->getBootstrap()->bootstrap('FrontController');
        $autoloader = $this->getAutoloader();
        foreach ($this->getModuleRoots() as $moduleName => $moduleRoot) {
            $autoloader->pushAutoloader($this->getNamespaceAutoloader($moduleName, $moduleRoot), "$moduleName\\");
        }
    }
    
    /**
     * @param string $namespace
     * @param string $root path
     * @return callback
     */
    protected function getNamespaceAutoloader($namespace, $root)
    {
        return array(new DoctrineClassLoader($namespace, $root), 'loadClass');
    }
    
    /**
     * @return \Zend_Loader_Autoloader
     */
    private function getAutoloader()
    {
        return $this->getBootstrap()->getApplication()->getAutoloader();
    }
    
    /**
     * @return \Zend_Controller_Front
     */
    private function getFrontController()
    {
        return $this->getBootstrap()->getResource('FrontController');
    }
    
    /**
     * @return array<ModuleName => root>
     */
    private function getModuleRoots()
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