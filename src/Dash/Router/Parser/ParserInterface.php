<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\Parser;

/**
 * Interface every parser must implement.
 *
 * A parser is not only responsible for parsing input, but also for generating
 * output based on parameters and defaults.
 */
interface ParserInterface
{
    /**
     * Parses an input string starting at a given offset.
     *
     * @param  string $input
     * @param  int    $offset
     * @return null|ParseResult
     */
    public function parse($input, $offset);

    /**
     * Compiles an output string based on parameters and default values.
     *
     * @param  array $params
     * @param  array $defaults
     * @return string
     */
    public function compile(array $params, array $defaults);
}