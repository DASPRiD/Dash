<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashTest\Router\Http\Parser\Asset;

use Dash\Router\Http\Parser\CacheAwareInterface;
use Dash\Router\Http\Parser\ParserInterface;

/**
 * This is a workaround, since stubbing multiple interfaces is not yet in any
 * stable tag.
 *
 * @see https://github.com/sebastianbergmann/phpunit-mock-objects/commit/73e7b0766ad6223e713ac87cfe5d4e211bd18af0
 */
interface CacheAwareParserInterface extends CacheAwareInterface, ParserInterface
{
}
