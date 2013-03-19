<?php
/**
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ClasticBundle\ClasticBundle;

use ClasticBundle\ClasticBundle\Routing\RouteCollector;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClasticBundle extends Bundle
{
    public function boot()
    {
        new RouteCollector($this->container);
    }
}
