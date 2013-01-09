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

use Clastic\Asset\Assets;

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
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var Assets
     */
    protected $assets;

    public function __construct()
    {
        $this->assets = &Clastic::getAssets();
    }

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
     * Dependency Injection api.
     *
     * @api
     *
     * @param string $id
     * @return object
     */
    final protected function get($id)
    {
        return Clastic::get($id);
    }

    /**
     * Gets a reference to the request.
     *
     * @api
     *
     * @return \Clastic\Request
     */
    final public function &getRequest()
    {
        if (is_null($this->request)) {
            $this->request = Clastic::getRequest();
        }
        return $this->request;
    }
}