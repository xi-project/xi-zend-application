<?php
namespace Xi\Zend\Application\Resource;

/**
 * Makes views use our alternative path scheme.
 */
class View extends \Zend_Application_Resource_View
{
    public function init()
    {
        $ret = parent::init();
        $viewRenderer = \Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->setViewBasePathSpec(':moduleDir/Resources/views');
        return $ret;
    }
    
    /**
     * Like \Zend_Application_Resource_View::getView() but uses
     * \Xi\Zend\Mvc\View, which overrides the default path scheme.
     */
    public function getView()
    {
        if (null === $this->_view) {
            $options = $this->getOptions();
            $this->_view = new \Xi\Zend\Mvc\View($options);

            if (isset($options['doctype'])) {
                $this->_view->doctype()->setDoctype(strtoupper($options['doctype']));
                if (isset($options['charset']) && $this->_view->doctype()->isHtml5()) {
                    $this->_view->headMeta()->setCharset($options['charset']);
                }
            }
            if (isset($options['contentType'])) {
                $this->_view->headMeta()->appendHttpEquiv('Content-Type', $options['contentType']);
            }
            if (isset($options['assign']) && is_array($options['assign'])) {
                $this->_view->assign($options['assign']);
            }
        }
        return $this->_view;
    }
}