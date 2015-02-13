<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\MatchResult;

/**
 * HTTP specific match result if a method is not allowed by a route.
 */
class MethodNotAllowed extends AbstractFailedMatch
{
    /**
     * @var string[]
     */
    protected $allowedMethods;

    /**
     * @param array $allowedMethods
     */
    public function __construct(array $allowedMethods)
    {
        $this->allowedMethods = $allowedMethods;
    }

    /**
     * @return string[]
     */
    public function getAllowedMethods()
    {
        return $this->allowedMethods;
    }

    /**
     * Merge this match result with another.
     *
     * @param self $disallowedMethod
     */
    public function merge(self $disallowedMethod)
    {
        $this->allowedMethods = array_unique(array_merge($this->allowedMethods, $disallowedMethod->getAllowedMethods()));
    }
}
