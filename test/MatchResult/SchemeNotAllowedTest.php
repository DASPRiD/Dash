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
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\MatchResult\SchemeNotAllowed
 */
class SchemeNotAllowedTest extends TestCase
{
    public function testIsSuccess()
    {
        $this->assertFalse((new SchemeNotAllowed(new Uri()))->isSuccess());
    }

    public function testGetAllowedUri()
    {
        $uri = new Uri('https://example.com');
        $this->assertSame($uri, (new SchemeNotAllowed($uri))->getAllowedUri());
    }

    public function testModifyResponse()
    {
        $result   = new SchemeNotAllowed(new Uri('https://example.com'));
        $response = $result->modifyResponse(new Response());
        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('https://example.com', $response->getHeaderLine('Location'));
    }
}
