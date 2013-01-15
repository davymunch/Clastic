<?php
/**
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Contrib\Providers\Notifier;

use Clastic\Provider\ProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Notifier\Notifier;

class NotifierProvider implements ProviderInterface
{
    public static function register(ContainerInterface &$container)
    {
        $notifier = new Notifier();
        $container->set('notifier', $notifier);
    }
}