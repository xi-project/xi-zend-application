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
    InvalidArgumentException,
    Doctrine\Common\Cache\ArrayCache,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Configuration;

/**
 * Doctrine application resource
 *
 * @category   Xi
 * @package    Application
 * @subpackage Resource
 * @author     pekkis
 * @author     Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
 */
class Doctrine extends ResourceAbstract
{
    /**
     * Doctrine ORM EntityManager
     *
     * @var EntityManager
     */
    private $em;

    /**
     * @return Doctrine
     * @throws InvalidArgumentException
     */
    public function init()
    {
        $this->em = $this->initORM();

        return $this;
    }

    /**
     * @return EntityManager|null
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * Inits ORM
     *
     * @return EntityManager|null
     * @throws InvalidArgumentException
     */
    private function initORM()
    {
        $options = $this->getOptions();

        if (isset($options['orm'])) {
            $ormOptions = $options['orm'];

            if (!isset($options['dbal'])) {
                throw new InvalidArgumentException('DBAL must be configured to use ORM');
            } else if (!isset($ormOptions['proxyDir'])) {
                throw new InvalidArgumentException('Proxy dir must be configured');
            } else if (!isset($ormOptions['proxyNamespace'])) {
                throw new InvalidArgumentException('Proxy namespace must be configured');
            }

            $config = new Configuration();
            $config->setMetadataCacheImpl($this->getCache($ormOptions, 'metadataCache'));
            $config->setQueryCacheImpl($this->getCache($ormOptions, 'queryCache'));
            $config->setResultCacheImpl($this->getCache($ormOptions, 'resultCache'));
            $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver(
                $this->getAnnotationDirs($ormOptions)
            ));
            $config->setProxyDir(realpath($ormOptions['proxyDir']));
            $config->setProxyNamespace($ormOptions['proxyNamespace']);
            $config->setAutoGenerateProxyClasses(
                (bool) isset($ormOptions['cache']['autoGenerateProxyClasses'])
                    ? $ormOptions['cache']['autoGenerateProxyClasses']
                    : false
            );

            return EntityManager::create($options['dbal'], $config);
        }
    }

    /**
     * Get cache or default if given doesn't exist
     *
     * @param  array                        $options
     * @param  string                       $name
     * @return \Doctrine\Common\Cache\Cache
     */
    private function getCache(array $options, $name)
    {
        return isset($options['cache'][$name])
            ? new $options['cache'][$name]()
            : new ArrayCache();
    }

    /**
     * Get annotation directories
     *
     * @param  array $options
     * @return array
     */
    private function getAnnotationDirs(array $options)
    {
        $dirs = array();

        if (isset($options['annotationDirectories'])) {
            foreach ($options['annotationDirectories'] as $directory) {
                $dirs[] = realpath($directory);
            }
        }

        return $dirs;
    }
}
