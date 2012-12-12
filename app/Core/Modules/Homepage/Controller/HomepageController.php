<?php
/*
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Modules\Homepage\Controller;

use Clastic\Module\ModuleController;
use Assetic\Asset\AssetReference;
use CssRemoveCommentsMinifierFilter;
use Assetic\Filter\FilterCollection;
use Assetic\Filter\CssMinFilter;
use Assetic\Filter\Yui\JsCompressorFilter;
use Assetic\Filter\JSMinPlusFilter;
use Assetic\Filter\CssImportFilter;
use Assetic\Asset\FileAsset;
use Assetic\Asset\StringAsset;
use Clastic\Block\Block;
use Clastic\Clastic;
use Clastic\Event\BlockCollectionEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller that handles the homepage.
 */
class HomepageController extends ModuleController
{
    protected $controllerName = 'Homepage';

    public function handle($_method)
    {
        $this->getDispatcher()->addListener(Clastic::EVENT_PRE_RENDER, array($this, 'addBlocks'));

        return $this->$_method();
    }

    public function addBlocks(BlockCollectionEvent $event)
    {
        $collection = $event->getCollection();
        $block = new Block('test');
        $block->setContent('testblock');
        $collection->addBlock($block);
    }

    public function homepage()
    {
        $this->assets->css()->add(new AssetReference($this->assets->getAsset(), 'jquery'));
        $this->assets->css()->add(new FileAsset(__DIR__.'/../Resources/public/css/homepage2.css'));

        $this->assets->js()->add(new AssetReference($this->assets->getAsset(), 'jquery2'));

        //TODO these will be placed in the top template.
        //var_dump($this->assets->getCssUri());
        //var_dump($this->assets->getJsUri());

        $response = new Response($this->render(
            '@Homepage/homepage.html.twig',
            array(
                 'rand' => rand(),
            )
        ));
        $response->setPrivate();
        $response->prepare($this->getRequest());
        return $response;
    }
}