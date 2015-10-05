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
use Psr\Http\Message\UriInterface;

/**
 * Match result if a scheme is not allowed by a route.
 */
class SchemeNotAllowed extends AbstractFailedMatch
{
    /**
     * @var UriInterface
     */
    protected $allowedUri;

    /**
     * @param UriInterface $allowedUri
     */
    public function __construct(UriInterface $allowedUri)
    {
        $this->allowedUri = $allowedUri;
    }

    public function modifyResponse(ResponseInterface $response)
    {
        return $response
            ->withStatus(301)
            ->withHeader('Location', (string) $this->allowedUri);
    }

    /**
     * @return UriInterface
     */
    public function getAllowedUri()
    {
        return $this->allowedUri;
    }
}
