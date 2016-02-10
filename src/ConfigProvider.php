<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash;

/**
 * Config provider for the dependency config.
 */
class ConfigProvider
{
    public function __invoke()
    {
        return require __DIR__ . '/../config/dependencies.config.php';
    }
}
