<?php
/*
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clastic\Provider;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

/**
 * This manager handles everything that involves more than one moduleController.
 */
class ProviderManager
{
    public static function registerProviders(ContainerInterface &$container)
    {
        $finder = new Finder();
        $iterator = $finder
          ->files()
          ->depth(1)
          ->name('*Provider.php')
          ->in(array_filter(\Clastic\Clastic::getPaths('/Providers'), function($dir) {return is_dir($dir);}));
        foreach ($iterator as $dir) {
            $uri = str_replace(array(
                CLASTIC_ROOT . '/app',
                '.php',
                '/'
            ), array(
                '',
                '',
                '\\',
            ), $dir->getPathName());
            call_user_func($uri . '::register', $container);
        }
    }
}