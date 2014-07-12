<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\Http\Parser;

use Dash\Router\Exception;

class Segment implements ParserInterface
{
    /**#@+
     * Descriptive part elements.
     */
    const TYPE       = 0;
    const NAME       = 1;
    const LITERAL    = 1;
    const DELIMITERS = 2;
    /**#@-*/

    /**
     * @var string
     */
    protected $delimiter;

    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var array
     */
    protected $constraints;

    /**
     * @var array
     */
    protected $tokens;

    /**
     * @var string
     */
    protected $regex;

    /**
     * @var array
     */
    protected $paramMap = [];

    /**
     * @param string $delimiter
     * @param string $pattern
     * @param array  $constraints
     */
    public function __construct($delimiter, $pattern, array $constraints)
    {
        $this->delimiter   = $delimiter;
        $this->pattern     = $pattern;
        $this->constraints = $constraints;
    }

    public function parse($input, $offset)
    {
        $result = preg_match('(\G' . $this->getRegex() . ')', $input, $matches, null, $offset);

        if (!$result) {
            return null;
        }

        $params = [];

        foreach ($this->paramMap as $index => $name) {
            if (isset($matches[$index]) && $matches[$index] !== '') {
                $params[$name] = $matches[$index];
            }
        }

        return new ParseResult($params, strlen($matches[0]));
    }

    public function compile(array $params, array $defaults)
    {
        return $this->buildString(
            $this->getTokens(),
            array_merge($defaults, $params),
            $defaults
        );
    }

    /**
     * Gets regex for matching.
     *
     * @return string
     */
    protected function getRegex()
    {
        if ($this->regex === null) {
            $this->regex = $this->buildRegex($this->getTokens(), $this->constraints);
        }

        return $this->regex;
    }

    /**
     * Gets parsed tokens.
     *
     * @return array
     */
    protected function getTokens()
    {
        if ($this->tokens === null) {
            $this->tokens = $this->parsePattern($this->pattern);
        }

        return $this->tokens;
    }

    /**
     * Parses a pattern.
     *
     * @param  string $pattern
     * @return array
     * @throws Exception\RuntimeException
     */
    protected function parsePattern($pattern)
    {
        $currentPos      = 0;
        $length          = strlen($pattern);
        $tokens          = [];
        $level           = 0;
        $quotedDelimiter = preg_quote($this->delimiter);

        while ($currentPos < $length) {
            preg_match('(\G(?P<literal>[^:{\[\]]*)(?P<token>[:\[\]]|$))', $pattern, $matches, 0, $currentPos);

            $currentPos += strlen($matches[0]);

            if (!empty($matches['literal'])) {
                $tokens[] = ['literal', $matches['literal']];
            }

            if ($matches['token'] === ':') {
                if (!preg_match('(\G(?P<name>[^:' . $quotedDelimiter . '{\[\]]+)(?:{(?P<delimiters>[^}]+)})?:?)', $pattern, $matches, 0, $currentPos)) {
                    throw new Exception\RuntimeException('Found empty parameter name');
                }

                $tokens[] = ['parameter', $matches['name'], isset($matches['delimiters']) ? $matches['delimiters'] : null];

                $currentPos += strlen($matches[0]);
            } elseif ($matches['token'] === '[') {
                $tokens[] = array('optional-start');
                $level++;
            } elseif ($matches['token'] === ']') {
                $tokens[] = array('optional-end');
                $level--;

                if ($level < 0) {
                    throw new Exception\RuntimeException('Found closing bracket without matching opening bracket');
                }
            } else {
                break;
            }
        }

        if ($level > 0) {
            throw new Exception\RuntimeException('Found unbalanced brackets');
        }

        return $tokens;
    }

    /**
     * Builds the matching regex from parsed tokens.
     *
     * @param  array $tokens
     * @param  array $constraints
     * @return string
     */
    protected function buildRegex(array $tokens, array $constraints)
    {
        $groupIndex      = 1;
        $regex           = '';
        $quotedDelimiter = preg_quote($this->delimiter);

        foreach ($tokens as $token) {
            switch ($token[static::TYPE]) {
                case 'literal':
                    $regex .= preg_quote($token[static::LITERAL]);
                    break;

                case 'parameter':
                    $groupName = '?P<param' . $groupIndex . '>';

                    if (isset($constraints[$token[static::NAME]])) {
                        $regex .= '(' . $groupName . $constraints[$token[static::NAME]] . ')';
                    } elseif ($token[static::DELIMITERS] === null) {
                        $regex .= '(' . $groupName . '[^' . $quotedDelimiter . ']+)';
                    } else {
                        $regex .= '(' . $groupName . '[^' . $token[static::DELIMITERS] . ']+)';
                    }

                    $this->paramMap['param' . $groupIndex++] = $token[static::NAME];
                    break;

                case 'optional-start':
                    $regex .= '(?:';
                    break;

                case 'optional-end':
                    $regex .= ')?';
                    break;
            }
        }

        return $regex;
    }

    /**
     * Builds a string from parts.
     *
     * @param  array $parts
     * @param  array $mergedParams
     * @param  array $defaults
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    protected function buildString(array $parts, array $mergedParams, array $defaults)
    {
        $stack   = [];
        $current = [
            'is_optional' => false,
            'skip'        => true,
            'skippable'   => false,
            'path'        => '',
        ];

        foreach ($parts as $part) {
            switch ($part[static::TYPE]) {
                case 'literal':
                    $current['path'] .= $part[static::LITERAL];
                    break;

                case 'parameter':
                    $current['skippable'] = true;

                    if (!isset($mergedParams[$part[static::NAME]])) {
                        if (!$current['is_optional']) {
                            throw new Exception\InvalidArgumentException(sprintf('Missing parameter "%s"', $part[static::NAME]));
                        }

                        continue;
                    } elseif (!$current['is_optional'] || !isset($defaults[$part[static::NAME]]) || $defaults[$part[static::NAME]] !== $mergedParams[$part[static::NAME]]) {
                        $current['skip'] = false;
                    }

                    $current['path'] .= $mergedParams[$part[static::NAME]];
                    break;

                case 'optional-start':
                    $stack[] = $current;
                    $current = [
                        'is_optional' => true,
                        'skip'        => true,
                        'skippable'   => false,
                        'path'        => '',
                    ];
                    break;

                case 'optional-end':
                    $parent = array_pop($stack);

                    if (!($current['path'] !== '' && $current['is_optional'] && $current['skippable'] && $current['skip'])) {
                        $parent['path'] .= $current['path'];
                        $parent['skip'] = false;
                    }

                    $current = $parent;
                    break;
            }
        }

        return $current['path'];
    }
}
