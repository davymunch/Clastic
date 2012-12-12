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
use Assetic\Asset\StringAsset;
use Assetic\Asset\FileAsset;
use Assetic\Filter\FilterCollection;
use Assetic\Filter\CssMinFilter;
use Assetic\FilterManager;
use Assetic\Factory\Worker\CacheBustingWorker;
use Assetic\AssetWriter;
use Assetic\Factory\AssetFactory;

class Assets
{
    /**
     * @var AssetManager
     */
    protected $asset;

    /**
     * @var AssetFactory
     */
    protected $factory;

    /**
     * @var AssetWriter
     */
    protected $writer;

    /**
     * @var FilterManager
     */
    protected $filter;

    /**
     * @var string
     */
    protected $writeDirectory = 'cache';

    public function __construct()
    {
        $this->instantiate();
        $this->setDefaults();
        $this->prepareBags();
    }

    /**
     * Instantiate all components.
     */
    protected function instantiate()
    {
        $this->asset = new AssetManager();

        $this->filter = new FilterManager();

        $this->factory = new AssetFactory(CLASTIC_ROOT . '/');
        $this->factory->setAssetManager($this->asset);
        $this->factory->setFilterManager($this->filter);
        //$this->factory->addWorker(new CacheBustingWorker(CacheBustingWorker::STRATEGY_CONTENT));

        $this->writer = new AssetWriter(CLASTIC_ROOT . '/' . $this->writeDirectory);
    }

    /**
     * Set some default stuff.
     */
    protected function setDefaults()
    {
        $this->getAsset()->set('jquery', new StringAsset('.hide {display: none;} '));
        $this->getAsset()->set('jquery2', new StringAsset('alert("test");'));
    }

    /**
     * Prepare some bags to hold all data.
     */
    protected function prepareBags()
    {
        $this->getFilter()->set('css', new FilterCollection(array()));
        $cssFilters = $this->getFilter()->get('css');
        $cssFilters->ensure(new CssMinFilter());
        $this->getAsset()->set('css', $this->getFactory()->createAsset(array(), array('css')));

        $this->getFilter()->set('js', new FilterCollection(array()));
        //$jsFilters = $this->getFilter()->get('js');
        $this->getAsset()->set('js', $this->getFactory()->createAsset(array(), array('js')));
    }

    /**
     * Getter for the AssetFactory
     *
     * @return \Assetic\Factory\AssetFactory
     */
    public function &getFactory()
    {
        return $this->factory;
    }

    /**
     * Getter for the AssetManager
     *
     * @return \Assetic\AssetManager
     */
    public function &getAsset()
    {
        return $this->asset;
    }

    /**
     * Getter for the AssetWriter
     *
     * @return \Assetic\AssetWriter
     */
    public function &getWriter()
    {
        return $this->writer;
    }

    /**
     * Getter for the FilterManager
     *
     * @return \Assetic\FilterManager
     */
    public function &getFilter()
    {
        return $this->filter;
    }

    /**
     * Write all assets to a file and return the uri of file.
     *  - Returns false if the file would be empty.
     *  - Returns a path if the file is build.
     *
     * @return bool|string
     */
    public function getCssUri()
    {
        return $this->getAssetUri('css', 'css');
    }

    /**
     * Write all javascript assets to a file and return the uri of file.
     *  - Returns false if the file would be empty.
     *  - Returns a path if the file is build.
     *
     * @return bool|string
     */
    public function getJsUri()
    {
        return $this->getAssetUri('js', 'js');
    }

    /**
     * Write all assets to a file and return the uri of file.
     *  - Returns false if the file would be empty.
     *  - Returns a path if the file is build.
     *
     * @param string $name
     * @param string $extension
     * @return bool|string
     */
    private function getAssetUri($name, $extension)
    {
        $assets = $this->getAsset()->get($name);
        if (count($assets->all())) {
            $assets->setTargetPath($assets->getTargetPath() . '.' . $extension);
            $writer = $this->getWriter();
            $writer->writeAsset($assets);
            return $this->writeDirectory . '/' . $assets->getTargetPath();
        }
        return false;
    }

    /**
     * A collection of all stylesheets.
     *
     * @return \Assetic\Asset\AssetCollection
     */
    public function &css()
    {
        static $asset;
        if (is_null($asset)) {
            $asset = $this->getAsset()->get('css');
        }
        return $asset;
    }

    /**
     * A collection of all javascript files.
     *
     * @return \Assetic\Asset\AssetCollection
     */
    public function &js()
    {
        static $asset;
        if (is_null($asset)) {
            $asset = $this->getAsset()->get('js');
        }
        return $asset;
    }
}
