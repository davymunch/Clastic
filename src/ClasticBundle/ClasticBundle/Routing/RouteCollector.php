<?php
/**
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ClasticBundle\ClasticBundle\Routing;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteCollector
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->container->get('dispatcher')
            ->addListener(KernelEvents::REQUEST, array($this, 'collectRoutes'), 1000000);
    }

    protected function init()
    {

    }

    public function collectRoutes()
    {
        $routeCollection = new RouteCollection();

        $routeCollection->add('homepage', new Route('/', array(
                '_controller' => function() {
                    return new Response('bls');
                }
            )));

        $this->container
            ->register('routing.context', 'Symfony\Component\Routing\RequestContext');
        $this->container
            ->register('routing.urlMatcher', 'Symfony\Component\Routing\Matcher\UrlMatcher')
            ->setArguments(array($routeCollection, new Reference('routing.context')));
        $this->container
            ->register('listener.router', 'Symfony\Component\HttpKernel\EventListener\RouterListener')
            ->setArguments(array(new Reference('routing.urlMatcher')));
        $this->container
            ->get('dispatcher')
            ->addSubscriber($this->container->get('listener.router'));
    }
}
