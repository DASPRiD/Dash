<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Parser;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Abstract factory for segments.
 *
 * The factory creates a segment parser. Parsers which share the same pattern and constraints will be cached and
 * re-used.
 */
abstract class AbstractSegmentFactory implements FactoryInterface
{
    /**
     * @var Segment[]
     */
    protected $cache = [];

    /**
     * @var string
     */
    protected $patternOptionName;

    /**
     * @var string
     */
    protected $delimiter;

    /**
     * {@inheritdoc}
     *
     * @return Segment
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $pattern     = (isset($options[$this->patternOptionName]) ? $options[$this->patternOptionName] : '');
        $constraints = (isset($options['constraints']) ? $options['constraints'] : []);
        $key         = serialize([$pattern, $constraints]);

        if (!isset($this->cache[$key])) {
            $this->cache[$key] = new Segment($this->delimiter, $pattern, $constraints);
        }

        return $this->cache[$key];
    }
}