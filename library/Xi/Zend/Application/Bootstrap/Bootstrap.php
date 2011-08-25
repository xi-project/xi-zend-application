<?php
namespace Xi\Zend\Application\Bootstrap;

use Doctrine\Common\ClassLoader as DoctrineClassLoader,
    Zend_Application_Bootstrap_Exception,
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
        $doctrineClassLoader = new DoctrineClassLoader($namespace);
        $doctrineClassLoader->setIncludePath($root);
        return array($doctrineClassLoader, 'loadClass');
    }
    
    /**
     * Execute a resource
     *
     * Checks to see if the resource has already been run. If not, it searches
     * first to see if a local method matches the resource, and executes that.
     * If not, it checks to see if a plugin resource matches, and executes that
     * if found.
     *
     * Finally, if not found, it throws an exception.
     *
     * @param  string $resource
     * @return void
     * @throws Zend_Application_Bootstrap_Exception When resource not found
     */
    protected function _executeResource($resource)
    {
        $resourceName = strtolower($resource);

        if (in_array($resourceName, $this->_run)) {
            return;
        }

        if (isset($this->_started[$resourceName]) && $this->_started[$resourceName]) {
            throw new Zend_Application_Bootstrap_Exception('Circular resource dependency detected');
        }

        $classResources = $this->getClassResources();
        if (array_key_exists($resourceName, $classResources)) {
            $this->_started[$resourceName] = true;
            $method = $classResources[$resourceName];
            $return = $this->$method();
            unset($this->_started[$resourceName]);
            $this->_markRun($resourceName);

            if (null !== $return) {
                $this->getContainer()->{$resourceName} = $return;
            }

            return;
        }

        if ($this->hasPluginResource($resource)) {
            $this->_started[$resourceName] = true;
            $plugin = $this->getPluginResource($resource);
            $return = $plugin->init();
            unset($this->_started[$resourceName]);
            $this->_markRun($resourceName);

            if (null !== $return) {
                $this->getContainer()->{$resourceName} = $return;
            }

            return;
        }

        throw new Zend_Application_Bootstrap_Exception('Resource matching "' . $resource . '" not found');
    }

    /**
     * Get a registered plugin resource
     *
     * @param  string $resourceName
     * @return Zend_Application_Resource_Resource
     */
    public function getPluginResource($resource)
    {
        if (array_key_exists(strtolower($resource), $this->_pluginResources)) {
            $resource = strtolower($resource);
            if (!$this->_pluginResources[$resource] instanceof Zend_Application_Resource_Resource) {
                $resourceName = $this->_loadPluginResource($resource, $this->_pluginResources[$resource]);
                if (!$resourceName) {
                    throw new Zend_Application_Bootstrap_Exception(sprintf('Unable to resolve plugin "%s"; no corresponding plugin with that name', $resource));
                }
                $resource = $resourceName;
            }
            return $this->_pluginResources[$resource];
        }

        foreach ($this->_pluginResources as $plugin => $spec) {
            if ($spec instanceof Zend_Application_Resource_Resource) {
                $pluginName = $this->_resolvePluginResourceName($spec);
                if (0 === strcasecmp($resource, $pluginName)) {
                    unset($this->_pluginResources[$plugin]);
                    $this->_pluginResources[$pluginName] = $spec;
                    return $spec;
                }
                continue;
            }

            if (false !== $pluginName = $this->_loadPluginResource($plugin, $spec)) {
                if (0 === strcasecmp($resource, $pluginName)) {
                    return $this->_pluginResources[$pluginName];
                }
                continue;
            }

            if (class_exists($plugin)) { //@SEE ZF-7550
                $spec = (array) $spec;
                $spec['bootstrap'] = $this;
                $instance = new $plugin($spec);
                $pluginName = $this->_resolvePluginResourceName($instance);
                unset($this->_pluginResources[$plugin]);
                $this->_pluginResources[$pluginName] = $instance;

                if (0 === strcasecmp($resource, $pluginName)) {
                    return $instance;
                }
            }
        }

        return null;
    }
}