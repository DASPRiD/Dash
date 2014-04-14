<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\Http\MatchResult;

use Dash\Router\MatchResult\MatchResultInterface;

/**
 * HTTP specific match result if a scheme is not allowed by a route.
 */
class DisallowedScheme implements MatchResultInterface
{
    const TYPE = 'disallowed-scheme';

    /**
     * @var string
     */
    protected $allowedUri;

    /**
     * @param string $allowedUri
     */
    public function __construct($allowedUri)
    {
        $this->allowedUri = $allowedUri;
    }

    /**
     * @return string
     */
    public function getAllowedUri()
    {
        return $this->allowedUri;
    }

    public function getType()
    {
        return self::TYPE;
    }
}
