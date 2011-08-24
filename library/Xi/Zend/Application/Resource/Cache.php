<?php
namespace Xi\Zend\Application\Resource;

use Zend_Application_Resource_ResourceAbstract as ResourceAbstract,
    Zend_Application_Exception,
    Zend_Cache_Core,
    Zend_Db_Table_Abstract,
    Zend_Date,
    Zend_Translate,
    Zend_Locale,
    Zend_Currency;

/**
 * Cache application resource
 *
 * @category   Xi
 * @package    Application
 * @subpackage Resource
 * @author     pekkis
 * @todo       The whole Zend Cache is soooo retarded. This does not work as it should work.
 * @license    http://www.opensource.org/licenses/BSD-3-Clause New BSD License
 */
class Cache extends ResourceAbstract
{
    protected $backends;

    protected $frontends;

    /**
     * @return Zend_Cache_Manager
     */
    public function init()
    {
        $opts = $this->getOptions();
        
        $cm = $this->getBootstrap()->bootstrap('cachemanager')->getResource('cachemanager');

        if(isset($opts['framework'])) {
            foreach($opts['framework'] as $key => $cache) {
                $method = '_init' . ucfirst($key);
                $this->$method($cm->getCache($cache));
            }
        }
        
        // output caching
        if(isset($opts['view'])) {
            $view = $this->getBootstrap()->bootstrap('view')->getResource('view');
            $cache = $cm->getCache($opts['view']);
            if(!$cache) {
                throw new Zend_Application_Exception("Cache '{$opts['cache']}' not found", 500);
            }
            $view->cache()->setCache($cache);
        }

        return $cm;
    }
    
    protected function _initTable(Zend_Cache_Core $cache)
    {
        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
    }
    
    protected function _initDate(Zend_Cache_Core $cache)
    {
        Zend_Date::setOptions(array('cache' => $cache));
    }
    
    protected function _initTranslate(Zend_Cache_Core $cache)
    {
        Zend_Translate::setCache($cache);
    }
    
    protected function _initLocale(Zend_Cache_Core $cache)
    {
        Zend_Locale::setCache($cache);
    }
    
    protected function _initCurrency(Zend_Cache_Core $cache)
    {
        Zend_Currency::setCache($cache);
    }
}
