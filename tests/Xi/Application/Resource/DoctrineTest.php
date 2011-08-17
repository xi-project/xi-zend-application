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
     * @return array
     */
    public static function misconfiguredProxies()
    {
        return array(
            array(
                array(
                    'orm'  => array(),
                    'dbal' => array(),
                )
            ),
            array(
                array(
                    'orm'  => array(
                        'proxyDir' => '',
                    ),
                    'dbal' => array(),
                )
            ),
            array(
                array(
                    'orm'  => array(
                        'proxyNamespace' => '',
                    ),
                    'dbal' => array(),
                )
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
}
