<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Parser;

use Dash\AbstractPluginManagerFactory;

/**
 * Factory for the parser manager.
 */
class ParserManagerFactory extends AbstractPluginManagerFactory
{
    /**
     * {@inheritdoc}
     */
    protected function getConfigKey()
    {
        return 'parser_manager';
    }

    /**
     * {@inheritdoc}
     */
    protected function getClassName()
    {
        return ParserManager::class;
    }
}
