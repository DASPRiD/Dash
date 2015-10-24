<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace DashBench;

use Athletic\AthleticEvent;
use Dash\Router;
use Dash\RouterFactory;
use Zend\Diactoros\ServerRequest;
use Zend\ServiceManager\ServiceManager;

class RouterBench extends AthleticEvent
{
    /**
     * @var type
     */
    protected $config;

    /**
     * @var ServerRequest
     */
    protected $firstMatchRequest;

    /**
     * @var ServerRequest
     */
    protected $lastMatchRequest;

    /**
     * @var Router
     */
    protected $assemblyRouter;

    public function classSetUp()
    {
        $this->config = require __DIR__ . '/../config/module.config.php';
        $routes = [];

        for ($i = 100; $i > 0; --$i) {
            $routes['route' . $i] = [
                '/route' . $i, 'priority' => $i, 'children' => [
                    'create' => ['/create'],
                    'edit' => ['/edit/:id'],
                    'delete' => ['/delete/:id'],
                    'show' => ['/show/:id'],
                ],
            ];
        }

        $this->config['service_manager']['services'] = [
            'config' => [
                'dash' => [
                    'base_uri' => 'http://example.com',
                    'routes' => $routes,
                ],
            ],
        ];

        $this->assemblyRouter    = $this->createRouter();
        $this->firstMatchRequest = new ServerRequest([], [], 'http://example.com/route100/edit/100', 'GET');
        $this->lastMatchRequest  = new ServerRequest([], [], 'http://example.com/route1/edit/100', 'GET');
    }

    /**
     * @iterations 5000
     */
    public function matchFirstRoute()
    {
        $this->createRouter()->match($this->firstMatchRequest);
    }

    /**
     * @iterations 5000
     */
    public function matchLastRoute()
    {
        $this->createRouter()->match($this->lastMatchRequest);
    }

    /**
     * @iterations 5000
     */
    public function assemble()
    {
        $this->assemblyRouter->assemble(['id' => 100], ['name' => 'route100/edit']);
    }

    /**
     * Create a fresh router.
     *
     * We need to make sure that we use a completely fresh router (with a fresh service manager) on each match
     * iteration, as otherwise stuff can be cached between the calls which would distort the results. It is clear that
     * we are also benchmarking the service manager creation through this, but since we want to make sure that the
     * factories all do a proper job, this is a good trade-off.
     *
     * @return Router
     */
    protected function createRouter()
    {
        $serviceManager = new ServiceManager($this->config['service_manager']);
        $routerFactory  = new RouterFactory();

        return $routerFactory($serviceManager, '');
    }
}
