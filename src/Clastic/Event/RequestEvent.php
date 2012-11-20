<?php
/*
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clastic\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\Event;

/**
 * Request event.
 *
 * This is used to alter requests during the request flow.
 */
class RequestEvent extends Event
{
    /**
     * The request to alter.
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * Constructor of the object.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the request.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function &getRequest()
    {
        return $this->request;
    }
}
