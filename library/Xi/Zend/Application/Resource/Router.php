<?php
namespace Xi\Zend\Application\Resource;

use Zend_Application_Resource_Router,
    Zend_Config;

class Router extends Zend_Application_Resource_Router
{
    /**
     * Retrieve router object
     *
     * @return \Zend_Controller_Router_Rewrite
     */
    public function getRouter()
    {
        if (null === $this->_router) {
            $bootstrap = $this->getBootstrap();
            $bootstrap->bootstrap('FrontController');
            $this->_router = $bootstrap->getContainer()->frontcontroller->getRouter();
            $this->initRouter($this->_router);
        }

        return $this->_router;
    }
    
    /**
     * @param \Zend_Controller_Router_Rewrite $router
     * @return void
     */
    protected function initRouter($router)
    {
        $options = $this->getOptions();

        if (isset($options['chainNameSeparator'])) {
            $router->setChainNameSeparator($options['chainNameSeparator']);
        }

        if (isset($options['useRequestParametersAsGlobal'])) {
            $router->useRequestParametersAsGlobal($options['useRequestParametersAsGlobal']);
        }
        
        if (isset($options['config'])) {
            $router->removeDefaultRoutes();
            $router->addRoutes($this->_loadConfig($options['config']));
        }
        
        if (isset($options['routes'])) {
            $router->removeDefaultRoutes();
            $router->addConfig(new Zend_Config($options['routes']));
        }
    }
    
    /**
     * @param string $path path to .php array configuration file
     * @return Zend_Config
     */
    protected function _loadConfig($path)
    {
        // TODO: Cache
        // TODO: Multiple configuration types
        return new Zend_Config(require $path);
    }
}