<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Router\Http\MatchResult;

use Dash\Router\Http\MatchResult\SuccessfulMatch;
use Dash\Router\Http\Parser\ParseResult;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Router\Http\MatchResult\SuccessfulMatch
 */
class SuccessfulMatchTest extends TestCase
{
    public function testExtendsBaseSuccessfulMatch()
    {
        $result = new SuccessfulMatch();
        $this->assertInstanceOf('Dash\Router\MatchResult\SuccessfulMatch', $result);
    }

    public function testIsSuccess()
    {
        $this->assertTrue((new SuccessfulMatch())->isSuccess());
    }

    public function testRouteNamePrepending()
    {
        $result = new SuccessfulMatch();
        $this->assertNull($result->getRouteName());

        $result->prependRouteName('foo');
        $this->assertEquals('foo', $result->getRouteName());

        $result->prependRouteName('bar');
        $this->assertEquals('bar/foo', $result->getRouteName());
    }

    public function testAddParseResult()
    {
        $result = new SuccessfulMatch(['foo' => 'bar']);
        $result->addParseResult(new ParseResult(['foo' => 'baz', 'bat' => 'bar'], 0));
        $this->assertEquals(['foo' => 'baz', 'bat' => 'bar'], $result->getParams());
    }

    public function testMerge()
    {
        $result = new SuccessfulMatch(['foo' => 'bar']);
        $result->prependRouteName('foo');

        $result->merge(new SuccessfulMatch(['foo' => 'baz', 'bat' => 'bar']));
        $this->assertEquals(['foo' => 'baz', 'bat' => 'bar'], $result->getParams());
        $this->assertEquals('foo', $result->getRouteName());

        $otherMatch = new SuccessfulMatch();
        $otherMatch->prependRouteName('bar');
        $result->merge($otherMatch);
        $this->assertEquals('bar/foo', $result->getRouteName());
    }
}
