<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router;

use Zend\Stdlib\ResponseInterface;

/**
 * Result objects returned by match methods of routers.
 *
 * If the MatchResult contains a response, it must be returned to the client.
 * Otherwise the RouteMatch can be used for dispatching.
 */
class MatchResult
{
    /**
     * @var Zend\Stdlib\ResponseInterface|null
     */
    protected $response;

    /**
     * @var RouteMatchInterface|null
     */
    protected $routeMatch;

    /**
     * @param ResponseInterface|RouteMatchInterface
     */
    public function __construct($responseOrRouteMatch)
    {
        if ($responseOrRouteMatch instanceof ResponseInterface) {
            $this->response = $responseOrRouteMatch;
        } elseif ($responseOrRouteMatch instanceof RouteMatchInterface) {
            $this->routeMatch = $responseOrRouteMatch;
        } else {
            throw new Exception\InvalidArgumentException(sprintf(
                'Payload must either implement ResponseInterface or RouteMatchInterface, %s given',
                is_object($responseOrRouteMatch) ? get_class($responseOrRouteMatch) : gettype($responseOrRouteMatch)
            ));
        }
    }

    /**
     * @return bool
     */
    public function hasResponse()
    {
        return $this->response !== null;
    }

    /**
     * @return bool
     */
    public function hasRouteMatch()
    {
        return $this->routeMatch !== null;
    }

    /**
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return RouteMatchInterface|null
     */
    public function getRouteMatch()
    {
        return $this->routeMatch;
    }
}
