<?php
/*
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Modules\Dashboard\Controller;

use Clastic\Module\ModuleController;
use Assetic\Asset\FileAsset;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends ModuleController
{

    protected $controllerName = 'Dashboard';

    public function handle($_method)
    {
        return $this->$_method();
    }

    public function dashboard()
    {
        $this->assets->css()->add(new FileAsset(__DIR__ . '/../Resources/css/dashboard.css'));
        return new Response($this->render('@Dashboard/dashboard.html.twig'));
    }

}