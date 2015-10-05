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
 * Unspecific match result when the router could not match the request.
 */
class UnsuccessfulMatch extends AbstractFailedMatch
{
    public function modifyResponse(ResponseInterface $response)
    {
        return $response->withStatus(404);
    }
}
