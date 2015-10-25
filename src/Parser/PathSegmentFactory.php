<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Parser;

/**
 * Factory for path segments.
 */
class PathSegmentFactory extends AbstractSegmentFactory
{
    /**
     * {@inheritdoc}
     */
    protected $patternOptionName = 'path';

    /**
     * {@inheritdoc}
     */
    protected $delimiter = '/';
}
