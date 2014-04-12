<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Router;

use Dash\Router\MatchResult;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Router\MatchResult
 */
class MatchResultTest extends TestCase
{
    public function testInvalidPayload()
    {
        $this->setExpectedException(
            'Dash\Router\Exception\InvalidArgumentException',
            'Payload must either implement ResponseInterface or RouteMatchInterface, string given'
        );
        new MatchResult('foo');
    }

    public function testResponsePayload()
    {
        $response    = $this->getMock('Zend\Stdlib\ResponseInterface');
        $matchResult = new MatchResult($response);

        $this->assertTrue($matchResult->hasResponse());
        $this->assertFalse($matchResult->hasRouteMatch());
        $this->assertSame($response, $matchResult->getResponse());
        $this->assertNull($matchResult->getRouteMatch());
    }

    public function testRouteMatchPayload()
    {
        $routeMatch  = $this->getMock('Dash\Router\RouteMatchInterface');
        $matchResult = new MatchResult($routeMatch);

        $this->assertFalse($matchResult->hasResponse());
        $this->assertTrue($matchResult->hasRouteMatch());
        $this->assertNull($matchResult->getResponse());
        $this->assertSame($routeMatch, $matchResult->getRouteMatch());
    }
}
