<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\RouteCollection;

use Dash\Exception\UnexpectedValueException;
use Dash\MatchResult\MatchResultInterface;
use Dash\MatchResult\MethodNotAllowed;
use Dash\MatchResult\SchemeNotAllowed;
use Dash\MatchResult\SuccessfulMatch;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Utility methods for working with route collections.
 */
class RouteCollectionUtils
{
    /**
     * This is a purely static class, so disallow instantiating.
     */
    private function __construct()
    {
    }

    /**
     * Matches a request against a route collection.
     *
     * @param  RouteCollectionInterface $routeCollection
     * @param  ServerRequestInterface   $request
     * @param  int                      $pathOffset
     * @param  array                    $previousParams
     * @return MatchResultInterface|null
     * @throws UnexpectedValueException
     */
    public static function matchRouteCollection(
        RouteCollectionInterface $routeCollection,
        ServerRequestInterface $request,
        $pathOffset,
        array $previousParams
    ) {
        $methodNotAllowedResult = null;
        $schemeNotAllowedResult = null;

        foreach ($routeCollection as $name => $route) {
            if (null === ($matchResult = $route->match($request, $pathOffset))) {
                continue;
            }

            if ($matchResult->isSuccess()) {
                if (!$matchResult instanceof SuccessfulMatch) {
                    throw new UnexpectedValueException(sprintf(
                        'Expected instance of %s, received %s',
                        SuccessfulMatch::class,
                        is_object($matchResult) ? get_class($matchResult) : gettype($matchResult)
                    ));
                }

                return SuccessfulMatch::fromChildMatch($matchResult, $previousParams, $name);
            }

            if ($matchResult instanceof MethodNotAllowed) {
                if ($methodNotAllowedResult === null) {
                    $methodNotAllowedResult = $matchResult;
                } else {
                    $methodNotAllowedResult = $methodNotAllowedResult::merge($methodNotAllowedResult, $matchResult);
                }
                continue;
            }

            if ($matchResult instanceof SchemeNotAllowed) {
                $schemeNotAllowedResult = $schemeNotAllowedResult ?: $matchResult;
                continue;
            }

            return $matchResult;
        }

        if (null !== $schemeNotAllowedResult) {
            return $schemeNotAllowedResult;
        }

        if (null !== $methodNotAllowedResult) {
            return $methodNotAllowedResult;
        }

        return null;
    }
}
