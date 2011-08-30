<?php
namespace Xi\Zend\Application\DI;

/**
 * A starting point for your DependencyProvider.
 */
class DefaultDependencyProvider extends DependencyProvider
{
    public function __construct(\Zend_Application_Bootstrap_Bootstrap $bootstrap)
    {
        parent::__construct();
        $app = $bootstrap->getApplication();
        
        $this->put('application', $app);
        
        $this->putFunc('entityManager', function() use ($bootstrap) {
            return $bootstrap->getResource('doctrine')->getEntityManager();
        });
    }
    
}