<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Factory for the default router.
 */
class RouterFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @return Router
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new Router(
            $container->get('DashRootRouteCollection'),
            $container->get('DashBaseUri')
        );
    }
}
