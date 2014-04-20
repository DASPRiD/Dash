<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Router\Http\Route;

use Dash\Router\Http\MatchResult\SuccessfulMatch;
use Dash\Router\Http\Parser\ParseResult;
use Dash\Router\Http\Route\Generic;
use Dash\Router\Http\RouteCollection\RouteCollection;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request;

/**
 * @covers Dash\Router\Http\Route\Generic
 */
class GenericTest extends TestCase
{
    /**
     * @var Generic
     */
    protected $route;

    /**
     * @var Request
     */
    protected $request;

    public function setUp()
    {
        $this->route = new Generic();

        $this->request = new Request();
        $this->request->setUri('http://example.com/foo/bar');
    }

    public function testNoMatchWithoutConfiguration()
    {
        $this->assertNull($this->route->match($this->request, 0));
    }

    public function testSuccessfulPathMatch()
    {
        $this->route->setPathParser($this->getSuccessfullPathParser());
        $match = $this->route->match($this->request, 4);

        $this->assertInstanceOf('Dash\Router\Http\MatchResult\SuccessfulMatch', $match);
        $this->assertEquals(['foo' => 'bar'], $match->getParams());
    }

    public function testFailedPathMatch()
    {
        $pathParser = $this->getMock('Dash\Router\Http\Parser\ParserInterface');
        $pathParser
            ->expects($this->once())
            ->method('parse')
            ->with($this->equalTo('/foo/bar'), $this->equalTo(4))
            ->will($this->returnValue(null));

        $this->route->setPathParser($pathParser);
        $this->assertNull($this->route->match($this->request, 4));
    }

    public function testIncompletePathMatch()
    {
        $this->route->setPathParser($this->getIncompletePathParser());
        $this->assertNull($this->route->match($this->request, 4));
    }

    public function testSuccessfulHostnameMatch()
    {
        $this->route->setPathParser($this->getSuccessfullPathParser());
        $this->route->setHostnameParser($this->getSuccessfullHostnameParser());
        $match = $this->route->match($this->request, 4);

        $this->assertInstanceOf('Dash\Router\Http\MatchResult\SuccessfulMatch', $match);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bat'], $match->getParams());
    }

    public function testFailedHostnameMatch()
    {
        $hostnameParser = $this->getMock('Dash\Router\Http\Parser\ParserInterface');
        $hostnameParser
            ->expects($this->once())
            ->method('parse')
            ->with($this->equalTo('example.com'), $this->equalTo(0))
            ->will($this->returnValue(null));

        $pathParser = $this->getMock('Dash\Router\Http\Parser\ParserInterface');
        $pathParser
            ->expects($this->never())
            ->method('parse');

        $this->route->setPathParser($pathParser);
        $this->route->setHostnameParser($hostnameParser);
        $this->assertNull($this->route->match($this->request, 4));
    }

    public function testIncompleteHostnameMatch()
    {
        $hostnameParser = $this->getMock('Dash\Router\Http\Parser\ParserInterface');
        $hostnameParser
            ->expects($this->once())
            ->method('parse')
            ->with($this->equalTo('example.com'), $this->equalTo(0))
            ->will($this->returnValue(new ParseResult(['baz' => 'bat'], 7)));

        $pathParser = $this->getMock('Dash\Router\Http\Parser\ParserInterface');
        $pathParser
            ->expects($this->never())
            ->method('parse');

        $this->route->setPathParser($pathParser);
        $this->route->setHostnameParser($hostnameParser);
        $this->assertNull($this->route->match($this->request, 4));
    }

    public function testEarlyReturnOnNonSecureScheme()
    {
        $pathParser = $this->getMock('Dash\Router\Http\Parser\ParserInterface');
        $pathParser
            ->expects($this->never())
            ->method('parse');

        $this->route->setPathParser($pathParser);
        $this->route->setSecure(true);

        $result = $this->route->match($this->request, 0);
        $this->assertInstanceOf('Dash\Router\Http\MatchResult\SchemeNotAllowed', $result);
        $this->assertEquals('https://example.com/foo/bar', $result->getAllowedUri());
    }

    public function testNoMatchWithWrongMethod()
    {
        $this->route->setPathParser($this->getSuccessfullPathParser());
        $this->route->setMethods('post');

        $result = $this->route->match($this->request, 4);
        $this->assertInstanceOf('Dash\Router\Http\MatchResult\MethodNotAllowed', $result);
        $this->assertEquals(['POST'], $result->getAllowedMethods());
    }

    public function testMatchWithWildcardMethod()
    {
        $this->route->setPathParser($this->getSuccessfullPathParser());
        $this->route->setMethods('*');
        $match = $this->route->match($this->request, 4);

        $this->assertInstanceOf('Dash\Router\Http\MatchResult\SuccessfulMatch', $match);
        $this->assertEquals(['foo' => 'bar'], $match->getParams());
    }

    public function testMatchWithMultipleMethods()
    {
        $this->route->setPathParser($this->getSuccessfullPathParser());
        $this->route->setMethods(['get', 'post']);
        $match = $this->route->match($this->request, 4);

        $this->assertInstanceOf('Dash\Router\Http\MatchResult\SuccessfulMatch', $match);
        $this->assertEquals(['foo' => 'bar'], $match->getParams());
    }

    public function testMatchOverridesDefaults()
    {
        $this->route->setDefaults(['foo' => 'bat', 'baz' =>'bat']);
        $this->route->setPathParser($this->getSuccessfullPathParser());
        $match = $this->route->match($this->request, 4);

        $this->assertInstanceOf('Dash\Router\Http\MatchResult\SuccessfulMatch', $match);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bat'], $match->getParams());
    }

    public function testSetMethodWithInvalidScalar()
    {
        $this->setExpectedException(
            'Dash\Router\Exception\InvalidArgumentException',
            '$methods must either be a string or an array'
        );

        $this->route->setMethods(1);
    }

    public function testSetInvalidMethod()
    {
        $this->setExpectedException(
            'Dash\Router\Exception\InvalidArgumentException',
            'FOO is not a valid HTTP method'
        );

        $this->route->setMethods('foo');
    }

    public function testIncompletePathMatchWithoutChildMatch()
    {
        $this->route->setChildren($this->getRouteCollection());
        $this->route->setPathParser($this->getIncompletePathParser());
        $this->assertNull($this->route->match($this->request, 4));
    }

    public function testIncompletePathMatchWithChildMatch()
    {
        $routeCollection = $this->getRouteCollection();

        $childMatch = new SuccessfulMatch(['baz' => 'bat']);

        $childRoute = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $childRoute
            ->expects($this->once())
            ->method('match')
            ->with($this->equalTo($this->request), $this->equalTo(5))
            ->will($this->returnValue($childMatch));

        $routeCollection->insert('child', $childRoute);

        $this->route->setChildren($routeCollection);
        $this->route->setPathParser($this->getIncompletePathParser());
        $match = $this->route->match($this->request, 4);

        $this->assertInstanceOf('Dash\Router\Http\MatchResult\SuccessfulMatch', $match);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bat'], $match->getParams());
    }

    public function testAssembleSecureSchema()
    {
        $this->route->setSecure(true);
        $assemblyResult = $this->route->assemble([]);

        $this->assertEquals('https:', $assemblyResult->generateUri('http', 'example.com', false));
    }

    public function testAssembleHostname()
    {
        $parser = $this->getMock('Dash\Router\Http\Parser\ParserInterface');
        $parser
            ->expects($this->once())
            ->method('compile')
            ->with($this->equalTo(['foo' => 'bar']), $this->equalTo(['baz' => 'bat']))
            ->will($this->returnValue('example.org'));

        $this->route->setHostnameParser($parser);
        $this->route->setDefaults(['baz' => 'bat']);
        $assemblyResult = $this->route->assemble(['foo' => 'bar']);

        $this->assertEquals('//example.org', $assemblyResult->generateUri('http', 'example.com', false));
    }

    public function testAssemblePath()
    {
        $parser = $this->getMock('Dash\Router\Http\Parser\ParserInterface');
        $parser
            ->expects($this->once())
            ->method('compile')
            ->with($this->equalTo(['foo' => 'bar']), $this->equalTo(['baz' => 'bat']))
            ->will($this->returnValue('/bar'));

        $this->route->setPathParser($parser);
        $this->route->setDefaults(['baz' => 'bat']);
        $assemblyResult = $this->route->assemble(['foo' => 'bar']);

        $this->assertEquals('/bar', $assemblyResult->generateUri('http', 'example.com', false));
    }

    public function testAssembleFailsWithoutChildren()
    {
        $this->setExpectedException('Dash\Router\Exception\RuntimeException', 'Route has no children to assemble');
        $this->route->assemble([], 'foo');
    }

    public function testAssemblePassesDownChildName()
    {
        $child = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $child
            ->expects($this->once())
            ->method('assemble')
            ->with($this->anything(), $this->equalTo('bar'));

        $routeCollection = $this->getRouteCollection();
        $routeCollection->insert('foo', $child);

        $this->route->setChildren($routeCollection);
        $this->route->assemble([], 'foo/bar');
    }

    public function testAssemblePassesNullWithoutFurtherChildren()
    {
        $child = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $child
            ->expects($this->once())
            ->method('assemble')
            ->with($this->anything(), $this->equalTo(null));

        $routeCollection = $this->getRouteCollection();
        $routeCollection->insert('foo', $child);

        $this->route->setChildren($routeCollection);
        $this->route->assemble([], 'foo');
    }

    public function testAssembleIgnoresTrailingSlash()
    {
        $child = $this->getMock('Dash\Router\Http\Route\RouteInterface');
        $child
            ->expects($this->once())
            ->method('assemble')
            ->with($this->anything(), $this->equalTo(null));

        $routeCollection = $this->getRouteCollection();
        $routeCollection->insert('foo', $child);

        $this->route->setChildren($routeCollection);
        $this->route->assemble([], 'foo/');
    }

    /**
     * @return RouteCollection
     */
    protected function getRouteCollection()
    {
        return new RouteCollection($this->getMock('Zend\ServiceManager\ServiceLocatorInterface'));
    }

    /**
     * @return \Dash\Router\Http\Parser\ParserInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getIncompletePathParser()
    {
        $pathParser = $this->getMock('Dash\Router\Http\Parser\ParserInterface');
        $pathParser
            ->expects($this->once())
            ->method('parse')
            ->with($this->equalTo('/foo/bar'), $this->equalTo(4))
            ->will($this->returnValue(new ParseResult(['foo' => 'bar'], 1)));

        return $pathParser;
    }

    /**
     * @return \Dash\Router\Http\Parser\ParserInterface||\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSuccessfullPathParser()
    {
        $pathParser = $this->getMock('Dash\Router\Http\Parser\ParserInterface');
        $pathParser
            ->expects($this->once())
            ->method('parse')
            ->with($this->equalTo('/foo/bar'), $this->equalTo(4))
            ->will($this->returnValue(new ParseResult(['foo' => 'bar'], 4)));

        return $pathParser;
    }

    /**
     * @return \Dash\Router\Http\Parser\ParserInterface||\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSuccessfullHostnameParser()
    {
        $hostnameParser = $this->getMock('Dash\Router\Http\Parser\ParserInterface');
        $hostnameParser
            ->expects($this->once())
            ->method('parse')
            ->with($this->equalTo('example.com'), $this->equalTo(0))
            ->will($this->returnValue(new ParseResult(['baz' => 'bat'], 11)));

        return $hostnameParser;
    }
}
