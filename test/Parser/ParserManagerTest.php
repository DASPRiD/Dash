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
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Parser\ParserManager
 */
class ParserManagerTest extends TestCase
{
    public function testRegisteredServices()
    {
        $parserManager = new ParserManager($this->prophesize(ContainerInterface::class)->reveal());
        $this->assertTrue($parserManager->has('HostnameSegment'));
        $this->assertTrue($parserManager->has('PathSegment'));
    }
}
