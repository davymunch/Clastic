<?php
/**
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Clastic\Asset;

use Assetic\AssetManager;
use Assetic\Factory\AssetFactory;

class Assets
{
    protected $manager;

    protected $factory;

    public function __construct()
    {
        $this->manager = new AssetManager();
        $this->factory = new AssetFactory(CLASTIC_ROOT . '/cache');
        $this->factory->setAssetManager($this->manager);
    }

    public function getFactory()
    {
        return $this->factory;
    }

    public function getManager()
    {
        return $this->manager;
    }

}
