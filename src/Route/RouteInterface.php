<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Route;

use Dash\MatchResult;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface every HTTP route must implement.
 */
interface RouteInterface
{
    /**
     * Matches a request at a given path offset.
     *
     * @param  ServerRequestInterface $request
     * @param  int                    $pathOffset
     * @return MatchResult|null
     */
    public function match(ServerRequestInterface $request, $pathOffset);

    /**
     * Assembles a URL.
     *
     * @param  array       $params
     * @param  string|null $childName
     * @return AssemblyResult
     */
    public function assemble(array $params, $childName = null);
}
