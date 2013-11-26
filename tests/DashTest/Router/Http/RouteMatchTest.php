<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Router\Http;

use Dash\Router\Http\Parser\ParseResult;
use Dash\Router\Http\RouteMatch;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Router\Http\RouteMatch
 */
class RouteMatchTest extends TestCase
{
    public function testConstructorInjection()
    {
        $routeMatch = new RouteMatch(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $routeMatch->getParams());
    }

    public function testPrependRouteName()
    {
        $routeMatch = new RouteMatch();
        $this->assertNull($routeMatch->getRouteName());

        $routeMatch->prependRouteName('foo');
        $this->assertEquals('foo', $routeMatch->getRouteName());

        $routeMatch->prependRouteName('bar');
        $this->assertEquals('bar/foo', $routeMatch->getRouteName());
    }

    public function testAddParseResult()
    {
        $routeMatch = new RouteMatch(['foo' => 'bar']);

        $routeMatch->addParseResult(new ParseResult(['foo' => 'baz'], 0));
        $this->assertEquals(['foo' => 'baz'], $routeMatch->getParams());
    }

    public function testMerge()
    {
        $routeMatch = new RouteMatch(['foo' => 'bar']);

        $routeMatch->merge(new RouteMatch(['foo' => 'baz']));
        $this->assertEquals(['foo' => 'baz'], $routeMatch->getParams());
    }

    public function testSetParam()
    {
        $routeMatch = new RouteMatch();
        $routeMatch->setParam('foo', 'bar');
        $this->assertEquals(['foo' => 'bar'], $routeMatch->getParams());
    }

    public function testGetExistingParam()
    {
        $routeMatch = new RouteMatch();
        $routeMatch->setParam('foo', 'bar');
        $this->assertEquals('bar', $routeMatch->getParam('foo'));
    }

    public function testGetNonExistingParam()
    {
        $routeMatch = new RouteMatch();
        $this->assertNull($routeMatch->getParam('foo'));
        $this->assertEquals('baz', $routeMatch->getParam('foo', 'baz'));
    }
}
