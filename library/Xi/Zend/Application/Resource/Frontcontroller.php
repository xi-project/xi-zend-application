<?php
namespace Xi\Zend\Application\Resource;

use Zend_Application_Resource_Frontcontroller;

class Frontcontroller extends Zend_Application_Resource_Frontcontroller
{
    /**
     * Retrieve front controller instance. Overridden to use the xi-zend-mvc
     * front controller instead of Zend's if available.
     *
     * @return Zend_Controller_Front
     */
    public function getFrontController()
    {
        if (null === $this->_front) {
            if (class_exists("Xi\Zend\Mvc\FrontController")) {
                $this->_front = \Xi\Zend\Mvc\FrontController::getInstance();
            } else {
                $this->_front = Zend_Controller_Front::getInstance();
            }
        }
        return $this->_front;
    }
}