<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\MatchResult;

use Dash\MatchResult\MethodNotAllowed;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\MatchResult\MethodNotAllowed
 */
class MethodNotAllowedTest extends TestCase
{
    public function testIsSuccess()
    {
        $this->assertFalse((new MethodNotAllowed([]))->isSuccess());
    }

    public function testGetAllowedMethods()
    {
        $this->assertEquals(['GET'], (new MethodNotAllowed(['GET']))->getAllowedMethods());
    }

    public function testMerge()
    {
        $result = new MethodNotAllowed(['GET', 'PUT']);
        $result->merge(new MethodNotAllowed(['POST', 'GET']));
        $this->assertEquals(['GET', 'PUT', 'POST'], $result->getAllowedMethods());
    }
}
