<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\MatchResult;

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
     * @var string|null
     */
    protected $routeName;

    /**
     * Creates a new successful match result.
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * @param self   $childMatch
     * @param array  $params
     * @param string $childName
     */
    public static function fromChildMatch(self $childMatch, array $params, $childName)
    {
        $match = new static($childMatch->getParams());
        $match->params += $params;
        $match->routeName = $childName;

        if (null !== $childMatch->getRouteName()) {
            $match->routeName .= '/' . $childMatch->getRouteName();
        }

        return $match;
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
     * Gets the name of the matched route.
     *
     * This method may return null during construction in the router, but must always return a value after being
     * returned by a router's match() method.
     *
     * @return string|null
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * {@inheritdoc}
     */
    final public function isSuccess()
    {
        return true;
    }
}
