<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Router\Http\MatchResult;

use Dash\Router\Http\MatchResult\SchemeNotAllowed;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Router\Http\MatchResult\SchemeNotAllowed
 */
class SchemeNotAllowedTest extends TestCase
{
    public function testIsSuccess()
    {
        $this->assertFalse((new SchemeNotAllowed(''))->isSuccess());
    }

    public function testGetAllowedUri()
    {
        $this->assertEquals('https://example.com', (new SchemeNotAllowed('https://example.com'))->getAllowedUri());
    }
}
