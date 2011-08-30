<?php
namespace Xi\Zend\Application\Resource;

use \Xi\Zend\Application\DI;

/**
 * Sets the default dependency provider to DefaultDependencyProvider,
 * or the class given in the configuration.
 */
class Dependencyprovider extends AbstractResource
{
    public function init()
    {
        $options = $this->getOptions();
        if (isset($options['className'])) {
            $class = $options['className'];
        } else {
            $class = 'Xi\Zend\Application\DI\DefaultDependencyProvider';
        }
        $obj = new $class($this->getBootstrap());
        DI\DependencyProvider::setDefault($obj);
    }
}