<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\MatchResult;

use Dash\MatchResult\UnsuccessfulMatch;
use GuzzleHttp\Psr7\Response;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\MatchResult\UnsuccessfulMatch
 */
class UnsuccessfulMatchTest extends TestCase
{
    public function testIsSuccess()
    {
        $this->assertFalse((new UnsuccessfulMatch())->isSuccess());
    }

    public function testModifyResponse()
    {
        $result = new UnsuccessfulMatch();
        $this->assertSame(404, $result->modifyResponse(new Response())->getStatusCode());
    }
}
