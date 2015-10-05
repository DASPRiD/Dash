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
 * Interface describing match results returned by routers.
 */
interface MatchResultInterface
{
    /**
     * Returns whether the match result is a success.
     *
     * @return bool
     */
    public function isSuccess();

    /**
     * Modifies the response to properly indicate the kind of failure.
     *
     * @param  ResponseInterface $response
     * @return ResponseInterface
     */
    public function modifyResponse(ResponseInterface $response);
}
