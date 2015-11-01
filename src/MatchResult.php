<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash;

use Dash\Exception\DomainException;
use Psr\Http\Message\UriInterface;

final class MatchResult
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $routeName;

    /**
     * @var array
     */
    private $params;

    /**
     * @var array
     */
    private $allowedMethods;

    /**
     * @var UriInterface
     */
    private $absoluteUri;

    /**
     * Match result may only be created through static methods.
     */
    private function __construct()
    {
    }

    /**
     * Creates a success match result.
     *
     * @param  array $params
     * @return self
     */
    public static function fromSuccess(array $params)
    {
        $matchResult = new self();
        $matchResult->type = 'success';
        $matchResult->params = $params;
        return $matchResult;
    }

    /**
     * Creates a method failure match reuslt.
     *
     * @param  array $allowedMethods
     * @return self
     */
    public static function fromMethodFailure(array $allowedMethods)
    {
        $matchResult = new self();
        $matchResult->type = 'methodFailure';
        $matchResult->allowedMethods = $allowedMethods;
        return $matchResult;
    }

    /**
     * Creates a scheme failure match result.
     *
     * @param  UriInterface $absoluteUri
     * @return self
     */
    public static function fromSchemeFailure(UriInterface $absoluteUri)
    {
        $matchResult = new self();
        $matchResult->type = 'schemeFailure';
        $matchResult->absoluteUri = $absoluteUri;
        return $matchResult;
    }

    /**
     * Creates a match failure match result.
     *
     * @return self
     */
    public static function fromMatchFailure()
    {
        $matchResult = new self();
        $matchResult->type = 'matchFailure';
        return $matchResult;
    }

    /**
     * Creates a new match result from a child match.
     *
     * @param  self   $childMatchResult
     * @param  array  $parentParams
     * @param  string $childRouteName
     * @return self
     * @throws DomainException
     */
    public static function fromChildMatch(self $childMatchResult, $parentParams, $childRouteName)
    {
        if (!$childMatchResult->isSuccess()) {
            throw new DomainException('Child match must be a successful match result');
        }

        $matchResult = self::fromSuccess($childMatchResult->getParams() + $parentParams);
        $matchResult->routeName = $childRouteName;

        if (null !== $childMatchResult->getRouteName()) {
            $matchResult->routeName .= '/' . $childMatchResult->getRouteName();
        }

        return $matchResult;
    }

    /**
     * Merges two method failure match results.
     *
     * @param  self $firstMatchResult
     * @param  self $secondMatchResult
     * @return self
     * @throws DomainException
     */
    public static function mergeMethodFailures(self $firstMatchResult, self $secondMatchResult)
    {
        if (!$firstMatchResult->isMethodFailure() || !$secondMatchResult->isMethodFailure()) {
            throw new DomainException('Both match results must be method failures');
        }

        return self::fromMethodFailure(array_unique(
            array_merge($firstMatchResult->getAllowedMethods(), $secondMatchResult->getAllowedMethods())
        ));
    }

    /**
     * Returns whether the result is a success.
     *
     * In case of success, you can retrieve the matched route name via {@link MatchResult::getRouteName()} and all route
     * parameters via {@link MatchResult::getParams()}.
     *
     * @return bool
     */
    public function isSuccess()
    {
        return 'success' === $this->type;
    }

    /**
     * Returns whether the result is a method failure.
     *
     * In case of a method failure, it is advised to return a 405 response with an "Allow" header, listing the allowed
     * methods supplied by {@link MatchResult::getAllowedMethods()}.
     *
     * @return bool
     */
    public function isMethodFailure()
    {
        return 'methodFailure' === $this->type;
    }

    /**
     * Returns whether the result is a scheme failure.
     *
     * In case of a scheme failure, it is advised to return a 308 response, redirecting the user to the URI specified by
     * {@link MatchResult::getAbsoluteUri()}.
     *
     * @return bool
     */
    public function isSchemeFailure()
    {
        return 'schemeFailure' === $this->type;
    }

    /**
     * Returns the matched route name.
     *
     * @return string
     * @throws DomainException
     */
    public function getRouteName()
    {
        if (!$this->isSuccess()) {
            throw new DomainException('Route name is only available on successful match');
        }

        return $this->routeName;
    }

    /**
     * Returns the route parameters.
     *
     * @return array
     * @throws DomainException
     */
    public function getParams()
    {
        if (!$this->isSuccess()) {
            throw new DomainException('Params are only available on successful match');
        }

        return $this->params;
    }

    /**
     * Returns the allowed methods for the matched route.
     *
     * @return array
     * @throws DomainException
     */
    public function getAllowedMethods()
    {
        if (!$this->isMethodFailure()) {
            throw new DomainException('Allowed methods are only available on method failure');
        }

        return $this->allowedMethods;
    }

    /**
     * Returns the absolute URI pointing to the matched route.
     *
     * @return UriInterface
     * @throws DomainException
     */
    public function getAbsoluteUri()
    {
        if (!$this->isSchemeFailure()) {
            throw new DomainException('Absolute URI is only available on scheme failure');
        }

        return $this->absoluteUri;
    }
}
