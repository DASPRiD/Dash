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
 * HTTP specific match result if a method is not allowed by a route.
 */
class MethodNotAllowed extends AbstractFailedMatch
{
    /**
     * @var string[]
     */
    protected $allowedMethods;

    /**
     * @param string[] $allowedMethods
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
     * Merges two MethodNotAllowed match reslts together.
     *
     * @param self
     */
    public static function merge(self $firstMatch, self $secondMatch)
    {
        return new static(array_unique(
            array_merge($firstMatch->getAllowedMethods(), $secondMatch->getAllowedMethods())
        ));
    }
}
