<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\Http\Parser;

/**
 * A generic parse reuslt which is returned by parsers.
 */
class ParseResult
{
    /**
     * @var array
     */
    protected $params;

    /**
     * @var int
     */
    protected $matchLength;

    /**
     * @param array $params
     * @param int   $matchLength
     */
    public function __construct(array $params, $matchLength)
    {
        $this->params      = $params;
        $this->matchLength = (int) $matchLength;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return int
     */
    public function getMatchLength()
    {
        return $this->matchLength;
    }
}
