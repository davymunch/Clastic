<?php
/**
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Themes\Backoffice\Controller;

use Clastic\Clastic;
use Assetic\Asset\FileAsset;
use Clastic\Event\AssetEvent;

class BackofficeTheme
{
    protected $name = 'Backoffice';

    public function __construct()
    {
        Clastic::get('dispatcher')->addListener(Clastic::EVENT_ASSETS_DEFAULTS, array($this, 'setAssets'));
    }

    public function getName()
    {
        return $this->name;
    }

    public function setAssets(AssetEvent $event)
    {
        $assets = &$event->getAssets();

        // Add css
        $assets->css()->add(new FileAsset(__DIR__ . '/../Resources/css/vendor/normalize.css'));
        $assets->css()->add(new FileAsset(__DIR__ . '/../Resources/css/vendor/bootstrap.css'));
        $assets->css()->add(new FileAsset(__DIR__ . '/../Resources/css/main.css'));
        $assets->css()->add(new FileAsset(__DIR__ . '/../Resources/css/vendor/bootstrap-responsive.css'));

        $assets->js_header()->add(new FileAsset(__DIR__ . '/../Resources/js/vendor/modernizer-2.6.2.min.js'));

        $assets->js()->add(new FileAsset(__DIR__ . '/../Resources/js/vendor/jquery-1.8.3.min.js'));
        $assets->js()->add(new FileAsset(__DIR__ . '/../Resources/js/vendor/bootstrap.min.js'));
        $assets->js()->add(new FileAsset(__DIR__ . '/../Resources/js/plugins.js'));

        // Add css filters
        //$assets->getFilter()->get('css')->ensure(new CssMinFilter());
    }
}