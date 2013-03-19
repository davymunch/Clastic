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
use Clastic\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache;
use Symfony\Component\Routing;
use Symfony\Component\HttpKernel;

$time = microtime(true);

define('CLASTIC_ROOT', dirname(__DIR__));

umask(0);

error_reporting(E_ALL);
ini_set('display_errors', 1);

$loader = require_once(__DIR__ . '/../vendor/autoload.php');



require_once(__DIR__ . '/../app/App.php');
$clastic = new App('dev', true);
//$clastic = new HttpCache\HttpCache($clastic, new HttpCache\Store(CLASTIC_ROOT.'/Cache'), new HttpCache\Esi());
$request = Request::createFromGlobals();
$response = $clastic->handle($request);
$response->send();
$clastic->terminate($request, $response);