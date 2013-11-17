<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Mvc\Router;

use Zend\Stdlib\RequestInterface;

/**
 * Interface every router must implement.
 */
interface RouterInterface
{
    /**
     * Match a given request.
     *
     * In case the request is not compatible with the router or the request can
     * not be matched otherwise, null should be returned.
     *
     * @param  RequestInterface $request
     * @return null|RouteMatchInterface
     */
    public function match(RequestInterface $request);

    public function assemble();
}
