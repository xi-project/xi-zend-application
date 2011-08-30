<?php
namespace Xi\Zend\Application\Resource;

use InvalidArgumentException,
    Memcache,
    Doctrine\Common\Cache\ArrayCache,
    Doctrine\Common\Cache\MemcacheCache,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Configuration as ORMConfiguration,
    Doctrine\ODM\MongoDB\DocumentManager as MongoDBDocumentManager,
    Doctrine\ODM\MongoDB\Configuration as MongoDBConfiguration;

/**
 * Doctrine application resource
 *
 * @category   Xi
 * @package    Application
 * @subpackage Resource
 * @author     pekkis
 * @author     Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause New BSD License
 */
class Doctrine extends AbstractResource
{
    /**
     * @var ORMConfiguration
     */
    private $ormConfig;
    
    /**
     * Doctrine ORM EntityManager
     *
     * @var EntityManager
     */
    private $em;

    /**
     * Doctrine MongoDB ODM DocumentManager
     *
     * @var MongoDBDocumentManager
     */
    private $dmMongo;

    /**
     * @return Doctrine
     * @throws InvalidArgumentException
     */
    public function init()
    {
        $this->ormConfig = $this->initORMConfig();
        $this->em        = $this->initORM();
        $this->dmMongo   = $this->initODMMongoDB();
        
        $this->initEntityAutoloading();

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
     * @return EntityManager|null
     */
    public function recreateEntityManager()
    {
        if ($this->em && $this->em->isOpen()) {
            $this->em->close();
        }
        $this->em = $this->initORM();
        return $this->em;
    }

    /**
     * @return MongoDBDocumentManager|null
     */
    public function getMongoDBDocumentManager()
    {
        return $this->dmMongo;
    }

    public function getORMConfig()
    {
        return $this->ormConfig;
    }
    
    private function initORMConfig()
    {
        $options = $this->getOptions();
        if (isset($options['orm'])) {
            $ormOptions = $options['orm'];

            if (!isset($options['dbal'])) {
                throw new InvalidArgumentException('DBAL must be configured to use ORM');
            }

            $this->assertProxyConfiguration($ormOptions);

            $config = new ORMConfiguration();
            $config->setMetadataCacheImpl($this->getCache($ormOptions, 'metadataCache'));
            $config->setQueryCacheImpl($this->getCache($ormOptions, 'queryCache'));
            $config->setResultCacheImpl($this->getCache($ormOptions, 'resultCache'));
            $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver(
                $this->getAnnotationDirectories($ormOptions)
            ));
            $config->setProxyDir(realpath($ormOptions['proxyDir']));
            $config->setProxyNamespace($ormOptions['proxyNamespace']);
            $config->setAutoGenerateProxyClasses(
                (bool) isset($ormOptions['autoGenerateProxyClasses'])
                    ? $ormOptions['autoGenerateProxyClasses']
                    : true
            );

            return $config;
        } else {
            return null;
        }
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
        $config = $this->getORMConfig();

        if ($config) {
            return EntityManager::create($options['dbal'], $config);
        } else {
            return null;
        }
    }

    /**
     * Inits MongoDB ODM
     *
     * @return EntityManager|null
     * @throws InvalidArgumentException
     */
    private function initODMMongoDB()
    {
        $options = $this->getOptions();

        if (isset($options['odm']['mongoDb'])) {
            $odmOptions = $options['odm']['mongoDb'];

            $this->assertProxyConfiguration($odmOptions);
            $this->assertHydratorConfiguration($odmOptions);

            $config = new MongoDBConfiguration();
            $config->setMetadataCacheImpl($this->getCache($odmOptions, 'metadataCache'));
            $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver(
                $this->getAnnotationDirectories($odmOptions)
            ));
            $config->setProxyDir(realpath($odmOptions['proxyDir']));
            $config->setProxyNamespace($odmOptions['proxyNamespace']);
            $config->setAutoGenerateProxyClasses(
                (bool) isset($odmOptions['autoGenerateProxyClasses'])
                    ? $odmOptions['autoGenerateProxyClasses']
                    : true
            );
            $config->setHydratorDir(realpath($odmOptions['hydratorDir']));
            $config->setHydratorNamespace($odmOptions['hydratorNamespace']);
            $config->setAutoGenerateHydratorClasses(
                (bool) isset($odmOptions['autoGenerateHydratorClasses'])
                    ? $odmOptions['autoGenerateHydratorClasses']
                    : true
            );

            if (isset($odmOptions['defaultDb'])) {
                $config->setDefaultDB($odmOptions['defaultDb']);
            }

            return MongoDBDocumentManager::create(null, $config);
        } else {
            return null;
        }
    }
    
    /**
     * Add autoloaders for entities.
     */
    private function initEntityAutoloading()
    {
        $options = $this->getOptions();
        $config = $this->getORMConfig();
        if ($config) {
            foreach ($this->getAnnotationDirectories($options['orm']) as $dir) {
                $loader = new \Xi\Zend\Application\ClassLoader(null, $dir);
                $this->getAutoloader()->pushAutoloader(array($loader, 'loadClass'));
            }
        }
    }

    /**
     * Get cache or default if given doesn't exist
     *
     * @param  array                        $options
     * @param  string                       $name
     * @return \Doctrine\Common\Cache\Cache
     * @throws InvalidArgumentException
     */
    private function getCache(array $options, $name)
    {
        $cache = isset($options[$name])
            ? new $options[$name]()
            : new ArrayCache();

        if ($cache instanceof MemcacheCache) {
            $cache->setMemcache($this->createMemcache($options));
        }

        if (isset($options['cache']['namespace'])) {
            $cache->setNamespace($options['cache']['namespace']);
        }

        return $cache;
    }

    /**
     * Get annotation directories
     *
     * @param  array $options
     * @return array
     */
    private function getAnnotationDirectories(array $options)
    {
        $dirs = array();

        if (isset($options['annotationDirectories'])) {
            foreach ($options['annotationDirectories'] as $directory) {
                $dirs[] = realpath($directory);
            }
        }

        return $dirs;
    }

    /**
     * Creates a Memcache instance
     *
     * @param  array                    $options
     * @return Memcache
     * @throws InvalidArgumentException
     */
    private function createMemcache(array $options)
    {
        if (!isset($options['memcache']['host'])) {
            throw new InvalidArgumentException('Memcache host is not configured');
        } else if (!isset($options['memcache']['port'])) {
            throw new InvalidArgumentException('Memcache port is not configured');
        }

        $memcache = new Memcache();
        $memcache->connect($options['memcache']['host'],
                           $options['memcache']['port']);

        return $memcache;
    }

    /**
     * Assert proxy dir and namespace configuration options
     *
     * @param  array                    $options
     * @return null
     * @throws InvalidArgumentException
     */
    private function assertProxyConfiguration(array $options)
    {
        if (!isset($options['proxyDir'])) {
            throw new InvalidArgumentException('Proxy dir must be configured');
        } else if (!isset($options['proxyNamespace'])) {
            throw new InvalidArgumentException('Proxy namespace must be configured');
        }
    }

    /**
     * Assert hydrator dir and namespace configuration options
     *
     * @param  array                    $options
     * @return null
     * @throws InvalidArgumentException
     */
    private function assertHydratorConfiguration(array $options)
    {
        if (!isset($options['hydratorDir'])) {
            throw new InvalidArgumentException('Hydrator dir must be configured');
        } else if (!isset($options['hydratorNamespace'])) {
            throw new InvalidArgumentException('Hydrator namespace must be configured');
        }
    }
}
