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
use Assetic\Asset\AssetCollection;
use Clastic\Event\AssetEvent;
use Clastic\Clastic;
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
        $this->prepareBags();
        $this->setDefaults();
    }

    /**
     * Instantiate all components.
     */
    protected function instantiate()
    {
        $this->asset = new AssetManager();

        $this->filter = new FilterManager();

        $this->factory = new AssetFactory(CLASTIC_ROOT . '/');

        $this->writer = new AssetWriter(CLASTIC_ROOT . '/' . $this->writeDirectory);
    }

    /**
     * Set some default stuff.
     */
    protected function setDefaults()
    {
        Clastic::getDispatcher()->dispatch(Clastic::EVENT_ASSETS_DEFAULTS, new AssetEvent($this))->getAssets();
    }

    /**
     * Prepare some bags to hold all data.
     */
    protected function prepareBags()
    {
        $this->getFilter()->set('css', new FilterCollection());
        $this->getFilter()->set('js', new FilterCollection());

        $this->getAsset()->set('css', new AssetCollection());
        $this->getAsset()->set('js', new AssetCollection());
    }

    /**
     * Getter for the AssetFactory.
     *
     * @return \Assetic\Factory\AssetFactory
     */
    public function &getFactory()
    {
        return $this->factory;
    }

    /**
     * Getter for the AssetManager.
     *
     * @return \Assetic\AssetManager
     */
    public function &getAsset()
    {
        return $this->asset;
    }

    /**
     * Getter for the AssetWriter.
     *
     * @return \Assetic\AssetWriter
     */
    public function &getWriter()
    {
        return $this->writer;
    }

    /**
     * Getter for the FilterManager.
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
    public function getCssUri($name = 'css')
    {
        return $this->getAssetUri($name, 'css');
    }

    /**
     * Write all javascript assets to a file and return the uri of file.
     *  - Returns false if the file would be empty.
     *  - Returns a path if the file is build.
     *
     * @return bool|string
     */
    public function getJsUri($name = 'js')
    {
        return $this->getAssetUri($name, 'js');
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
            $path = 'assetic/' . $this->factory->generateAssetName($assets, array()) . '.' . $extension;
            $assets->ensureFilter($this->getFilter()->get($extension));
            $assets->setTargetPath($path);
            $writer = $this->getWriter();
            $writer->writeAsset($assets);
            return $this->writeDirectory . '/' . $assets->getTargetPath();
        }
        return false;
    }

    public function __call($name, $arguments = array())
    {
        if (!$this->getAsset()->has($name)) {
            $this->getAsset()->set($name, new AssetCollection());
        }
        return $this->getAsset()->get($name);
    }
}
