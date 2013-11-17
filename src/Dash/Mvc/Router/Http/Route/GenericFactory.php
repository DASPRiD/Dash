<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Mvc\Router\Http\Route;

use Dash\Mvc\Router\Http\RouteCollection\RouteCollection;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\MutableCreationOptionsInterface;

class GenericFactory implements FactoryInterface, MutableCreationOptionsInterface
{
    /**
     * @var null|array
     */
    protected $createOptions;

    public function setCreationOptions(array $options)
    {
        $this->createOptions = $options;
    }

    /**
     * @return Generic
     */
    public function createService(ServiceLocatorInterface $routeManager)
    {
        $route = new Generic();

        if ($this->createOptions === null) {
            return $route;
        }

        $options       = $this->createOptions;
        $parserManager = $routeManager->getServiceLocator()->get('Dash\Mvc\Router\Http\Parser\ParserManager');

        if (isset($options['path_parser'])) {
            $route->setPathParser($parserManager->get($options['path_parser'], $options));
        } elseif (null !== ($options['path'] = (isset($options['path']) ? $options['path'] : (isset($options[0]) ? $options[0] : null)))) {
            $route->setPathParser($parserManager->get('pathsegment', $options));
        }

        if (isset($options['hostname_parser'])) {
            $route->setHostnameParser($parserManager->get($options['hostname_parser'], $options));
        } elseif (null !== ($options['hostname'] = (isset($options['hostname']) ? $options['hostname'] : null))) {
            $route->setHostnameParser($parserManager->get('hostnamesegment', $options));
        }

        if (null !== ($methods = (isset($options['methods']) ? $options['methods'] : (isset($options[3]) ? $options[3] : null)))) {
            $route->setMethods($methods);
        }

        if (isset($options['secure'])) {
            $route->setSecure($options['secure']);
        }

        $defaults = (isset($options['defaults']) ? $options['defaults'] : []);

        if (isset($options[1]) && !isset($options['controller'])) {
            $defaults['controller'] = $options[1];
        }

        if (isset($options[2]) && !isset($options['action'])) {
            $defaults['action'] = $options[2];
        }

        $route->setDefaults($defaults);

        if (isset($options['children'])) {
            $routeList = new RouteCollection($routeManager);

            foreach ($options['children'] as $name => $child) {
                $routeList->insert($name, $child, is_array($child) && isset($child['priority']) ? $child['priority'] : 1);
            }

            $route->setChildren($routeList);
        }

        return $route;
    }
}