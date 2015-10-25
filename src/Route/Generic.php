<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Route;

use Dash\Exception;
use Dash\MatchResult\MethodNotAllowed;
use Dash\MatchResult\SchemeNotAllowed;
use Dash\MatchResult\SuccessfulMatch;
use Dash\Parser\ParserInterface;
use Dash\RouteCollection\RouteCollectionInterface;
use Dash\RouteCollection\RouteCollectionUtils;
use Psr\Http\Message\ServerRequestInterface;

/**
 * A generic route which takes care of all HTTP aspects.
 */
class Generic implements RouteInterface
{
    /**
     * Allowed methods on this route.
     *
     * @var array|null
     */
    protected $methods;

    /**
     * Whether to force the route to be HTTPS or HTTP.
     *
     * @var bool|null
     */
    protected $secure;

    /**
     * @var int|null
     */
    protected $port;

    /**
     * @var ParserInterface|null
     */
    protected $pathParser;

    /**
     * @var ParserInterface|null
     */
    protected $hostnameParser;

    /**
     * @var array
     */
    protected $defaults = [];

    /**
     * @var RouteCollectionInterface|RouteInterface[]|null
     */
    protected $children;

    /**
     * @param ParserInterface|null          $pathParser
     * @param ParserInterface|null          $hostnameParser
     * @param array|null                    $methods
     * @param bool|null                     $secure
     * @param int|null                      $port
     * @param array                         $defaults
     * @param RouteCollectionInterface|null $children
     */
    public function __construct(
        ParserInterface $pathParser = null,
        ParserInterface $hostnameParser = null,
        array $methods = null,
        $secure = null,
        $port = null,
        array $defaults = [],
        RouteCollectionInterface $children = null
    ) {
        $this->pathParser     = $pathParser;
        $this->hostnameParser = $hostnameParser;
        $this->defaults       = $defaults;
        $this->children       = $children;

        if (null !== $secure) {
            $this->secure = (bool) $secure;
        }

        if (null !== $port) {
            $this->port = (int) $port;
        }

        if (null !== $methods) {
            $this->methods = array_flip(array_map('strtoupper', array_values($methods)));

            if (isset($this->methods['GET']) ^ isset($this->methods['HEAD'])) {
                // Implicitly enable HEAD on GET, and vise versa.
                $this->methods['GET']  = true;
                $this->methods['HEAD'] = true;
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception\UnexpectedValueException
     */
    public function match(ServerRequestInterface $request, $pathOffset)
    {
        $uri = $request->getUri();

        // Verify scheme first, if set.
        if (true === $this->secure && 'https' !== $uri->getScheme()) {
            return new SchemeNotAllowed($uri->withScheme('https'));
        } elseif (false === $this->secure && 'http' !== $uri->getScheme()) {
            return new SchemeNotAllowed($uri->withScheme('http'));
        }

        // Then match hostname, if parser is set.
        if (null !== $this->hostnameParser) {
            $hostnameResult = $this->hostnameParser->parse($uri->getHost(), 0);

            if (null === $hostnameResult || strlen($uri->getHost()) !== $hostnameResult->getMatchLength()) {
                return null;
            }
        }

        // Then match port, if set
        if (null !== $this->port) {
            $port = $uri->getPort() ?: ('http' === $uri->getScheme() ? 80 : 443);

            if ($port !== $this->port) {
                return null;
            }
        }

        // Next match the path.
        $completePathMatched = false;

        if (null !== $this->pathParser) {
            if (null === ($pathResult = $this->pathParser->parse($uri->getPath(), $pathOffset))) {
                return null;
            }

            $pathOffset += $pathResult->getMatchLength();
            $completePathMatched = ($pathOffset === strlen($uri->getPath()));
        }

        // Looks good so far, let's create a match.
        $params = $this->defaults;

        if (isset($hostnameResult)) {
            $params = $hostnameResult->getParams() + $params;
        }

        if (isset($pathResult)) {
            $params = $pathResult->getParams() + $params;
        }

        if ($completePathMatched) {
            if (null === $this->methods || isset($this->methods[$request->getMethod()])) {
                return new SuccessfulMatch($params);
            }

            if (empty($this->methods)) {
                // Special case: when no methods are defined at all, this route may simply not terminate.
                return null;
            }

            return new MethodNotAllowed(array_keys($this->methods));
        }

        // The path was not completely matched yet, so we check the children.
        if (null === $this->children) {
            return null;
        }

        return RouteCollectionUtils::matchRouteCollection($this->children, $request, $pathOffset, $params);
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception\RuntimeException
     */
    public function assemble(array $params, $childName = null)
    {
        if ($childName !== null) {
            $nameParts  = explode('/', $childName, 2);
            $parentName = $nameParts[0];
            $childName  = isset($nameParts[1]) ? $nameParts[1] : null;

            if ($this->children === null) {
                throw new Exception\RuntimeException('Route has no children to assemble');
            }

            $assemblyResult = $this->children->get($parentName)->assemble($params, $childName);
        } else {
            $assemblyResult = new AssemblyResult();
        }

        if (null !== $this->secure) {
            $assemblyResult->scheme = $this->secure ? 'https' : 'http';
        }

        if (null !== $this->port) {
            $assemblyResult->port = $this->port;
        }

        if ($this->hostnameParser !== null) {
            $assemblyResult->host = $this->hostnameParser->compile($params, $this->defaults);
        }

        if ($this->pathParser !== null) {
            $assemblyResult->path = $this->pathParser->compile($params, $this->defaults) . $assemblyResult->path;
        }

        return $assemblyResult;
    }
}
