<?php
/*
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Plugins\Backoffice;

use Clastic\Clastic;
use Clastic\Event\ThemeEvent;
use Clastic\Plugin\PluginController;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Plugin that handles the backoffice.
 */
class BackofficePlugin extends PluginController
{
    protected function registerDispatchers(EventDispatcher &$dispatcher)
    {
        $dispatcher->addListener(Clastic::EVENT_THEME, array($this, 'switchBackofficeTheme'));
    }

    public function switchBackofficeTheme(ThemeEvent $event)
    {
        if ($this->getRequest()->isBackoffice()) {
            $theme = &$event->getTheme();
            $theme = 'Backoffice';
        }
    }
}
