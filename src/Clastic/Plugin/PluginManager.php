<?php
/*
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clastic\Plugin;

use Symfony\Component\Finder\Finder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Clastic\Clastic;

class PluginManager
{
    public static function triggerPlugins(EventDispatcher $dispatcher)
    {
        $pluginDirectories = array_filter(
            Clastic::getPaths('/Plugins'),
            function ($directory) {
                return is_dir($directory);
            }
        );

        $finder = new Finder();
        $iterator = $finder
          ->directories()
          ->depth(0)
          ->in($pluginDirectories);

        $plugins = array();
        foreach ($iterator as $plugin) {
            $pluginFile = $plugin->getRealPath() . '/' . $plugin->getRelativePathname() . 'Plugin.php';
            if (file_exists($pluginFile)) {
                $plugins[$plugin->getRelativePathname()] = $pluginFile;
            }
        }
        foreach ($plugins as $plugin) {
            require_once($plugin);

            $plugin = str_replace(array(CLASTIC_ROOT . '/app', '.php'), '', $plugin);
            $plugin = str_replace('/', '\\', $plugin);
            new $plugin($dispatcher);
        }
    }
}