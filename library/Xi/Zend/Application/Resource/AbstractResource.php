<?php
namespace Xi\Zend\Application\Resource;

abstract class AbstractResource extends \Zend_Application_Resource_ResourceAbstract
{
    /**
     * @return \Zend_Loader_Autoloader_Interface
     */
    public function getAutoloader()
    {
        return $this->getBootstrap()->getApplication()->getAutoloader();
    }
}