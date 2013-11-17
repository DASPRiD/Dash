<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Mvc\Router\Http\Parser;

use Dash\Mvc\Router\Exception;

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
    protected $parts;

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
            $this->getParts(),
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
            $this->regex = $this->buildRegex($this->getParts(), $this->constraints);
        }

        return $this->regex;
    }

    /**
     * Gets parsed parts.
     *
     * @return array
     */
    protected function getParts()
    {
        if ($this->parts === null) {
            $this->parts = $this->parsePattern($this->pattern);
        }

        return $this->parts;
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
        $parts           = [];
        $levelParts      = [&$parts];
        $level           = 0;
        $quotedDelimiter = preg_quote($this->delimiter);

        while ($currentPos < $length) {
            preg_match('(\G(?P<literal>[^:{\[\]]*)(?P<token>[:\[\]]|$))', $pattern, $matches, 0, $currentPos);

            $currentPos += strlen($matches[0]);

            if (!empty($matches['literal'])) {
                $levelParts[$level][] = ['literal', $matches['literal']];
            }

            if ($matches['token'] === ':') {
                if (!preg_match('(\G(?P<name>[^:' . $quotedDelimiter . '{\[\]]+)(?:{(?P<delimiters>[^}]+)})?:?)', $pattern, $matches, 0, $currentPos)) {
                    throw new Exception\RuntimeException('Found empty parameter name');
                }

                $levelParts[$level][] = ['parameter', $matches['name'], isset($matches['delimiters']) ? $matches['delimiters'] : null];

                $currentPos += strlen($matches[0]);
            } elseif ($matches['token'] === '[') {
                $levelParts[$level][] = ['optional', []];
                $levelParts[$level + 1] = &$levelParts[$level][count($levelParts[$level]) - 1][1];

                $level++;
            } elseif ($matches['token'] === ']') {
                unset($levelParts[$level]);
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

        return $parts;
    }

    /**
     * Builds the matching regex from parsed parts.
     *
     * @param  array $parts
     * @param  array $constraints
     * @param  int   $groupIndex
     * @return string
     */
    protected function buildRegex(array $parts, array $constraints, &$groupIndex = 1)
    {
        $regex = '';

        foreach ($parts as $part) {
            switch ($part[static::TYPE]) {
                case 'literal':
                    $regex .= preg_quote($part[static::LITERAL]);
                    break;

                case 'parameter':
                    $groupName = '?P<param' . $groupIndex . '>';

                    if (isset($constraints[$part[static::NAME]])) {
                        $regex .= '(' . $groupName . $constraints[$part[static::NAME]] . ')';
                    } elseif ($part[static::DELIMITERS] === null) {
                        $regex .= '(' . $groupName . '[^' . preg_quote($this->delimiter) . ']+)';
                    } else {
                        $regex .= '(' . $groupName . '[^' . $part[static::DELIMITERS] . ']+)';
                    }

                    $this->paramMap['param' . $groupIndex++] = $part[static::NAME];
                    break;

                case 'optional':
                    $regex .= '(?:' . $this->buildRegex($part[static::NAME], $constraints, $groupIndex) . ')?';
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
     * @param  bool  $isOptional
     * @return string
     */
    protected function buildString(array $parts, array $mergedParams, array $defaults, $isOptional = false)
    {
        $path      = '';
        $skip      = true;
        $skippable = false;

        foreach ($parts as $part) {
            switch ($part[static::TYPE]) {
                case 'literal':
                    $path .= $part[static::LITERAL];
                    break;

                case 'parameter':
                    $skippable = true;

                    if (!isset($mergedParams[$part[static::NAME]])) {
                        if (!$isOptional) {
                            throw new Exception\InvalidArgumentException(sprintf('Missing parameter "%s"', $part[static::NAME]));
                        }

                        return '';
                    } elseif (!$isOptional || !isset($defaults[$part[static::NAME]]) || $defaults[$part[static::NAME]] !== $mergedParams[$part[static::NAME]]) {
                        $skip = false;
                    }

                    // @todo Implement proper encoding strategy
                    $path .= $mergedParams[$part[static::NAME]];

                    $this->assembledParams[] = $part[static::NAME];
                    break;

                case 'optional':
                    $skippable = true;
                    $optionalPart = $this->buildString($part[static::NAME], $mergedParams, $defaults, true);

                    if ($optionalPart !== '') {
                        $path .= $optionalPart;
                        $skip = false;
                    }
                    break;
            }
        }

        if ($isOptional && $skippable && $skip) {
            return '';
        }

        return $path;
    }
}
