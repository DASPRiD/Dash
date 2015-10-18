<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Route;

use Dash\Parser\ParserManager;
use Dash\RouteCollection\LazyRouteCollection;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class GenericFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @return Generic
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if ($options === null) {
            return new Generic();
        }

        // Parameter normalization
        if (!isset($options['path']) && isset($options[0])) {
            $options['path'] = $options[0];
        }

        if (isset($options['defaults']) && isset($options[1])) {
            $options['defaults'] += $options[1];
        } elseif (isset($options[1])) {
            $options['defaults'] = $options[1];
        }

        if (isset($options['methods']) && isset($options[2])) {
            $options['methods'] = array_merge($options[2], $options['methods']);
        } elseif (isset($options[2])) {
            $options['methods'] = $options[2];
        }

        // Try to retrive path and hostname parser
        $parserManager = $container->get(ParserManager::class);

        if (isset($options['path_parser'])) {
            $pathParser = $parserManager->get($options['path_parser'], $options);
        } elseif (isset($options['path'])) {
            $pathParser = $parserManager->get('PathSegment', $options);
        } else {
            $pathParser = null;
        }

        if (isset($options['hostname_parser'])) {
            $hostnameParser = $parserManager->get($options['hostname_parser'], $options);
        } elseif (isset($options['hostname'])) {
            $hostnameParser = $parserManager->get('HostnameSegment', $options);
        } else {
            $hostnameParser = null;
        }

        // Setup children if they exist
        if (isset($options['children'])) {
            $children = new LazyRouteCollection($container->get(RouteManager::class), $options['children']);
        } else {
            $children = null;
        }

        return new Generic(
            $pathParser,
            $hostnameParser,
            isset($options['methods']) ? $options['methods'] : null,
            isset($options['secure']) ? $options['secure'] : null,
            isset($options['port']) ? $options['port'] : null,
            isset($options['defaults']) ? $options['defaults'] : [],
            $children
        );
    }
}
