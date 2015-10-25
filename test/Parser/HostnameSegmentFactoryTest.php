<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Parser;

use Dash\Parser\AbstractSegmentFactory;
use Dash\Parser\HostnameSegmentFactory;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Parser\HostnameSegmentFactory
 */
class HostnameSegmentFactoryTest extends TestCase
{
    public function testFactoryUsesAbstractSegmentFactory()
    {
        $factory = new HostnameSegmentFactory();
        $this->assertInstanceOf(AbstractSegmentFactory::class, $factory);
    }

    public function testFactorySettings()
    {
        $factory = new HostnameSegmentFactory();
        $this->assertAttributeSame('hostname', 'patternOptionName', $factory);
        $this->assertAttributeSame('.', 'delimiter', $factory);
    }
}
