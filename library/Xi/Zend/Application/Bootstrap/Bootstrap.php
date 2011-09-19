<?php
namespace Xi\Zend\Application\Bootstrap;

use Zend_Application_Bootstrap_Exception,
    Zend_Application_Resource_Resource;

class Bootstrap extends \Zend_Application_Bootstrap_Bootstrap
{
    /**
     * @param array $namespaces
     * @return Bootstrap
     */
    public function setLibraryNamespaces($namespaces)
    {
        $libraryPath = $this->getLibraryPath();
        foreach ($namespaces as $ns) {
            $this->registerNamespaceAutoloader($ns, $libraryPath);
        }
        return $this;
    }
    
    /**
     * @return string
     */
    protected function getLibraryPath()
    {
        return APPLICATION_PATH . '/../library';
    }
    
    /**
     * @param string $namespace
     * @param string $rootPath
     * @return Bootstrap
     */
    protected function registerNamespaceAutoloader($namespace, $rootPath)
    {
        $autoloader = $this->getNamespaceAutoloader($namespace, $rootPath);
        $this->getApplication()->getAutoloader()->pushAutoloader($autoloader, "$namespace\\");
        return $this;
    }
    
    /**
     * @param string $namespace
     * @param string $root path
     * @return callback
     */
    protected function getNamespaceAutoloader($namespace, $root)
    {
        require_once $this->getLibraryPath() . '/Doctrine/Common/ClassLoader.php';
        require_once $this->getLibraryPath() . '/Xi/Zend/Application/ClassLoader.php';
        return array(new \Xi\Zend\Application\ClassLoader($namespace, $root), 'loadClass');
    }
}