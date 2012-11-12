<?php
/*
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clastic\Routing;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Route;

/**
 * ModuleMatcher matches URL based on a set of routes. Strips the first
 * part to find the moduleController.
 *
 * @api
 */
class ModuleMatcher extends UrlMatcher
{

	/**
	 * {@inheritdoc}
	 */
	public function match($pathinfo)
	{
		return parent::match('/' . current(array_filter(explode('/', $pathinfo))));
	}
}
