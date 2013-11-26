<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Router\Http\Parser;

use Dash\Router\Http\Parser\ParserManager;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Router\Http\Parser\ParserManager
 */
class ParserManagerTest extends TestCase
{
    public function testPassOnValidPlugin()
    {
        $parserManager = new ParserManager();
        $parserManager->validatePlugin($this->getMock('Dash\Router\Http\Parser\ParserInterface'));
        // No assertions required, this will fail if an exception would occur.
    }

    public function testFailOnInvalidPlugin()
    {
        $this->setExpectedException(
            'Zend\ServiceManager\Exception\RuntimeException',
            'Plugin of type NULL is invalid; must implement Dash\Router\Http\Parser\ParserInterface'
        );

        $parserManager = new ParserManager();
        $parserManager->validatePlugin(null);
    }
}
