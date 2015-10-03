<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash;

use Dash\Exception;
use Dash\MatchResult\SuccessfulMatch;
use Dash\MatchResult\UnsuccessfulMatch;
use Dash\Route\RouteInterface;
use Dash\RouteCollection\RouteCollectionInterface;
use Dash\RouterInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Router implements RouterInterface
{
    /**
     * @var RouteCollectionInterface
     */
    protected $routeCollection;

    /**
     * @var array
     */
    protected $baseUri;

    /**
     * Creates a new router.
     *
     * @param RouteCollectionInterface $routeCollection
     * @param UriInterface             $baseUri
     */
    public function __construct(RouteCollectionInterface $routeCollection, UriInterface $baseUri)
    {
        $this->routeCollection = $routeCollection;
        $this->baseUri = [
            'scheme' => $baseUri->getScheme(),
            'host'   => $baseUri->getHost(),
            'path'   => rtrim($baseUri->getPath(), '/'),
        ];
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

    public function match(RequestInterface $request)
    {
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
