<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\Transformer;

use Dash\Router\MatchResult\MatchResultInterface;

/**
 * Interface for transforming parameters.
 */
interface TransformerInterface
{
    /**
     * @param  SuccessfulMatch $matchResult
     * @return MatchResultInterface
     */
    public function transformMatch(SuccessfulMatch $matchResult);
    
    /**
     * @param  array $params
     * @return array
     */
    public function transformAssemble(array $params);
}
