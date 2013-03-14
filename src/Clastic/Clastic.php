<?php
/*
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clastic;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * Clastic FrameworkController.
 */
abstract class Clastic
{
    /**
     * @var string The root dir of the app.
     */
    protected $rootDir;

    /**
     * @var string The environment scope.
     */
    protected $environment;

    /**
     * @var bool Indicates if the framework is in debug mode.
     */
    protected $debug;

    /**
     * @var bool Indicates if the framework is booted.
     */
    protected $booted;

    /**
     * @var BundleInterface[]
     */
    protected $bundles = array();

    /**
     * @var BundleInterface[]
     */
    protected $bundleMap = array();

    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @var EventDispatcher The event dispatcher.
     */
    protected $dispatcher;

    /**
     * Build the Clastic framework.
     *
     * @param $env
     * @param bool $debug
     */
    public function __construct($env, $debug = false)
    {
        $this->rootDir = $this->getRootDir();
        $this->environment = $env;
        $this->debug = $debug;
        $this->booted = false;

        $this->init();
    }

    /**
     * Initialize additional components.
     */
    public function init()
    {
        $this->container = new ContainerBuilder();

        $this->buildDispatcher();
        $this->buildControllerResolver();
        $this->buildHttpKernel();
    }

    /**
     * Get the root dir of the app.
     */
    public function getRootDir()
    {
        if (null === $this->rootDir) {
            $r = new \ReflectionClass($this);

            $this->rootDir = str_replace('\\', '/', dirname($r->getFileName()));
        }

        return $this->rootDir;
    }

    /**
     * Build the event dispatcher.
     */
    protected function buildDispatcher()
    {
        $this->container
            ->register('dispatcher', 'Symfony\Component\EventDispatcher\EventDispatcher');
    }

    /**
     * Build the controller resolver.
     */
    protected function buildControllerResolver()
    {
        $this->container
            ->register('controller_resolver', 'Symfony\Component\HttpKernel\Controller\ControllerResolver');
    }

    /**
     * Build the kernel.
     */
    protected function buildHttpKernel()
    {
        $this->container
            ->register('http_kernel', 'Symfony\Component\HttpKernel\HttpKernel')
            ->setArguments(array(
                $this->container->get('dispatcher'),
                $this->container->get('controller_resolver'),
            ));
    }

    /**
     * Initialize all bundles.
     *
     * @throws \LogicException
     */
    protected function initializeBundles()
    {
        // init bundles
        $this->bundles = array();
        $topMostBundles = array();
        $directChildren = array();

        foreach ($this->registerBundles() as $bundle) {
            $name = $bundle->getName();
            if (isset($this->bundles[$name])) {
                throw new \LogicException(sprintf('Trying to register two bundles with the same name "%s"', $name));
            }
            $this->bundles[$name] = $bundle;

            if ($parentName = $bundle->getParent()) {
                if (isset($directChildren[$parentName])) {
                    throw new \LogicException(sprintf('Bundle "%s" is directly extended by two bundles "%s" and "%s".', $parentName, $name, $directChildren[$parentName]));
                }
                if ($parentName == $name) {
                    throw new \LogicException(sprintf('Bundle "%s" can not extend itself.', $name));
                }
                $directChildren[$parentName] = $name;
            } else {
                $topMostBundles[$name] = $bundle;
            }
        }

        // look for orphans
        if (count($diff = array_values(array_diff(array_keys($directChildren), array_keys($this->bundles))))) {
            throw new \LogicException(sprintf('Bundle "%s" extends bundle "%s", which is not registered.', $directChildren[$diff[0]], $diff[0]));
        }

        // inheritance
        $this->bundleMap = array();
        foreach ($topMostBundles as $name => $bundle) {
            $bundleMap = array($bundle);
            $hierarchy = array($name);

            while (isset($directChildren[$name])) {
                $name = $directChildren[$name];
                array_unshift($bundleMap, $this->bundles[$name]);
                $hierarchy[] = $name;
            }

            foreach ($hierarchy as $bundle) {
                $this->bundleMap[$bundle] = $bundleMap;
                array_pop($bundleMap);
            }
        }
    }

    /**
     * @return BundleInterface[]
     */
    abstract function registerBundles();

    /**
     * Handle the requests.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return mixed
     */
    public function handle(Request $request)
    {
        if (false === $this->booted) {
            $this->boot();
        }

        $kernel = $this->container->get('http_kernel');

        return $kernel->handle($request);
    }

    /**
     * Boot the framework and all bundles.
     */
    protected function boot()
    {
        if (true === $this->booted) {
            return;
        }

        $this->initializeBundles();

        foreach ($this->getBundles() as $bundle) {
            $bundle->setContainer($this->container);
            $bundle->boot();
        }

        $this->booted = true;
    }

    /**
     * @return BundleInterface[]
     */
    protected function getBundles()
    {
        return $this->bundles;
    }
}
