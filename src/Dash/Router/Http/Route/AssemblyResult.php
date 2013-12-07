<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\Http\Route;

/**
 * A generic assembly result which is returned by assemble methods.
 *
 * We are not using Zend\Uri here for performance reasons. All the normalization
 * going on in Zend\Uri have a huge performance impact and are not required
 * for simple assembling.
 *
 * There is no validation done in this class, as it is assumed that someone who
 * writes custom route classes actually knows what one is doing. We only take
 * care of required encoding here.
 *
 * Seriously, consider twice before tweaking this class, as it is quite
 * performance-critical to assembling. Any changes should be backed by proper
 * benchmarks.
 */
class AssemblyResult
{
    /**
     * These characters are allowed within a path and should not be encoded.
     *
     * @var array
     */
    protected static $allowedPathChars = [
        '%2F' => '/',
        '%40' => '@',
        '%3A' => ':',
        '%3B' => ';',
        '%2C' => ',',
        '%3D' => '=',
        '%2B' => '+',
        '%21' => '!',
        '%2A' => '*',
        '%7C' => '|',
    ];

    /**
     * @var null|string
     */
    protected $scheme;

    /**
     * @var null|string
     */
    protected $host;

    /**
     * @var null|string
     */
    protected $path;

    /**
     * @var null|array
     */
    protected $query;

    /**
     * @var null|string
     */
    protected $fragment;

    /**
     * Sets the scheme.
     *
     * @param string $scheme
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
    }

    /**
     * Sets the host.
     *
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Prepends a path to an already set one.
     *
     * @param string $path
     */
    public function prependPath($path)
    {
        $this->path = $path . $this->path;
    }

    /**
     * Sets the query.
     *
     * @param array $query
     */
    public function setQuery(array $query)
    {
        $this->query = $query;
    }

    /**
     * Sets the fragment.
     *
     * @param string $fragment
     */
    public function setFragment($fragment)
    {
        $this->fragment = $fragment;
    }

    /**
     * Converts the assembly result to a string.
     *
     * If $forceCanonical is set to false, a scheme or host is only prepended
     * to the result if they differ from the references. In case $forceCanonical
     * is set to true, either the set scheme and host will be used, or if not
     * set, the reference values.
     *
     * @param  string $referenceScheme
     * @param  string $referenceHost
     * @param  bool   $forceCanonical
     * @return string
     */
    public function toString($referenceScheme, $referenceHost, $forceCanonical)
    {
        $url = '';

        if ($forceCanonical || $this->scheme !== null && $referenceScheme !== $this->scheme) {
            $url .= ($this->scheme ?: $referenceScheme) . ':';
        }

        if ($forceCanonical || $this->host !== null && $referenceHost !== $this->host) {
            $url .= '//' . ($this->host ?: $referenceHost);
        }

        $url .= strtr(rawurlencode($this->path), static::$allowedPathChars);

        if ($this->query !== null) {
            $url .= '?' . http_build_query($this->query, '', '&');
        }

        if ($this->fragment !== null) {
            $url .= '#' . $this->fragment;
        }

        return $url;
    }
}
