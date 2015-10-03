<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Parser;

use Dash\Parser\ParserManagerFactory;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\ServiceManager\ServiceManager;

/**
 * @covers Dash\Parser\ParserManagerFactory
 */
class ParserManagerFactoryTest extends TestCase
{
    public function testFactorySucceedsWithoutConfig()
    {
        $factory = new ParserManagerFactory();
        $factory($this->getServiceLocator([]), '');
    }

    public function testFactoryWithConfig()
    {
        $factory       = new ParserManagerFactory();
        $parserManager = $factory($this->getServiceLocator([
            'dash' => [
                'parser_manager' => [
                    'services' => [
                        'foo' => [],
                    ],
                ],
            ],
        ]), '');

        $this->assertTrue($parserManager->has('foo'));
    }

    /**
     * @param  array $config
     * @return ServiceManager
     */
    protected function getServiceLocator(array $config)
    {
        return new ServiceManager([
            'services' => [
                'config' => $config,
            ]
        ]);
    }
}
