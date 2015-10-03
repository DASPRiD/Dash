<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash;

use Psr\Http\Message\RequestInterface;

/**
 * Interface every router must implement.
 */
interface RouterInterface
{
    /**
     * Matches a given request.
     *
     * @param  RequestInterface $request
     * @return MatchResult\MatchResultInterface
     */
    public function match(RequestInterface $request);

    /**
     * Assembles a response.
     *
     * @param  array $params
     * @param  array $options
     * @return mixed
     */
    public function assemble(array $params, array $options);
}
