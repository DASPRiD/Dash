<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\MatchResult;

use Psr\Http\Message\ResponseInterface;

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

    public function modifyResponse(ResponseInterface $response)
    {
        return $response
            ->withStatus(405)
            ->withHeader('Allow', implode(', ', $this->allowedMethods));
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
     * @param MethodNotAllowed $disallowedMethod
     */
    public function merge(MethodNotAllowed $disallowedMethod)
    {
        $this->allowedMethods = array_unique(
            array_merge($this->allowedMethods, $disallowedMethod->getAllowedMethods())
        );
    }
}
