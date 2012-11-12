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

class RequestEvent extends Event
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function &getRequest()
    {
        return $this->request;
    }
}