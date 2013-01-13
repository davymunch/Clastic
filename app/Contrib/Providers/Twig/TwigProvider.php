<?php
/**
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Contrib\Providers\Twig;

use Clastic\Provider\ProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Clastic\Bridge\TwigExtension;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Clastic\Clastic;

class TwigProvider implements ProviderInterface
{
    protected $templateEngine;

    public static function register(ContainerInterface &$container)
    {
        $container->register('twig', new self());
    }

    public function __call($name, $arguments)
    {
        if (is_null($this->templateEngine)) {
            $paths = array_filter(
                Clastic::getPaths('/Themes/' . Clastic::getTheme()->getName() . '/Resources/templates'),
                function ($path) {
                    return is_dir($path);
                }
            );
            $loader = new Twig_Loader_Filesystem($paths);
            $this->templateEngine = new Twig_Environment($loader, array(
                'cache' => Clastic::$debug ? false : CLASTIC_ROOT . '/cache/templates',
            ), array(
                'debug' => Clastic::$debug,
            ));
            $this->templateEngine->addExtension(new TwigExtension());
        }
        return call_user_func_array(array($this->templateEngine, $name), $arguments);
    }


}