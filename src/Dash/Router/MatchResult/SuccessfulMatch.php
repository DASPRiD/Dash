<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\MatchResult;

/**
 * Generic successful match result.
 */
class SuccessfulMatch implements MatchResultInterface
{
    const TYPE = 'successful-match';

    /**
     * @var array
     */
    protected $params;

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

    public function getType()
    {
        return self::TYPE;
    }
}
