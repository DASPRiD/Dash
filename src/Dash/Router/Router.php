<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router;

use Dash\Router\Exception;
use Dash\Router\MatchResult\SuccessfulMatch;
use Dash\Router\MatchResult\UnsuccessfulMatch;
use Dash\Router\Route\RouteInterface;
use Dash\Router\RouteCollection\RouteCollectionInterface;
use Dash\Router\RouterInterface;
use Psr\Http\Message\RequestInterface;

class Router implements RouterInterface
{
    /**
     * @var RouteCollectionInterface
     */
    protected $routeCollection;

    /**
     * @var null|array
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
     * @return array
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * Sets the base URI.
     *
     * @param string $scheme
     * @param string $host
     * @param string $path
     */
    public function setBaseUri($scheme, $host, $path)
    {
        $this->baseUri = [
            'scheme' => $scheme,
            'host'   => $host,
            'path'   => rtrim($path, '/'),
        ];
    }

    public function match(RequestInterface $request)
    {
        if ($this->baseUri === null) {
            $requestUri = $request->getUri();
            $this->setBaseUri(
                $requestUri->getScheme(),
                $requestUri->getHost(),
                method_exists($request, 'getBaseUrl') ? $request->getBaseUrl() : ''
            );
        }

        $basePathLength = strlen($this->baseUri['path']);

        /** @var RouteInterface $route */
        foreach ($this->routeCollection as $name => $route) {
            if (null === ($matchResult = $route->match($request, $basePathLength))) {
                continue;
            }

            if ($matchResult->isSuccess()) {
                if (!$matchResult instanceof SuccessfulMatch) {
                    throw new Exception\UnexpectedValueException(sprintf(
                        'Expected instance of %s, received %s',
                        SuccessfulMatch::class,
                        is_object($matchResult) ? get_class($matchResult) : gettype($matchResult)
                    ));
                }

                $matchResult->prependRouteName($name);
            }

            return $matchResult;
        }

        return new UnsuccessfulMatch();
    }

    /**
     * @throws Exception\RuntimeException
     */
    public function assemble(array $params, array $options)
    {
        if ($this->baseUri === null) {
            throw new Exception\RuntimeException('Base URI has not been set');
        }

        if (!isset($options['name'])) {
            throw new Exception\RuntimeException('No route name was supplied');
        }

        $nameParts  = explode('/', $options['name'], 2);
        $parentName = $nameParts[0];
        $childName  = isset($nameParts[1]) ? $nameParts[1] : null;

        $assemblyResult = $this->routeCollection->get($parentName)->assemble($params, $childName);
        $assemblyResult->path = $this->baseUri['path'] . $assemblyResult->path;

        if (isset($options['query'])) {
            $assemblyResult->query = $options['query'];
        }

        if (isset($options['fragment'])) {
            $assemblyResult->fragment = $options['fragment'];
        }

        return $assemblyResult->generateUri(
            $this->baseUri['scheme'],
            $this->baseUri['host'],
            (isset($options['force_canonical']) && $options['force_canonical'])
        );
    }
}