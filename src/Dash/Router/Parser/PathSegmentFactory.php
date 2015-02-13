<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\Parser;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\MutableCreationOptionsInterface;

/**
 * Factory for path segments.
 *
 * The factory creates a hostname-specific segment parser. Parsers which share
 * the same pattern and constraints will be cached and re-used.
 */
class PathSegmentFactory implements FactoryInterface, MutableCreationOptionsInterface
{
    /**
     * @var array
     */
    protected $instances = [];

    /**
     * @var null|array
     */
    protected $createOptions;

    public function setCreationOptions(array $options)
    {
        $this->createOptions = $options;
    }

    /**
     * @return Segment
     */
    public function createService(ServiceLocatorInterface $parserManager)
    {
        $pattern     = (isset($this->createOptions['path']) ? $this->createOptions['path'] : '');
        $constraints = (isset($this->createOptions['constraints']) ? $this->createOptions['constraints'] : []);
        $key         = serialize([$pattern, $constraints]);

        if (!isset($this->instances[$key])) {
            $this->instances[$key] = new Segment('/', $pattern, $constraints);
        }

        return $this->instances[$key];
    }
}