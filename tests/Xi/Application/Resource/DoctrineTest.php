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

use PHPUnit_Framework_TestCase;

/**
 * Doctrine application resource test
 *
 * @category   Xi
 * @package    Application
 * @subpackage Resource
 * @author     Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
 */

class DoctrineTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function emptyOptionsShouldNotTryToBootstrapAnything()
    {
        $initedResource = $this->getResource()->init();

        $this->assertEquals(null, $initedResource->getEntityManager());
        $this->assertEquals(null, $initedResource->getMongoDBDocumentManager());
    }

    /**
     * @test
     */
    public function shouldNotBeAbleToUseORMWithoutDBAL()
    {
        $options = array(
            'orm' => array(),
        );

        $this->setExpectedException('InvalidArgumentException');

        $this->getResource($options)->init();
    }

    /**
     * @test
     * @dataProvider misconfiguredProxies
     */
    public function shouldEnsureThatProxiesAreConfigured($options)
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->getResource($options)->init();
    }

    /**
     * @test
     */
    public function configuringProxiesShouldSuffice()
    {
        $this->markTestSkippedIfDoctrineIsMissing();

        $em = $this->getResource($this->getORMConfiguration(array(
            'proxyDir'       => '',
            'proxyNamespace' => 'Proxy',
        )))->init()->getEntityManager();

        $this->assertInstanceOf('Doctrine\ORM\EntityManager', $em);
    }

    /**
     * @return array
     */
    public function misconfiguredProxies()
    {
        return array(
            array(
                $this->getORMConfiguration()
            ),
            array(
                $this->getORMConfiguration(array(
                    'proxyDir' => '',
                ))
            ),
            array(
                $this->getORMConfiguration(array(
                    'proxyNamespace' => '',
                ))
            ),
        );
    }

    /**
     * @param array $options
     * @return Doctrine
     */
    private function getResource(array $options = array())
    {
        return new Doctrine($options);
    }

    /**
     * Get basic ORM configuration.
     *
     * @param array $options
     * @return type
     */
    private function getORMConfiguration(array $options = array())
    {
        return array(
            'orm'  => $options,
            'dbal' => array(
                'driver' => 'pdo_mysql',
            )
        );
    }

    /**
     * Marks a test skipped if Doctrine library is missing.
     */
    private function markTestSkippedIfDoctrineIsMissing()
    {
        if (!class_exists('\\Doctrine\ORM\EntityManager')) {
            $this->markTestSkipped('Doctrine ORM library was not found in the include path.');
        }
    }
}
