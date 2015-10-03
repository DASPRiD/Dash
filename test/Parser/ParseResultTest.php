<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Parser;

use Dash\Parser\ParseResult;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Parser\ParseResult
 */
class ParseResultTest extends TestCase
{
    public function testOutputEqualsInput()
    {
        $parseResult = new ParseResult(['foo' => 'bar'], 10);
        $this->assertEquals(['foo' => 'bar'], $parseResult->getParams());
        $this->assertEquals(10, $parseResult->getMatchLength());
    }
}
