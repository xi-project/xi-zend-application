<?php
namespace Xi\Zend\Application\Resource;

use Zend_Application_Resource_ResourceAbstract as ResourceAbstract,
    Zend_Exception,
    Mongo as MongoDB,
    MongoConnectionException;

/**
 * Mongo application resource
 *
 * @category   Xi
 * @package    Application
 * @subpackage Resource
 * @author     pekkis
 * @todo       Multiple mongoloids
 */
class Mongo extends ResourceAbstract
{
    /**
     * @var array default options
     */
    protected $_options = array(
		'hostname' => '127.0.0.1',
		'port' => '27017',
		'username' => null,
		'password' => null,
		'databasename' => null,
		'connect'  => true,
    );

    /**
     * @return MongoDb
     * @throws Zend_Exception
     */
    public function init()
    {
        $options = $this->getOptions();
        $dns = $this->formatDns($options);
        try {
            return $this->createMongo(
                $dns,
                $options['connect'],
                isset($options['databasename']) ? $options['databasename'] : null
            );
        } catch (MongoConnectionException $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    /**
     * @param array $options
     * @return string
     */
    protected function formatDns($options)
    {
        if ($options['username'] && $options['password']) {
            return "mongodb://{$options['username']}:{$options['password']}@{$options['hostname']}:{$options['port']}/{$options['databasename']}";
        } else {
            return "mongodb://{$options['hostname']}:{$options['port']}/{$options['databasename']}";
        }
    }

    /**
     * @param string $dns
     * @param boolean $connect
     * @param string $databasename optional
     * @return MongoDB
     * @throws MongoConnectionException
     */
    protected function createMongo($dns, $connect, $databasename = null)
    {
        $mongo = new MongoDB($dns, array('connect' => $connect));

        // @todo: refuctor this kludgering
        if (null !== $databasename) {
            $mongo = $mongo->{$databasename};
        }

        return $mongo;
    }
}
