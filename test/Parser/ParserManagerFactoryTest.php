<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Parser;

use Dash\AbstractPluginManagerFactory;
use Dash\Parser\ParserManager;
use Dash\Parser\ParserManagerFactory;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Parser\ParserManagerFactory
 */
class ParserManagerFactoryTest extends TestCase
{
    public function testFactoryUsesAbstractPluginManagerFactory()
    {
        $factory = new ParserManagerFactory();
        $this->assertInstanceOf(AbstractPluginManagerFactory::class, $factory);
    }

    public function testFactorySettings()
    {
        $factory = new ParserManagerFactory();

        $invoker = function ($methodName) { return $this->{$methodName}(); };
        $protectedInvoker = $invoker->bindTo($factory, $factory);

        $this->assertSame('parser_manager', $protectedInvoker('getConfigKey'));
        $this->assertSame(ParserManager::class, $protectedInvoker('getClassName'));
    }
}
