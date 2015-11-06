<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest;

use Dash\Exception\DomainException;
use Dash\MatchResult;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\UriInterface;

/**
 * @covers Dash\MatchResult
 */
class MatchResultTest extends TestCase
{
    public function testSuccess()
    {
        $matchResult = MatchResult::fromSuccess(['foo' => 'bar']);

        $this->assertTrue($matchResult->isSuccess());
        $this->assertFalse($matchResult->isMethodFailure());
        $this->assertFalse($matchResult->isSchemeFailure());
        $this->assertSame(['foo' => 'bar'], $matchResult->getParams());
        $this->assertNull($matchResult->getRouteName());

        try {
            $matchResult->getAllowedMethods();
            $this->fail('An expected DomainException was not raised');
        } catch (DomainException $e) {
            $this->assertSame('Allowed methods are only available on method failure', $e->getMessage());
        }

        try {
            $matchResult->getAbsoluteUri();
            $this->fail('An expected DomainException was not raised');
        } catch (DomainException $e) {
            $this->assertSame('Absolute URI is only available on scheme failure', $e->getMessage());
        }
    }

    public function testMethodFailure()
    {
        $matchResult = MatchResult::fromMethodFailure(['GET', 'POST']);

        $this->assertFalse($matchResult->isSuccess());
        $this->assertTrue($matchResult->isMethodFailure());
        $this->assertFalse($matchResult->isSchemeFailure());
        $this->assertSame(['GET', 'POST'], $matchResult->getAllowedMethods());

        try {
            $matchResult->getRouteName();
            $this->fail('An expected DomainException was not raised');
        } catch (DomainException $e) {
            $this->assertSame('Route name is only available on successful match', $e->getMessage());
        }

        try {
            $matchResult->getParams();
            $this->fail('An expected DomainException was not raised');
        } catch (DomainException $e) {
            $this->assertSame('Params are only available on successful match', $e->getMessage());
        }

        try {
            $matchResult->getAbsoluteUri();
            $this->fail('An expected DomainException was not raised');
        } catch (DomainException $e) {
            $this->assertSame('Absolute URI is only available on scheme failure', $e->getMessage());
        }
    }

    public function testSchemeFailure()
    {
        $absoluteUri = $this->prophesize(UriInterface::class)->reveal();
        $matchResult = MatchResult::fromSchemeFailure($absoluteUri);

        $this->assertFalse($matchResult->isSuccess());
        $this->assertFalse($matchResult->isMethodFailure());
        $this->assertTrue($matchResult->isSchemeFailure());
        $this->assertSame($absoluteUri, $matchResult->getAbsoluteUri());

        try {
            $matchResult->getRouteName();
            $this->fail('An expected DomainException was not raised');
        } catch (DomainException $e) {
            $this->assertSame('Route name is only available on successful match', $e->getMessage());
        }

        try {
            $matchResult->getParams();
            $this->fail('An expected DomainException was not raised');
        } catch (DomainException $e) {
            $this->assertSame('Params are only available on successful match', $e->getMessage());
        }

        try {
            $matchResult->getAllowedMethods();
            $this->fail('An expected DomainException was not raised');
        } catch (DomainException $e) {
            $this->assertSame('Allowed methods are only available on method failure', $e->getMessage());
        }
    }

    public function testMatchFailure()
    {
        $matchResult = MatchResult::fromMatchFailure();

        $this->assertFalse($matchResult->isSuccess());
        $this->assertFalse($matchResult->isMethodFailure());
        $this->assertFalse($matchResult->isSchemeFailure());

        try {
            $matchResult->getRouteName();
            $this->fail('An expected DomainException was not raised');
        } catch (DomainException $e) {
            $this->assertSame('Route name is only available on successful match', $e->getMessage());
        }

        try {
            $matchResult->getParams();
            $this->fail('An expected DomainException was not raised');
        } catch (DomainException $e) {
            $this->assertSame('Params are only available on successful match', $e->getMessage());
        }

        try {
            $matchResult->getAllowedMethods();
            $this->fail('An expected DomainException was not raised');
        } catch (DomainException $e) {
            $this->assertSame('Allowed methods are only available on method failure', $e->getMessage());
        }

        try {
            $matchResult->getAbsoluteUri();
            $this->fail('An expected DomainException was not raised');
        } catch (DomainException $e) {
            $this->assertSame('Absolute URI is only available on scheme failure', $e->getMessage());
        }
    }

    public function testChildMatchMergingParams()
    {
        $matchResult = MatchResult::fromChildMatch(
            MatchResult::fromSuccess(['foo' => 'bar', 'baz' => 'bat']),
            ['foo' => 'bat', 'bar' => 'bar'],
            null
        );

        $this->assertTrue($matchResult->isSuccess());
        $this->assertSame([
            'foo' => 'bar',
            'baz' => 'bat',
            'bar' => 'bar',
        ], $matchResult->getParams());
    }

    public function testChildMatchMergingWithoutRouteName()
    {
        $matchResult = MatchResult::fromChildMatch(
            MatchResult::fromSuccess([]),
            [],
            'foo'
        );

        $this->assertTrue($matchResult->isSuccess());
        $this->assertSame('foo', $matchResult->getRouteName());
    }

    public function testChildMatchMergingWithRouteName()
    {
        $matchResult = MatchResult::fromChildMatch(
            MatchResult::fromChildMatch(MatchResult::fromSuccess([]), [], 'bar'),
            [],
            'foo'
        );

        $this->assertTrue($matchResult->isSuccess());
        $this->assertSame('foo/bar', $matchResult->getRouteName());
    }

    public function testChildMatchMergingWithFailure()
    {
        $this->setExpectedException(DomainException::class, 'Child match must be a successful match result');
        MatchResult::fromChildMatch(MatchResult::fromMatchFailure(), [], '');
    }

    public function testMergeMethodFailures()
    {
        $matchResult = MatchResult::mergeMethodFailures(
            MatchResult::fromMethodFailure(['GET', 'PUT']),
            MatchResult::fromMethodFailure(['POST', 'GET'])
        );
        $this->assertEquals(['GET', 'PUT', 'POST'], $matchResult->getAllowedMethods());
    }

    public function testMergeMethodFailuresWithIncompatibleMatchResults()
    {
        $this->setExpectedException(DomainException::class, 'Both match results must be method failures');
        MatchResult::mergeMethodFailures(MatchResult::fromMatchFailure(), MatchResult::fromMatchFailure());
    }
}
