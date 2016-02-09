<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest;

use Dash\BaseUriFactory;
use Dash\Exception\OutOfBoundsException;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\BaseUriFactory
 */
class BaseUriFactoryTest extends TestCase
{
    public function testFailureWithoutConfig()
    {
        $this->setExpectedException(OutOfBoundsException::class, 'Missing "base_uri" key in "dash" section');

        $factory = new BaseUriFactory();
        $factory($this->getContainer(), '');
    }

    public function testFactorySucceedsWithEmptyConfig()
    {
        $factory = new BaseUriFactory();
        $baseUri = $factory($this->getContainer(['dash' => ['base_uri' => 'http://example.com']]), '');

        $this->assertSame('http://example.com', $baseUri);
    }

    /**
     * @param  array $config
     * @return ContainerInterface
     */
    protected function getContainer(array $config = null)
    {
        $container = $this->prophesize(ContainerInterface::class);

        if (null !== $config) {
            $container->get('config')->willReturn($config);
            $container->has('config')->willReturn(true);
        } else {
            $container->has('config')->willReturn(false);
        }

        return $container->reveal();
    }
}
