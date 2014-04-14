<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\Http\MatchResult;

use Dash\Router\Http\Parser\ParseResult;
use Dash\Router\MatchResult\SuccessfulMatch as BaseSuccessfulMatch;

/**
 * HTTP specific successful match result.
 */
class SuccessfulMatch extends BaseSuccessfulMatch
{
    const TYPE = 'successful-match';

    /**
     * @var null|string
     */
    protected $routeName;

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
        $this->prependRouteName($match->getRouteName());
    }
}
