<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Mvc\Router\Http;

use Dash\Mvc\Router\Http\RouteCollection\RouteCollectionInterface;
use Dash\Mvc\Router\RouterInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Http\Request as HttpRequest;
use Zend\Uri\Http as HttpUri;

class Router implements RouterInterface
{
    /**
     * @var RouteCollectionInterface
     */
    protected $routeCollection;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var HttpUri
     */
    protected $requestUri;

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
     * If none was set yet, a generic was is created.
     *
     * @return RouteCollectionInterface
     */
    public function getRouteCollection()
    {
        return $this->routeCollection;
    }

    /**
     * Sets the base URL.
     *
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Gets the base URL.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Sets the request URI.
     *
     * @param HttpUri $uri
     */
    public function setRequestUri(HttpUri $uri)
    {
        $this->requestUri = $uri;
        return $this;
    }

    /**
     * Gets the request URI.
     *
     * @return HttpUri
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    public function match(RequestInterface $request)
    {
        if (!$request instanceof HttpRequest) {
            return null;
        }

        if ($this->getBaseUrl() === null && method_exists($request, 'getBaseUrl')) {
            $this->setBaseUrl($request->getBaseUrl());
        }

        $baseUrlLength = strlen($this->getBaseUrl());

        if ($this->getRequestUri() === null) {
            $this->setRequestUri($request->getUri());
        }

        foreach ($this->routeCollection as $name => $route) {
            if (null !== ($routeMatch = $route->match($request, $baseUrlLength))) {
                $routeMatch->setRouteName($name);
                return $routeMatch;
            }
        }

        return null;
    }

    public function assemble()
    {

    }
}