<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash;

use Dash\Exception\InvalidArgumentException;
use Dash\Exception\RuntimeException;
use Dash\Exception\UnexpectedValueException;
use Dash\RouteCollection\RouteCollectionInterface;
use Dash\RouteCollection\RouteCollectionMatcher;
use Dash\RouterInterface;
use Psr\Http\Message\ServerRequestInterface;
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
     * @param UriInterface|string      $baseUri
     */
    public function __construct(RouteCollectionInterface $routeCollection, $baseUri)
    {
        $this->routeCollection = $routeCollection;

        if ($baseUri instanceof UriInterface) {
            $this->setBaseUriFromObject($baseUri);
        } elseif (is_string($baseUri)) {
            $this->setBaseUriFromString($baseUri);
        } else {
            throw new InvalidArgumentException(sprintf(
                'Expected base URI of type string or %s, got %s',
                UriInterface::class,
                is_object($baseUri) ? get_class($baseUri) : gettype($baseUri)
            ));
        }

        if ('' === $this->baseUri['scheme'] || '' === $this->baseUri['host']) {
            throw new InvalidArgumentException(sprintf(
                'Base URI "%s" does not seem to be canonical',
                (string) $baseUri
            ));
        }

        if (null === $this->baseUri['port']) {
            $this->baseUri['port'] = ('http' === $this->baseUri['scheme'] ? 80 : 443);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnexpectedValueException
     */
    public function match(ServerRequestInterface $request)
    {
        $basePathLength = strlen($this->baseUri['path']);

        return RouteCollectionMatcher::matchRouteCollection(
            $this->routeCollection,
            $request,
            $basePathLength,
            []
        ) ?: MatchResult::fromMatchFailure();
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     */
    public function assemble($routeName, array $params = [], array $options = [])
    {
        $nameParts  = explode('/', $routeName, 2);
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
            $this->baseUri['port'],
            (isset($options['enforce_absolute_uri']) && $options['enforce_absolute_uri'])
        );
    }

    /**
     * @param UriInterface $baseUri
     */
    protected function setBaseUriFromObject(UriInterface $baseUri)
    {
        $this->baseUri = [
            'scheme' => $baseUri->getScheme(),
            'host'   => $baseUri->getHost(),
            'port'   => $baseUri->getPort(),
            'path'   => rtrim($baseUri->getPath(), '/'),
        ];
    }

    /**
     * @param  string $baseUri
     * @throws InvalidArgumentException
     */
    protected function setBaseUriFromString($baseUri)
    {
        if (false === ($parts = parse_url($baseUri))) {
            throw new InvalidArgumentException(sprintf(
                'Base URI "%s" does not appear to be a valid URI',
                $baseUri
            ));
        }

        $this->baseUri = [
            'scheme' => (isset($parts['scheme']) ? $parts['scheme'] : ''),
            'host'   => (isset($parts['host']) ? $parts['host'] : ''),
            'port'   => (isset($parts['port']) ? $parts['port'] : null),
            'path'   => (isset($parts['path']) ? rtrim($parts['path'], '/') : ''),
        ];
    }
}
