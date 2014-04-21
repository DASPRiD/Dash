<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\Http\Route;

use Dash\Router\Exception;
use Dash\Router\Http\MatchResult\MethodNotAllowed;
use Dash\Router\Http\MatchResult\SchemeNotAllowed;
use Dash\Router\Http\MatchResult\SuccessfulMatch;
use Dash\Router\Http\Parser\ParserInterface;
use Dash\Router\Http\RouteCollection\RouteCollectionInterface;
use Zend\Http\Request as HttpRequest;

/**
 * A generic route which takes care of all HTTP aspects.
 */
class Generic implements RouteInterface
{
    /**
     * List of valid methods.
     *
     * @var array
     */
    protected static $validMethods = ['OPTIONS', 'GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'TRACE', 'CONNECT', 'PATCH'];

    /**
     * Allowed methods on this route.
     *
     * @var array|string
     */
    protected $methods = '*';

    /**
     * Whether to force the route to be HTTPS.
     *
     * @var bool
     */
    protected $secure = false;

    /**
     * @var null|ParserInterface
     */
    protected $pathParser;

    /**
     * @var null|ParserInterface
     */
    protected $hostnameParser;

    /**
     * @var array
     */
    protected $defaults = [];

    /**
     * @var RouteCollectionInterface
     */
    protected $children;

    /**
     * @param string|array $methods
     */
    public function setMethods($methods)
    {
        $this->methods = [];

        if (is_string($methods)) {
            if ('' === $methods || '*' === $methods) {
                $this->methods = $methods;
                return;
            } else {
                $methods = [$methods];
            }
        } elseif (!is_array($methods)) {
            throw new Exception\InvalidArgumentException('$methods must either be a string or an array');
        }

        foreach ($methods as $method) {
            $method = strtoupper($method);

            if (!in_array($method, self::$validMethods)) {
                throw new Exception\InvalidArgumentException(sprintf('%s is not a valid HTTP method', $method));
            }

            $this->methods[$method] = true;
        }

        if (isset($this->methods['GET']) xor isset($this->methods['HEAD'])) {
            // Implicitly enable HEAD on GET, and vise versa.
            $this->methods['GET']  = true;
            $this->methods['HEAD'] = true;
        }
    }

    /**
     * @param bool $secure
     */
    public function setSecure($secure)
    {
        $this->secure = (bool) $secure;
    }

    /**
     * @param ParserInterface $pathParser
     */
    public function setPathParser(ParserInterface $pathParser = null)
    {
        $this->pathParser = $pathParser;
    }

    /**
     * @param ParserInterface $hostnameParser
     */
    public function setHostnameParser(ParserInterface $hostnameParser = null)
    {
        $this->hostnameParser = $hostnameParser;
    }

    /**
     * @param array $defaults
     */
    public function setDefaults(array $defaults)
    {
        $this->defaults = $defaults;
    }

    /**
     * @param RouteCollectionInterface $children
     */
    public function setChildren(RouteCollectionInterface $children = null)
    {
        $this->children = $children;
    }

    public function match(HttpRequest $request, $pathOffset)
    {
        $uri = $request->getUri();

        // Verify scheme first, if set.
        if ($this->secure && 'https' !== $uri->getScheme()) {
            $allowedUri = clone $uri;
            $allowedUri->setScheme('https');
            return new SchemeNotAllowed($allowedUri->toString());
        }

        // Then match hostname, if parser is set.
        if (null !== $this->hostnameParser) {
            $hostnameResult = $this->hostnameParser->parse($uri->getHost(), 0);

            if (null === $hostnameResult || strlen($uri->getHost()) !== $hostnameResult->getMatchLength()) {
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
        $match = new SuccessfulMatch($this->defaults);

        if (isset($hostnameResult)) {
            $match->addParseResult($hostnameResult);
        }

        if (isset($pathResult)) {
            $match->addParseResult($pathResult);
        }

        if ($completePathMatched) {
            if ('' === $this->methods) {
                return null;
            }

            if ('*' === $this->methods || isset($this->methods[$request->getMethod()])) {
                return $match;
            }

            return new MethodNotAllowed(array_keys($this->methods));
        }

        // The path was not completely matched yet, so we check the children.
        if (null === $this->children) {
            return null;
        }

        $methodNotAllowedResult = null;
        $schemeNotAllowedResult = null;
        $childMatch             = null;

        foreach ($this->children as $childName => $childRoute) {
            if (null === ($childMatch = $childRoute->match($request, $pathOffset))) {
                continue;
            }

            if ($childMatch->isSuccess()) {
                if (!$childMatch instanceof SuccessfulMatch) {
                    throw new Exception\UnexpectedValueException(sprintf(
                        'Expected instance of Dash\Router\Http\MatchResult\SuccessfulMatch, received %s',
                        is_object($childMatch) ? get_class($childMatch) : gettype($childMatch)
                    ));
                }

                $childMatch->prependRouteName($childName);
                $match->merge($childMatch);
                return $match;
            }

            if ($childMatch instanceof MethodNotAllowed) {
                if ($methodNotAllowedResult === null) {
                    $methodNotAllowedResult = $childMatch;
                } else {
                    $methodNotAllowedResult->merge($childMatch);
                }
                continue;
            }

            if ($childMatch instanceof SchemeNotAllowed) {
                $schemeNotAllowedResult = $schemeNotAllowedResult ?: $childMatch;
                continue;
            }

            return $childMatch;
        }

        if (null !== $schemeNotAllowedResult) {
            return $schemeNotAllowedResult;
        }

        if (null !== $methodNotAllowedResult) {
            return $methodNotAllowedResult;
        }

        return null;
    }

    /**
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

        if ($this->secure) {
            $assemblyResult->scheme = 'https';
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
