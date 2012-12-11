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
use Assetic\FilterManager;
use Assetic\Factory\Worker\CacheBustingWorker;
use Assetic\AssetWriter;
use Assetic\Factory\AssetFactory;

class Assets
{
    protected $manager;

    protected $factory;

    protected $writer;

    protected $filter;

    public function __construct()
    {
        $this->manager = new AssetManager();
        $this->filter = new FilterManager();
        $this->factory = new AssetFactory(CLASTIC_ROOT . '/');
        $this->factory->setAssetManager($this->manager);
        $this->factory->setFilterManager($this->filter);
        //$this->factory->addWorker(new CacheBustingWorker(CacheBustingWorker::STRATEGY_CONTENT));
        $this->writer = new AssetWriter(CLASTIC_ROOT . '/cache');
    }

    public function &getFactory()
    {
        return $this->factory;
    }

    public function &getManager()
    {
        return $this->manager;
    }

    public function &getWriter()
    {
        return $this->writer;
    }

    public function &getFilter()
    {
        return $this->filter;
    }

}
