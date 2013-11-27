<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\Http\Parser;

use Zend\Cache\Storage\StorageInterface;

/**
 * Interface for parsers which are cache aware.
 */
interface CacheAwareInterface
{
    /**
     * Sets a cache used by the parser.
     *
     * @param null|StorageInterface $cache
     */
    public function setCache(StorageInterface $cache = null);
}
