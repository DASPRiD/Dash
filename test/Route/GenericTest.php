<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Route;

use Dash\Exception\RuntimeException;
use Dash\Exception\UnexpectedValueException;
use Dash\MatchResult\MatchResultInterface;
use Dash\MatchResult\MethodNotAllowed;
use Dash\MatchResult\SchemeNotAllowed;
use Dash\MatchResult\SuccessfulMatch;
use Dash\Parser\ParseResult;
use Dash\Parser\ParserInterface;
use Dash\Route\AssemblyResult;
use Dash\Route\Generic;
use Dash\Route\RouteInterface;
use Dash\RouteCollection\RouteCollectionInterface;
use IteratorAggregate;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * @covers Dash\Route\Generic
 */
class GenericTest extends TestCase
{
    public function testNoMatchWithoutConfiguration()
    {
        $this->assertNull($this->buildRoute()->match($this->buildRequest(), 0));
    }

    public function testSuccessfulPathMatch()
    {
        $matchResult = $this->buildRoute([
            'path_parser' => $this->getSuccessfullPathParser(),
        ])->match($this->buildRequest(), 4);

        $this->assertInstanceOf(SuccessfulMatch::class, $matchResult);
        $this->assertEquals(['foo' => 'bar'], $matchResult->getParams());
    }

    public function testFailedPathMatch()
    {
        $pathParser = $this->prophesize(ParserInterface::class);
        $pathParser->parse('/foo/bar', 4)->willReturn(null);

        $matchResult = $this->buildRoute([
            'path_parser' => $pathParser->reveal(),
        ])->match($this->buildRequest(), 4);

        $this->assertNull($matchResult);
    }

    public function testIncompletePathMatch()
    {
        $matchResult = $this->buildRoute([
            'path_parser' => $this->getIncompletePathParser(),
        ])->match($this->buildRequest(), 4);

        $this->assertNull($matchResult);
    }

    public function testSuccessfulHostnameMatch()
    {
        $matchResult = $this->buildRoute([
            'path_parser' => $this->getSuccessfullPathParser(),
            'hostname_parser' => $this->getSuccessfullHostnameParser(),
        ])->match($this->buildRequest(), 4);

        $this->assertInstanceOf(SuccessfulMatch::class, $matchResult);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bat'], $matchResult->getParams());
    }

    public function testFailedHostnameMatch()
    {
        $hostnameParser = $this->prophesize(ParserInterface::class);
        $hostnameParser->parse('example.com', 0)->willReturn(null);

        $matchResult = $this->buildRoute([
            'path_parser' => $this->getSuccessfullPathParser(),
            'hostname_parser' => $hostnameParser->reveal(),
        ])->match($this->buildRequest(), 4);

        $this->assertNull($matchResult);
    }

    public function testIncompleteHostnameMatch()
    {
        $hostnameParser = $this->prophesize(ParserInterface::class);
        $hostnameParser->parse('example.com', 0)->willReturn(new ParseResult(['baz' => 'bat'], 7));

        $matchResult = $this->buildRoute([
            'path_parser' => $this->getSuccessfullPathParser(),
            'hostname_parser' => $hostnameParser->reveal(),
        ])->match($this->buildRequest(), 4);

        $this->assertNull($matchResult);
    }

    public function testFailedPortMatch()
    {
        $matchResult = $this->buildRoute([
            'path_parser' => $this->getSuccessfullPathParser(),
            'port' => 500,
        ])->match($this->buildRequest(), 4);

        $this->assertNull($matchResult);
    }

    public function testSuccessfulPortMatch()
    {
        $matchResult = $this->buildRoute([
            'path_parser' => $this->getSuccessfullPathParser(),
            'port' => 80,
        ])->match($this->buildRequest(), 4);

        $this->assertInstanceOf(SuccessfulMatch::class, $matchResult);
        $this->assertEquals(['foo' => 'bar'], $matchResult->getParams());
    }

    public function testEarlyReturnOnNonSecureScheme()
    {
        $pathParser = $this->prophesize(ParserInterface::class);
        $pathParser->parse()->shouldNotBeCalled();

        $matchResult = $this->buildRoute([
            'path_parser' => $pathParser->reveal(),
            'secure' => true,
        ])->match($this->buildRequest(), 0);

        $this->assertInstanceOf(SchemeNotAllowed::class, $matchResult);
        $this->assertEquals('https://example.com/foo/bar', $matchResult->getAllowedUri());
    }

    public function testSwitchToNonSecureScheme()
    {
        $matchResult = $this->buildRoute([
            'secure' => false,
        ])->match($this->buildRequest(true), 0);

        $this->assertInstanceOf(SchemeNotAllowed::class, $matchResult);
        $this->assertEquals('http://example.com/foo/bar', $matchResult->getAllowedUri());
    }

    public function testNoMatchWithEmptyMethod()
    {
        $matchResult = $this->buildRoute([
            'path_parser' => $this->getSuccessfullPathParser(),
            'methods' => [],
        ])->match($this->buildRequest(), 4);

        $this->assertNull($matchResult);
    }

    public function testMethodNotAllowedResultWithWrongMethod()
    {
        $matchResult = $this->buildRoute([
            'path_parser' => $this->getSuccessfullPathParser(),
            'methods' => ['post'],
        ])->match($this->buildRequest(), 4);

        $this->assertInstanceOf(MethodNotAllowed::class, $matchResult);
        $this->assertEquals(['POST'], $matchResult->getAllowedMethods());
    }

    public function testMatchWithWildcardMethod()
    {
        $matchResult = $this->buildRoute([
            'path_parser' => $this->getSuccessfullPathParser(),
            'methods' => null,
        ])->match($this->buildRequest(), 4);

        $this->assertInstanceOf(SuccessfulMatch::class, $matchResult);
        $this->assertEquals(['foo' => 'bar'], $matchResult->getParams());
    }

    public function testMatchWithMultipleMethods()
    {
        $matchResult = $this->buildRoute([
            'path_parser' => $this->getSuccessfullPathParser(),
            'methods' => ['get', 'post'],
        ])->match($this->buildRequest(), 4);

        $this->assertInstanceOf(SuccessfulMatch::class, $matchResult);
        $this->assertEquals(['foo' => 'bar'], $matchResult->getParams());
    }

    public function testMatchOverridesDefaults()
    {
        $matchResult = $this->buildRoute([
            'path_parser' => $this->getSuccessfullPathParser(),
            'defaults' => ['foo' => 'bat', 'baz' =>'bat'],
        ])->match($this->buildRequest(), 4);

        $this->assertInstanceOf(SuccessfulMatch::class, $matchResult);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bat'], $matchResult->getParams());
    }

    public function testIncompletePathMatchWithoutChildMatch()
    {
        $matchResult = $this->buildRoute([
            'path_parser' => $this->getIncompletePathParser(),
            'children' => $this->buildChildren(),
        ])->match($this->buildRequest(), 4);

        $this->assertNull($matchResult);
    }

    public function testIncompletePathMatchWithChildMatch()
    {
        $matchResult = $this->buildRoute([
            'path_parser' => $this->getIncompletePathParser(),
            'children' => $this->buildChildren([
                new SuccessfulMatch(['baz' => 'bat']),
            ]),
        ])->match($this->buildRequest(), 4);

        $this->assertInstanceOf(SuccessfulMatch::class, $matchResult);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bat'], $matchResult->getParams());
    }

    public function testAssembleSecureSchema()
    {
        $assemblyResult = $this->buildRoute([
            'secure' => true,
        ])->assemble([]);

        $this->assertAssemblyResult($assemblyResult, ['scheme' => 'https']);
    }

    public function testAssembleHostname()
    {
        $hostnameParser = $this->prophesize(ParserInterface::class);
        $hostnameParser->compile(['foo' => 'bar'], ['baz' => 'bat'])->willReturn('example.org');

        $assemblyResult = $this->buildRoute([
            'hostname_parser' => $hostnameParser->reveal(),
            'defaults' => ['baz' => 'bat'],
        ])->assemble(['foo' => 'bar']);

        $this->assertAssemblyResult($assemblyResult, ['host' => 'example.org']);
    }

    public function testAssemblePath()
    {
        $pathParser = $this->prophesize(ParserInterface::class);
        $pathParser->compile(['foo' => 'bar'], ['baz' => 'bat'])->willReturn('/bar');

        $assemblyResult = $this->buildRoute([
            'path_parser' => $pathParser->reveal(),
            'defaults' => ['baz' => 'bat'],
        ])->assemble(['foo' => 'bar']);

        $this->assertAssemblyResult($assemblyResult, ['path' => '/bar']);
    }

    public function testAssemblePort()
    {
        $assemblyResult = $this->buildRoute([
            'port' => 400,
        ])->assemble([]);

        $this->assertAssemblyResult($assemblyResult, ['port' => 400]);
    }

    public function testAssembleFailsWithoutChildren()
    {
        $this->setExpectedException(RuntimeException::class, 'Route has no children to assemble');
        $this->buildRoute()->assemble([], 'foo');
    }

    public function testAssemblePassesDownChildName()
    {
        $child = $this->prophesize(RouteInterface::class);
        $child->assemble([], 'bar')->shouldBeCalled();

        $children = $this->prophesize(RouteCollectionInterface::class);
        $children->get('foo')->willReturn($child->reveal());

        $this->buildRoute([
            'children' => $children->reveal(),
        ])->assemble([], 'foo/bar');
    }

    public function testAssemblePassesNullWithoutFurtherChildren()
    {
        $child = $this->prophesize(RouteInterface::class);
        $child->assemble([], null)->shouldBeCalled();

        $children = $this->prophesize(RouteCollectionInterface::class);
        $children->get('foo')->willReturn($child->reveal());

        $this->buildRoute([
            'children' => $children->reveal(),
        ])->assemble([], 'foo');
    }

    /**
     * @return ParserInterface
     */
    protected function getIncompletePathParser()
    {
        $pathParser = $this->prophesize(ParserInterface::class);
        $pathParser->parse('/foo/bar', 4)->willReturn(new ParseResult(['foo' => 'bar'], 1));

        return $pathParser->reveal();
    }

    /**
     * @return ParserInterface
     */
    protected function getSuccessfullPathParser()
    {
        $pathParser = $this->prophesize(ParserInterface::class);
        $pathParser->parse('/foo/bar', 4)->willReturn(new ParseResult(['foo' => 'bar'], 4));

        return $pathParser->reveal();
    }

    /**
     * @return ParserInterface
     */
    protected function getSuccessfullHostnameParser()
    {
        $hostnameParser = $this->prophesize(ParserInterface::class);
        $hostnameParser->parse('example.com', 0)->willReturn(new ParseResult(['baz' => 'bat'], 11));

        return $hostnameParser->reveal();
    }

    /**
     * @param  array $options
     * @return Generic
     */
    protected function buildRoute(array $options = [])
    {
        return new Generic(
            isset($options['path_parser']) ? $options['path_parser'] : null,
            isset($options['hostname_parser']) ? $options['hostname_parser'] : null,
            isset($options['methods']) ? $options['methods'] : null,
            isset($options['secure']) ? $options['secure'] : null,
            isset($options['port']) ? $options['port'] : null,
            isset($options['defaults']) ? $options['defaults'] : [],
            isset($options['children']) ? $options['children'] : null
        );
    }

    /**
     * @return ServerRequestInterface
     */
    protected function buildRequest($secure = false)
    {
        $switchScheme = $this->prophesize(UriInterface::class);
        $switchScheme->__toString()->willReturn(($secure ? 'http' : 'https') . '://example.com/foo/bar');

        $uri = $this->prophesize(UriInterface::class);
        $uri->getScheme()->willReturn($secure ? 'https' : 'http');
        $uri->getHost()->willReturn('example.com');
        $uri->getPort()->willReturn(80);
        $uri->getPath()->willReturn('/foo/bar');
        $uri->withScheme($secure ? 'http' : 'https')->willReturn($switchScheme->reveal());

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getMethod()->willReturn('GET');
        $request->getUri()->willReturn($uri->reveal());

        return $request->reveal();
    }

    /**
     * @param  array $routes
     * @return RouteCollectionInterface
     */
    protected function buildChildren(array $routes = [])
    {
        $children = $this->prophesize(RouteCollectionInterface::class);
        $children->willImplement(IteratorAggregate::class);

        $testCase = $this;

        $children->getIterator()->will(function () use ($testCase, $routes) {
            foreach ($routes as $matchResult) {
                if ('no-call' === $matchResult) {
                    $child = $testCase->prophesize(RouteInterface::class);
                    $child->match(
                        Argument::type(ServerRequestInterface::class),
                        5
                    )->shouldNotBeCalled();
                } else {
                    $child = $testCase->prophesize(RouteInterface::class);
                    $child->match(
                        Argument::type(ServerRequestInterface::class),
                        5
                    )->shouldBeCalled()->willReturn($matchResult);
                }

                yield $child->reveal();
            }
        });

        return $children->reveal();
    }

    /**
     * @param array $values
     */
    protected function assertAssemblyResult(AssemblyResult $assemblyResult, array $values)
    {
        foreach ($assemblyResult as $key => $value) {
            if (!isset($values[$key])) {
                $this->assertNull($value);
            } else {
                $this->assertSame($values[$key], $value);
            }
        }
    }
}
