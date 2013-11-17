<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Mvc\Router\Http\Route;

use Dash\Mvc\Router\Http\RouteMatch;
use Zend\Http\Request as HttpRequest;

/**
 * Interface every HTTP route must implement.
 */
interface RouteInterface
{
    /**
     * Matches a request at a given path offset.
     *
     * @param  HttpRequest $request
     * @param  int         $pathOffset
     * @return null|RouteMatch
     */
    public function match(HttpRequest $request, $pathOffset);

    public function assemble();
}