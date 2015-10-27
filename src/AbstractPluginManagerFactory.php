<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Abstract factory for plugin managers.
 */
abstract class AbstractPluginManagerFactory implements FactoryInterface
{
    /**
     * @var string
     */
    private $configKey;

    /**
     * @var string
     */
    private $className;

    /**
     * Caches the abstract getter results to eliminate performance impact.
     */
    public function __construct()
    {
        $this->configKey = $this->getConfigKey();
        $this->className = $this->getClassName();
    }

    /**
     * {@inheritdoc}
     *
     * @return AbstractPluginManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->has('config') ? $container->get('config') : [];

        if (isset($config['dash'][$this->configKey]) && is_array($config['dash'][$this->configKey])) {
            return new $this->className($container, $config['dash'][$this->configKey]);
        }

        return new $this->className($container);
    }

    /**
     * @return string
     */
    abstract protected function getConfigKey();

    /**
     * @return string
     */
    abstract protected function getClassName();
}
