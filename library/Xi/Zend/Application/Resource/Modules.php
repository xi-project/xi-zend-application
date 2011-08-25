<?php
namespace Xi\Zend\Application\Resources;

class Modules extends \Zend_Application_Resource_ResourceAbstract
{
    /**
     * NOTE: Copied over from parent class to parametrize bootstrap class format.
     *
     * @return array
     * @throws Zend_Application_Resource_Exception When bootstrap class was not found
     */
    public function init()
    {
        $bootstraps = array();
        $bootstrap = $this->getBootstrap();
        $bootstrap->bootstrap('FrontController')->bootstrap('ModuleAutoloaders');
        $front = $bootstrap->getResource('FrontController');

        $modules = $front->getControllerDirectory();
        $default = $front->getDefaultModule();
        $curBootstrapClass = get_class($bootstrap);
        foreach ($modules as $module => $moduleDirectory) {
            $bootstrapClass = $this->formatBootstrapClass($module);
            if (!class_exists($bootstrapClass, false)) {
                $bootstrapPath  = dirname($moduleDirectory) . '/Bootstrap.php';
                if (file_exists($bootstrapPath)) {
                    $eMsgTpl = 'Bootstrap file found for module "%s" but bootstrap class "%s" not found';
                    include_once $bootstrapPath;
                    if (($default != $module)
                        && !class_exists($bootstrapClass, false)
                    ) {
                        throw new Zend_Application_Resource_Exception(sprintf(
                            $eMsgTpl, $module, $bootstrapClass
                        ));
                    } elseif ($default == $module) {
                        if (!class_exists($bootstrapClass, false)) {
                            $bootstrapClass = 'Bootstrap';
                            if (!class_exists($bootstrapClass, false)) {
                                throw new Zend_Application_Resource_Exception(sprintf(
                                    $eMsgTpl, $module, $bootstrapClass
                                ));
                            }
                        }
                    }
                } else {
                    continue;
                }
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
        return $this->_formatModuleName($module) . '_Bootstrap';
    }
}
