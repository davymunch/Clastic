<?php
/*
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Clastic\Clastic;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache;
use Symfony\Component\Routing;
use Symfony\Component\HttpKernel;

$time = microtime(true);

define('CLASTIC_ROOT', __DIR__);

umask(0);

require_once('vendor/autoload.php');

$request = Request::createFromGlobals();

$framework = new Clastic($request);
//$framework = new HttpCache\HttpCache($framework, new HttpCache\Store(CLASTIC_ROOT.'/Cache'), new HttpCache\Esi());

$response = $framework->handle($request)->send();

//var_dump(array('mem' => round(memory_get_peak_usage(true)/1024/1024, 2) . ' MB', 'time' => round((microtime(true)-$time)*1000, 2) . ' ms'));