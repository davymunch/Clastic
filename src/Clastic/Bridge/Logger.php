<?php
/**
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clastic\Bridge;

use Symfony\Component\HttpKernel\Log\LoggerInterface;

class Logger extends \Monolog\Logger implements LoggerInterface
{

}