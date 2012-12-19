<?php
/**
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Themes\Clean\Controller;

use Clastic\Clastic;
use Assetic\Asset\FileAsset;
use Clastic\Event\AssetEvent;

class CleanTheme
{
    protected $name = 'Clean';

    public function __construct()
    {
        Clastic::getDispatcher()->addListener(Clastic::EVENT_ASSETS_DEFAULTS, array($this, 'setAssets'));
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

        // Add css filters
        //$assets->getFilter()->get('css')->ensure(new CssMinFilter());
    }
}