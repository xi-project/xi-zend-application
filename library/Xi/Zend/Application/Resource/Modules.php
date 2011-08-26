<?php
namespace Xi\Zend\Application\Resource;

use Zend_Application_Resource_Modules,
    Zend_Application_Resource_Exception;

class Modules extends Zend_Application_Resource_Modules
{
    /**
     * NOTE: Copied over from parent class to parametrize bootstrap class format.
     * Also added dependency to ModuleAutoloaders.
     *
     * @return array
     * @throws Zend_Application_Resource_Exception When bootstrap class was not found
     */
    public function init()
    {
        $bootstrap = $this->getBootstrap();
        $bootstrap->bootstrap(array('ModuleAutoloaders', 'FrontController'));
        $front = $bootstrap->getResource('FrontController');

        $modules = $front->getControllerDirectory();
        $default = $front->getDefaultModule();
        $curBootstrapClass = get_class($bootstrap);
        
        $bootstraps = array();
        foreach ($modules as $module => $moduleDirectory) {
            $bootstrapClass = $this->formatBootstrapClass($module);
            // Relying on module autoloaders to find the class for us
            if (!class_exists($bootstrapClass)) {
                throw new Zend_Application_Resource_Exception("Bootstrap class '$bootstrapClass' not found");
            }

            if ($bootstrapClass == $curBootstrapClass) {
                // If the found bootstrap class matches the one calling this
                // resource, don't re-execute.
                continue;
            }

            $bootstraps[$module] = $bootstrapClass;
        }

        return $this->_bootstraps = $this->bootstrapBootstraps($bootstraps);
    }
    
    /**
     * @param string $module
     * @return string
     */
    protected function formatBootstrapClass($module)
    {
        return ucfirst($module)."\\Bootstrap";
    }
}
