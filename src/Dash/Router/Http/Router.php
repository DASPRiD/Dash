<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\Http;

use Dash\Router\Http\RouteCollection\RouteCollectionInterface;
use Dash\Router\RouterInterface;
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
    protected $basePath;

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
     * Sets the base path.
     *
     * @param string $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Gets the base path.
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Sets the request URI.
     *
     * @param HttpUri $uri
     */
    public function setRequestUri(HttpUri $uri)
    {
        $this->requestUri = $uri;
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

        if ($this->basePath === null && method_exists($request, 'getBaseUrl')) {
            $this->basePath = $request->getBaseUrl();
        }

        $baseUrlLength = strlen($this->basePath);

        if ($this->requestUri === null) {
            $this->requestUri = $request->getUri();
        }

        foreach ($this->routeCollection as $name => $route) {
            if (null !== ($routeMatch = $route->match($request, $baseUrlLength))) {
                $routeMatch->prependRouteName($name);
                return $routeMatch;
            }
        }

        return null;
    }

    public function assemble()
    {

    }
}