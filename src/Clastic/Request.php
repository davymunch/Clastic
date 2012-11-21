<?php
/**
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clastic;

use Symfony\Component\HttpFoundation as HttpFoundation;

class Request extends HttpFoundation\Request
{
    public function isBackoffice()
    {
        return current(array_filter(explode('/',$this->getPathInfo()))) == 'admin';
    }
}