<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\MatchResult;

use Dash\Parser\ParseResult;
use Psr\Http\Message\ResponseInterface;

/**
 * Generic successful match result.
 */
class SuccessfulMatch implements MatchResultInterface
{
    /**
     * @var array
     */
    protected $params;

    /**
     * @var null|string
     */
    protected $routeName;

    /**
     * Creates a new successful match result.
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    /**
     * Sets a single parameter.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    /**
     * Gets a specific parameter.
     *
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        if (isset($this->params[$name]) || array_key_exists($name, $this->params)) {
            return $this->params[$name];
        }

        return $default;
    }

    /**
     * Gets all parameters.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $routeName
     */
    public function prependRouteName($routeName)
    {
        if ($this->routeName === null) {
            $this->routeName = $routeName;
        } else {
            $this->routeName = $routeName . '/' . $this->routeName;
        }
    }

    /**
     * Gets the name of the matched route.
     *
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * Adds the parameters of a parse result to the match.
     *
     * @param ParseResult $parseResult
     */
    public function addParseResult(ParseResult $parseResult)
    {
        $this->params = $parseResult->getParams() + $this->params;
    }

    /**
     * Merges another match with this one.
     *
     * @param self $match
     */
    public function merge(self $match)
    {
        $this->params = $match->getParams() + $this->params;

        if ($match->getRouteName() !== null) {
            $this->prependRouteName($match->getRouteName());
        }
    }

    final public function isSuccess()
    {
        return true;
    }

    public function modifyResponse(ResponseInterface $response)
    {
        return $response;
    }
}
