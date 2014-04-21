<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Router\MatchResult;

use Dash\Router\MatchResult\SuccessfulMatch;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Router\MatchResult\SuccessfulMatch
 */
class SuccessfulMatchTest extends TestCase
{
    public function testIsSuccess()
    {
        $this->assertTrue((new SuccessfulMatch())->isSuccess());
    }

    public function testConstructorInjection()
    {
        $this->assertEquals(['foo' => 'bar'], (new SuccessfulMatch(['foo' => 'bar']))->getParams());
    }

    public function testSetParam()
    {
        $result = new SuccessfulMatch();
        $result->setParam('foo', 'bar');
        $this->assertEquals('bar', $result->getParam('foo'));
    }

    public function testGetParamDefault()
    {
        $this->assertNull((new SuccessfulMatch())->getParam('foo'));
        $this->assertEquals('bar', (new SuccessfulMatch())->getParam('foo', 'bar'));
    }
}
