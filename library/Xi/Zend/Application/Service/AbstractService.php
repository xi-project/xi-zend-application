<?php
namespace Xi\Zend\Application\Service;

use \Xi\Zend\Application\DI\DependencyProvider;

/**
 * A base for your service classes.
 */
class AbstractService
{
    /**
     * @var EntityManager
     */
    protected $em;
    
    public function __construct(DependencyProvider $dp = null)
    {
        $dp = $dp ?: DependencyProvider::getDefault();
        $this->em = $dp->get('entityManager');
    }
}