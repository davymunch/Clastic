<?php
/*
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Plugins\Backoffice\Controller;

use Clastic\Clastic;
use Assetic\Filter\CssMinFilter;
use Assetic\Asset\FileAsset;
use Assetic\Asset\StringAsset;
use Assetic\Asset\AssetReference;
use Clastic\Event\AssetEvent;
use Clastic\Event\ThemeEvent;
use Clastic\Plugin\PluginController;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Plugin that handles the backoffice.
 */
class BackofficeController extends PluginController
{
    protected function registerDispatchers(EventDispatcher &$dispatcher)
    {
        if ($this->getRequest()->isBackoffice()) {
            $dispatcher->addListener(Clastic::EVENT_THEME, array($this, 'switchBackofficeTheme'));
            $dispatcher->addListener(Clastic::EVENT_ASSETS_DEFAULTS, array($this, 'setAssets'));
        }
    }

    public function switchBackofficeTheme(ThemeEvent $event)
    {
        $theme = &$event->getTheme();
        $theme = 'Backoffice';
    }

    public function setAssets(AssetEvent $event)
    {
        $assets = &$event->getAssets();

        // Add css
        $assets->css()->add(new FileAsset(__DIR__ . '/../Resources/public/css/normalize.css'));

        // Add css filters
        $assets->getFilter()->get('css')->ensure(new CssMinFilter());
    }
}
