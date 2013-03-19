<?php
/**
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Clastic\Clastic;

class App extends Clastic
{
    public function registerBundles()
    {
        require_once(CLASTIC_ROOT . '/bundles/Clastic/TestBundle/TestBundle.php');

        $bundles = array(
            new \ClasticBundle\ClasticBundle\ClasticBundle(),
            new \Clastic\TestBundle\TestBundle(),
        );

        return $bundles;
    }
}
