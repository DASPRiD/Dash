<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface every router must implement.
 */
interface RouterInterface
{
    /**
     * Matches a given request.
     *
     * @param  ServerRequestInterface $request
     * @return MatchResult
     */
    public function match(ServerRequestInterface $request);

    /**
     * Assembles a URL.
     *
     * @param  string $routeName
     * @param  array  $params
     * @param  array  $options
     * @return string
     */
    public function assemble($routeName, array $params = [], array $options = []);
}
