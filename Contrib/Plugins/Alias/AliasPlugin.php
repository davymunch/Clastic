<?php
/*
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Contrib\Plugins\Alias;

use Clastic\Plugin\PluginController;
use Clastic\Event\RequestEvent;
use Clastic\Clastic;
use Symfony\Component\EventDispatcher\EventDispatcher;

class AliasPlugin extends PluginController
{
	public function registerDispatchers(EventDispatcher &$dispatcher)
	{
		$dispatcher->addListener(Clastic::EVENT_PRE_HANDLE, array($this, 'resolveAlias'));
	}

	public function resolveAlias(RequestEvent $event)
	{
		$request = &$event->getRequest();

		if ($request->getPathInfo() == '/') {
			$route = '/homepage';
		}

		if (isset($route)) {
			$request->server->set('ALIAS_URI', $request->getPathInfo());
			$request->server->set('REQUEST_URI', $route);
			$request = $request->duplicate(null, null, null, null, null, $request->server->all());
		}
	}
}