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
use Core\Themes\Backoffice\Controller\BackofficeTheme;
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
            $dispatcher->addListener(Clastic::EVENT_THEME, array($this, 'setBackofficeTheme'));
        }
    }

    public function setBackofficeTheme($event)
    {
        $theme = &$event->getTheme();
        $theme = new BackofficeTheme();
    }

}
