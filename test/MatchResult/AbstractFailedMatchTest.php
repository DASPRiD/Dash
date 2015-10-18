<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\MatchResult;

use Dash\MatchResult\AbstractFailedMatch;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\MatchResult\AbstractFailedMatch
 */
class AbstractFailedMatchTest extends TestCase
{
    public function testIsFailure()
    {
        $this->assertFalse($this->prophesize()->willExtend(AbstractFailedMatch::class)->reveal()->isSuccess());
    }
}
