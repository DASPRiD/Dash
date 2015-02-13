<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Router\MatchResult;

use Dash\Router\MatchResult\UnsuccessfulMatch;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Router\MatchResult\UnsuccessfulMatch
 */
class UnsuccessfulMatchTest extends TestCase
{
    public function testIsSuccess()
    {
        $this->assertFalse((new UnsuccessfulMatch())->isSuccess());
    }
}
