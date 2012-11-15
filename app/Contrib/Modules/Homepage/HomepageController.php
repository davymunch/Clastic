<?php
/*
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Contrib\Modules\Homepage;



class HomepageController extends \Core\Modules\Homepage\HomepageController
{

	protected $controllerName = 'Homepage';

	public function getRoutes()
	{
		$routes = parent::getRoutes();

		return $routes;
	}


}