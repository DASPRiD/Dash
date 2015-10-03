<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Parser;

use Dash\Exception\InvalidArgumentException;
use Dash\Exception\RuntimeException;
use Dash\Parser\ParseResult;
use Dash\Parser\Segment;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Parser\Segment
 */
class SegmentTest extends TestCase
{
    public function parserProvider()
    {
        return [
            'simple-match' => [
                new Segment('/', '/:foo', []),
                '/bar',
                0,
                ['foo' => 'bar'],
            ],
            'no-match-without-leading-slash' => [
                new Segment('/', ':foo', []),
                '/bar/',
            ],
            'offset-skips-beginning' => [
                new Segment('/', ':foo', []),
                '/bar',
                1,
                ['foo' => 'bar'],
            ],
            'match-overrides-default' => [
                new Segment('/', '/:foo', []),
                '/bar',
                0,
                ['foo' => 'bar'],
                ['foo' => 'baz'],
            ],
            'constraints-prevent-match' => [
                new Segment('/', '/:foo', ['foo' => '\d+']),
                '/bar',
            ],
            'constraints-allow-match' => [
                new Segment('/', '/:foo', ['foo' => '\d+']),
                '/123',
                0,
                ['foo' => '123'],
            ],
            'constraints-override-non-standard-delimiter' => [
                new Segment('/', '/:foo{-}/bar', ['foo' => '[^/]+']),
                '/foo-bar/bar',
                0,
                ['foo' => 'foo-bar'],
            ],
            'constraints-with-parantheses-dont-break-parameter-map' => [
                new Segment('/', '/:foo/:bar', ['foo' => '(bar)']),
                '/bar/baz',
                0,
                ['foo' => 'bar', 'bar' => 'baz']
            ],
            'simple-match-with-optional-parameter' => [
                new Segment('/', '/[:foo]', []),
                '/',
                0,
                [],
            ],
            'optional-parameter-is-ignored' => [
                new Segment('/', '/:foo[/:bar]', []),
                '/bar',
                0,
                ['foo' => 'bar'],
            ],
            'optional-parameter-is-provided-with-default' => [
                new Segment('/', '/:foo[/:bar]', []),
                '/bar',
                0,
                ['foo' => 'bar'],
                ['bar' => 'baz'],
            ],
            'optional-parameter-is-consumed' => [
                new Segment('/', '/:foo[/:bar]', []),
                '/bar/baz',
                0,
                ['foo' => 'bar', 'bar' => 'baz']
            ],
            'optional-group-is-discarded-with-missing-parameter' => [
                new Segment('/', '/:foo[/:bar/:baz]', []),
                '/bar',
                0,
                ['foo' => 'bar'],
                ['bar' => 'baz'],
            ],
            'optional-group-within-optional-group-is-ignored' => [
                new Segment('/', '/:foo[/:bar[/:baz]]', []),
                '/bar',
                0,
                ['foo' => 'bar'],
                ['bar' => 'baz', 'baz' => 'bat'],
            ],
            'non-standard-delimiter-before-parameter' => [
                new Segment('/', '/foo-:bar', []),
                '/foo-baz',
                0,
                ['bar' => 'baz'],
            ],
            'non-standard-delimiter-between-parameters' => [
                new Segment('/', '/:foo{-}-:bar', []),
                '/bar-baz',
                0,
                ['foo' => 'bar', 'bar' => 'baz'],
            ],
            'non-standard-delimiter-before-optional-parameter' => [
                new Segment('/', '/:foo{-/}[-:bar]/:baz', []),
                '/bar-baz/bat',
                0,
                ['foo' => 'bar', 'bar' => 'baz', 'baz' => 'bat'],
            ],
            'non-standard-delimiter-before-ignored-optional-parameter' => [
                new Segment('/', '/:foo{-/}[-:bar]/:baz', []),
                '/bar/bat',
                0,
                ['foo' => 'bar', 'baz' => 'bat'],
            ],
            'parameter-with-dash-in-name' => [
                new Segment('/', '/:foo-bar', []),
                '/baz',
                0,
                ['foo-bar' => 'baz'],
            ],
            'different-delimiter' => [
                new Segment('.', ':foo.example.com', []),
                'bar.example.com',
                0,
                ['foo' => 'bar'],
            ],
        ];
    }

    public function parseExceptionsProvider()
    {
        return [
            'unbalanced-brackets' => [
                '[',
                RuntimeException::class,
                'Found unbalanced brackets'
            ],
            'closing-bracket-without-opening-bracket' => [
                ']',
                RuntimeException::class,
                'Found closing bracket without matching opening bracket'
            ],
            'empty-parameter-name' => [
                ':',
                RuntimeException::class,
                'Found empty parameter name'
            ],
        ];
    }

    /**
     * @dataProvider parserProvider
     * @param Segment     $parser
     * @param string      $input
     * @param int         $offset
     * @param array       $params
     * @param array|null  $defaults
     */
    public function testParsing(Segment $parser, $input, $offset = 0, array $params = null)
    {
        $result = $parser->parse($input, $offset);

        if ($params === null) {
            $this->assertNull($result);
        } else {
            $this->assertInstanceOf(ParseResult::class, $result);
            $this->assertSame($params, $result->getParams());
        }
    }

    /**
     * @dataProvider parserProvider
     * @param Segment     $parser
     * @param string      $input
     * @param int         $offset
     * @param array       $params
     * @param array|null  $defaults
     */
    public function testCompiling(Segment $parser, $input, $offset = 0, array $params = null, array $defaults = [])
    {
        if ($params === null) {
            // Input which will not parse are not tested for compiling.
            return;
        }

        $result = $parser->compile($params, $defaults);
        $this->assertSame(substr($input, $offset), $result);
    }

    /**
     * @dataProvider parseExceptionsProvider
     * @param string $route
     * @param string $exceptionName
     * @param string $exceptionMessage
     */
    public function testParseExceptions($route, $exceptionName, $exceptionMessage)
    {
        $this->setExpectedException($exceptionName, $exceptionMessage);
        $segment = new Segment('/', $route, []);
        $segment->compile([], []);
    }

    public function testCompileWithMissingParameterInRoot()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'Missing parameter "foo"');
        $route = new Segment('/', '/:foo', []);
        $route->compile([], []);
    }
}
