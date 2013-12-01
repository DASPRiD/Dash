<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\Http\Route;

use Dash\Router\Http\RouteMatch;
use Zend\Http\Request as HttpRequest;
use Zend\Uri\Http as HttpUri;

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

    /**
     * Assembles a URL.
     *
     * Even thhough by API design the caller works with the returned HttpUri
     * object, it is not ensured that the passed HttpUri object is not modified,
     * as the implementation is allowed to just modify the object and return it
     * again.
     *
     * If you rely on your original object to not be modifed, you should call
     * this method the following way:
     *
     * <code>
     * $route->assemble(clone $uri, $params, $childName);
     * </code>
     *
     * @param  HttpUri     $uri
     * @param  array       $params
     * @param  null|string $childName
     * @return HttpUri
     */
    public function assemble(HttpUri $uri, array $params, $childName = null);
}
