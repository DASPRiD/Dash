<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash;

use Dash\Exception\OutOfBoundsException;
use Interop\Container\ContainerInterface;

/**
 * Factory for a configured base URI.
 */
class BaseUriFactory
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];

        if (!isset($config['dash']['base_uri'])) {
            throw new OutOfBoundsException('Missing "base_uri" key in "dash" section');
        }

        return $config['dash']['base_uri'];
    }
}
