<?php
/*
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clastic;
/**
 * The base of all controllers.
 */
class Controller
{
	protected $controllerName;

	private $dispatcher;
	private $request;


	public function getControllerName()
	{
		return $this->controllerName;
	}

	/**
	 * @api
	 *
	 * @return \Symfony\Component\EventDispatcher\EventDispatcher
	 */
	final protected function &getDispatcher()
	{
		if (is_null($this->dispatcher)) {
			$this->dispatcher = Clastic::getDispatcher();
		}
		return $this->dispatcher;
	}

	/**
	 * @api
	 *
	 * @return \Symfony\Component\HttpFoundation\Request
	 */
	final public function &getRequest()
	{
		if (is_null($this->request)) {
			$this->request = Clastic::getRequest();
		}
		return $this->request;
	}

}