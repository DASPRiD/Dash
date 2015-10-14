<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Parser;

use Dash\Parser\ParserManager;
use Dash\Parser\ParserManagerFactory;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Parser\ParserManagerFactory
 */
class ParserManagerFactoryTest extends TestCase
{
    public function testFactorySucceedsWithoutConfig()
    {
        $factory       = new ParserManagerFactory();
        $parserManager = $factory($this->getMock(ContainerInterface::class), '');

        $this->assertInstanceOf(ParserManager::class, $parserManager);
    }

    public function testFactoryWithConfig()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'dash' => [
                'parser_manager' => [
                    'services' => [
                        'test' => true,
                    ],
                ],
            ],
        ]);

        $factory       = new ParserManagerFactory();
        $parserManager = $factory($container->reveal(), '');

        $this->assertInstanceOf(ParserManager::class, $parserManager);
        $this->assertAttributeSame(['test' => true], 'services', $parserManager);
    }
}
