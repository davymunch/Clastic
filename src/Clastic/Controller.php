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
	/**
	 * Name of the controller.
	 *
	 * @var string
	 */
	protected $controllerName;

	/**
	 * Holds a reference to the eventDispatcher.
	 *
	 * @var \Symfony\Component\EventDispatcher\EventDispatcher
	 */
	private $dispatcher;

	/**
	 * Holds a reference to the request.
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	private $request;

	/**
	 * Getter for the controller's name.
	 *
	 * @return string
	 */
	public function getControllerName()
	{
		return $this->controllerName;
	}

	/**
	 * Returns a reference to the eventDispatcher.
	 *
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
	 * Gets a reference to the request.
	 *
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