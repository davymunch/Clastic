<?php
/**
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Contrib\Providers\Monolog;

use Clastic\Provider\ProviderInterface;
use Monolog\Handler\StreamHandler;
use Clastic\Clastic;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MonologProvider implements ProviderInterface
{
    public static function register(ContainerInterface &$container)
    {
        $container
          ->register('watchdog', '\Clastic\Bridge\Logger')
          ->setArguments(array('watchdog'))
          ->addMethodCall('pushHandler', array(new StreamHandler(CLASTIC_ROOT . '/logs/watchdog-' . Clastic::getSiteId() . '.log')));
    }

}