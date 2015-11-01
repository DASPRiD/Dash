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
use Dash\MatchResult;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Utility methods for working with route collections.
 */
final class RouteCollectionMatcher
{
    /**
     * This is a purely static class, so disallow instantiating.
     *
     * @codeCoverageIgnore
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
     * @param  array                    $parentParams
     * @return MatchResult|null
     * @throws UnexpectedValueException
     */
    public static function matchRouteCollection(
        RouteCollectionInterface $routeCollection,
        ServerRequestInterface $request,
        $pathOffset,
        array $parentParams
    ) {
        $methodFailureResult = null;
        $schemeFailureResult = null;

        foreach ($routeCollection as $name => $route) {
            if (null === ($matchResult = $route->match($request, $pathOffset))) {
                continue;
            }

            if ($matchResult->isSuccess()) {
                return MatchResult::fromChildMatch($matchResult, $parentParams, $name);
            }

            if ($matchResult->isMethodFailure()) {
                if (null === $methodFailureResult) {
                    $methodFailureResult = $matchResult;
                } else {
                    $methodFailureResult = MatchResult::mergeMethodFailures($methodFailureResult, $matchResult);
                }
                continue;
            }

            if ($matchResult->isSchemeFailure()) {
                $schemeFailureResult = $schemeFailureResult ?: $matchResult;
                continue;
            }

            return $matchResult;
        }

        if (null !== $schemeFailureResult) {
            return $schemeFailureResult;
        }

        if (null !== $methodFailureResult) {
            return $methodFailureResult;
        }

        return null;
    }
}
