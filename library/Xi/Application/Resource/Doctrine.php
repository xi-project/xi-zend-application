<?php

/**
 * Xi
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled with this
 * package in the file LICENSE.
 *
 * @category   Xi
 * @package    Application
 * @subpackage Resource
 * @license    http://www.opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Xi\Application\Resource;

use Zend_Application_Resource_ResourceAbstract as ResourceAbstract,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Configuration;

/**
 * Doctrine application resource
 *
 * @category   Xi
 * @package    Application
 * @subpackage Resource
 * @author     pekkis
 */
class Doctrine extends ResourceAbstract
{
    
    public function init()
    {
        $options = $this->getOptions();
                
        $dirs = array();
        if (isset($options['annotationDirectories'])) {
            foreach ($options['annotationDirectories'] as $directory) {
                $dirs[] = realpath($directory);
            }
        }

        $cache = new $options['cache']();

        $config = new Configuration;
        $config->setMetadataCacheImpl($cache);
                
        $driverImpl = $config->newDefaultAnnotationDriver($dirs);
                
        $config->setMetadataDriverImpl($driverImpl);
        
        $config->setQueryCacheImpl($cache);
        
        $config->setProxyDir(realpath($options['proxyDir']));
        $config->setProxyNamespace($options['proxyNamespace']);

        $config->setAutoGenerateProxyClasses((bool) $options['autoGenerateProxyClasses']);

        $em = EntityManager::create($options['connectionParams'], $config);
        
        return $em;
        
        
    }
    
    
    
    
    
}

