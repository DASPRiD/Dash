<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router;

use Zend\Stdlib\RequestInterface;

/**
 * Interface every router must implement.
 */
interface RouterInterface
{
    /**
     * Matches a given request.
     *
     * In case the request is not compatible with the router or the request can
     * not be matched otherwise, null should be returned.
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
