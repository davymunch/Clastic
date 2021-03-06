<?php
/**
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clastic\Bridge;

use Clastic\Clastic;

class TwigExtension extends \Twig_Extension
{
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'Clastic';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('renderStylesheets', array($this, 'renderStylesheets'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('renderScripts', array($this, 'renderScripts'), array('is_safe' => array('html'))),
        );
    }

    public function renderStylesheets($name = 'css')
    {
        if ($src = Clastic::getAssets()->getCssUri($name)) {
            return '<link href="' . $src . '" type="text/css" rel="stylesheet" />';
        }
        return '';
    }

    public function renderScripts($name = 'js')
    {
        if ($src = Clastic::getAssets()->getJsUri($name)) {
            return '<script type="text/javascript" src="' . $src . '"></script>';
        }
        return '';
    }
}
