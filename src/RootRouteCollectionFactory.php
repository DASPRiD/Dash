<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash;

use Dash\Route\RouteManager;
use Dash\RouteCollection\LazyRouteCollection;
use Interop\Container\ContainerInterface;

/**
 * Factory for the root route collection.
 */
class RootRouteCollectionFactory
{
    /**
     * {@inheritdoc}
     *
     * @return LazyRouteCollection
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];

        return new LazyRouteCollection(
            $container->get(RouteManager::class),
            isset($config['dash']['routes']) ? $config['dash']['routes'] : []
        );
    }
}
