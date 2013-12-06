<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\Http;

use Dash\Router\Exception;
use Dash\Router\Http\Route\RouteInterface;
use Dash\Router\Http\RouteCollection\RouteCollectionInterface;
use Dash\Router\RouterInterface;
use Zend\Http\Request as HttpRequest;
use Zend\Stdlib\RequestInterface;
use Zend\Uri\Http as HttpUri;

class Router implements RouterInterface
{
    /**
     * @var RouteCollectionInterface
     */
    protected $routeCollection;

    /**
     * @var HttpUri
     */
    protected $baseUri;

    /**
     * Creates a new router.
     *
     * @param RouteCollectionInterface $routeCollection
     */
    public function __construct(RouteCollectionInterface $routeCollection)
    {
        $this->routeCollection = $routeCollection;
    }

    /**
     * Gets the route collection.
     *
     * @return RouteCollectionInterface
     */
    public function getRouteCollection()
    {
        return $this->routeCollection;
    }

    /**
     * Gets the base URI.
     *
     * @return HttpUri
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * Sets the base URI.
     *
     * @param HttpUri $uri
     */
    public function setBaseUri(HttpUri $uri)
    {
        $this->baseUri = $uri->normalize();
    }

    public function match(RequestInterface $request)
    {
        if (!$request instanceof HttpRequest) {
            return null;
        }

        if ($this->baseUri === null) {
            $requestUri = $request->getUri();

            $this->baseUri = new HttpUri();
            $this->baseUri->setScheme($requestUri->getScheme());
            $this->baseUri->setHost($requestUri->getHost());
            $this->baseUri->setPort($requestUri->getPort());

            if (method_exists($request, 'getBaseUrl')) {
                $this->baseUri->setPath(rtrim($request->getBaseUrl(), '/'));
            }

            $this->baseUri->normalize();
        }

        $basePathLength = strlen($this->baseUri->getPath());

        /** @var RouteInterface $route */
        foreach ($this->routeCollection as $name => $route) {
            if (null !== ($routeMatch = $route->match($request, $basePathLength))) {
                $routeMatch->prependRouteName($name);
                return $routeMatch;
            }
        }

        return null;
    }

    /**
     * @throws Exception\RuntimeException
     */
    public function assemble(array $params, array $options)
    {
        if (!isset($options['name'])) {
            throw new Exception\RuntimeException('No route name was supplied');
        }

        $nameParts  = explode('/', $options['name'], 2);
        $parentName = $nameParts[0];
        $childName  = isset($nameParts[1]) ? $nameParts[1] : null;

        $uri = $this->routeCollection->get($parentName)->assemble(clone $this->baseUri, $params, $childName);

        if (isset($options['query'])) {
            $uri->setQuery($options['query']);
        }

        if (isset($options['fragment'])) {
            $uri->setFragment($options['fragment']);
        }

        if (!isset($options['force_canonical']) || !$options['force_canonical']) {
            $uri->makeRelative($this->baseUri);

            if ($uri->getPath() === '') {
                // @todo This is just a workaround for now, as Zend\Uri\Http
                //       does not allow empty paths as valid relative URI, needs
                //       to be fixed.
                // @see  https://github.com/zendframework/zf2/issues/5563
                $uri->setPath($this->baseUri->getPath());
            }
        }

        return $uri->normalize()->toString();
    }
}