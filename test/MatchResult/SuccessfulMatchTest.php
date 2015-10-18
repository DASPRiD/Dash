<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\MatchResult;

use Dash\MatchResult\SuccessfulMatch;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\MatchResult\SuccessfulMatch
 */
class SuccessfulMatchTest extends TestCase
{
    public function testConstructorInjection()
    {
        $this->assertEquals(['foo' => 'bar'], (new SuccessfulMatch(['foo' => 'bar']))->getParams());
    }

    public function testGetParamDefault()
    {
        $this->assertNull((new SuccessfulMatch([]))->getParam('foo'));
        $this->assertSame('bar', (new SuccessfulMatch([]))->getParam('foo', 'bar'));
    }

    public function testGetParam()
    {
        $this->assertSame('bar', (new SuccessfulMatch(['foo' => 'bar']))->getParam('foo'));
    }

    public function testIsSuccess()
    {
        $this->assertTrue((new SuccessfulMatch([]))->isSuccess());
    }

    public function testFromChildMatchParamMerge()
    {
        $childMatch = new SuccessfulMatch(['foo' => 'bar', 'baz' => 'bat']);
        $match = SuccessfulMatch::fromChildMatch($childMatch, ['foo' => 'bat', 'bar' => 'bar'], '');

        $this->assertSame([
            'foo' => 'bar',
            'baz' => 'bat',
            'bar' => 'bar',
        ], $match->getParams());
    }

    public function testFromChildMatchWithoutRouteName()
    {
        $childMatch = new SuccessfulMatch([]);
        $match = SuccessfulMatch::fromChildMatch($childMatch, [], 'foo');

        $this->assertSame('foo', $match->getRouteName());
    }

    public function testFromChildMatchWithRouteName()
    {
        $childMatch = SuccessfulMatch::fromChildMatch(new SuccessfulMatch([]), [], 'bar');
        $match = SuccessfulMatch::fromChildMatch($childMatch, [], 'foo');

        $this->assertSame('foo/bar', $match->getRouteName());
    }
}
