<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Mvc\Router\Http\Route;

use Dash\Mvc\Router\Exception;
use Dash\Mvc\Router\Http\Parser\ParserInterface;
use Dash\Mvc\Router\Http\RouteMatch;
use Dash\Mvc\Router\Http\RouteCollection\RouteCollectionInterface;
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
    protected static $validMethods = ['OPTIONS', 'GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'TRACE', 'CONNECT'];

    /**
     * Allowed methods on this route.
     *
     * @var array
     */
    protected $methods = ['GET'];

    /**
     * Whether to force the route to be HTTPS.
     *
     * @var bool
     */
    protected $secure = false;

    /**
     * @var ParserInterface
     */
    protected $pathParser;

    /**
     * @var ParserInterface
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
            if ($methods === '' || $methods === '*') {
                $this->methods = $methods;
                return;
            }

            $methods = (array) $methods;
        } elseif (!is_array($methods)) {
            throw new Exception\InvalidArgumentException('$methods must either be a string or an array');
        }

        foreach ($methods as $method) {
            $method = strtoupper($method);

            if (!in_array($method, self::$validMethods)) {
                throw new Exception\InvalidArgumentException(sprintf('%s is not a valid HTTP method', $method));
            }

            $this->methods[] = $method;
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
        if ($this->secure && $uri->getScheme() !== 'https') {
            return null;
        }

        // Then match hostname, if parser is set.
        if ($this->hostnameParser !== null) {
            $hostnameResult = $this->hostnameParser->parse($uri->getHost(), 0);

            if ($hostnameResult === null || strlen($uri->getHost()) !== $hostnameResult->getMatchLength()) {
                return null;
            }
        }

        // Next match the path.
        $completePathMatched = false;

        if ($this->pathParser !== null) {
            if (null === ($pathResult = $this->pathParser->parse($uri->getPath(), $pathOffset))) {
                return null;
            }

            $pathOffset += $pathResult->getMatchLength();
            $completePathMatched = ($pathOffset === strlen($uri->getPath()));
        }

        // Looks good so far, let's create a route match.
        $match = new RouteMatch($this->defaults);

        if (isset($hostnameResult)) {
            $match->addParseResult($hostnameResult);
        }

        if (isset($pathResult)) {
            $match->addParseResult($pathResult);
        }

        if ($completePathMatched) {
            if ($this->methods === '*' || in_array($request->getMethod(), $this->methods)) {
                return $match;
            }

            return null;
        }

        // The path was not completely matched yet, so we check the children.
        if ($this->children === null) {
            return null;
        }

        $childMatch = null;

        foreach ($this->children as $childName => $childRoute) {
            if (null !== ($childMatch = $childRoute->match($request, $pathOffset))) {
                $childMatch->setRouteName($childName);
                break;
            }
        }

        if ($childMatch === null) {
            return null;
        }

        $match->merge($childMatch);
        return $match;
    }

    public function assemble()
    {

    }
}