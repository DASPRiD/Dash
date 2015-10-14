<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\MatchResult;

use Dash\MatchResult\SchemeNotAllowed;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\UriInterface;

/**
 * @covers Dash\MatchResult\SchemeNotAllowed
 */
class SchemeNotAllowedTest extends TestCase
{
    public function testIsFailure()
    {
        $this->assertFalse((new SchemeNotAllowed($this->getMock(UriInterface::class)))->isSuccess());
    }

    public function testGetAllowedUri()
    {
        $uri = $this->getMock(UriInterface::class);
        $this->assertSame($uri, (new SchemeNotAllowed($uri))->getAllowedUri());
    }
}
