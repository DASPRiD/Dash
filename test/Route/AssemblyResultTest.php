<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Route;

use Dash\Route\AssemblyResult;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers Dash\Route\AssemblyResult
 */
class AssemblyResultTest extends TestCase
{
    public function paramProvider()
    {
        return [
            'no-params' => [
                [],
                '',
            ],
            'path-only' => [
                ['path' => '/foo'],
                '/foo',
            ],
            'query-only' => [
                ['query' => ['foo' => 'bar']],
                '?foo=bar',
            ],
            'fragment-only' => [
                ['fragment' => 'foo'],
                '#foo',
            ],
            'path-query-and-fragment' => [
                ['path' => '/foo', 'query' => ['foo' => 'bar'], 'fragment' => 'foo'],
                '/foo?foo=bar#foo',
            ],
            'different-host' => [
                ['host' => 'example.org'],
                '//example.org',
            ],
            'different-host-with-path' => [
                ['host' => 'example.org', 'path' => '/foo'],
                '//example.org/foo',
            ],
            'different-scheme' => [
                ['scheme' => 'https'],
                'https:',
            ],
            'different-scheme-with-path' => [
                ['scheme' => 'https', 'path' => '/foo'],
                'https:/foo',
            ],
            'force-canonical' => [
                [],
                'http://example.com',
                true
            ],
        ];
    }

    /**
     * @dataProvider paramProvider
     * @param array  $params
     * @param string $expectedUri
     * @param bool   $forceCanonical
     */
    public function testGenerateUri(array $params, $expectedUri, $forceCanonical = false)
    {
        $assemblyResult = new AssemblyResult();

        foreach ($params as $key => $value) {
            $assemblyResult->{$key} = $value;
        }

        $this->assertEquals(
            $expectedUri,
            $assemblyResult->generateUri('http', 'example.com', $forceCanonical)
        );
    }
}
