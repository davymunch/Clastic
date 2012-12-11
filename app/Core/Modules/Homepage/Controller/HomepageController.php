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

        $am = $this->assets->getManager();
        $am->set('homepage_css', new FileAsset(__DIR__.'/../Resources/public/css/homepage.css'));
        $am->set('homepage2_css', new FileAsset(__DIR__.'/../Resources/public/css/homepage2.css'));

        $am->set('module', $this->assets->getFactory()->createAsset(array(
            '@homepage_css',
            '@homepage2_css',
        )));

        $this->assets->getFilter()->set('css', new FilterCollection(array(
            new CssMinFilter()
        )));

        $m = $this->assets->getManager()->get('module');
        $m->ensureFilter($this->assets->getFilter()->get('css'));
        $m->setTargetPath($m->getTargetPath() . '.css');
        $writer = $this->assets->getWriter();
        $writer->writeAsset($m);

        $m2 = $this->assets->getFactory()->createAsset('@homepage_css');
        $m2->ensureFilter($this->assets->getFilter()->get('css'));
        $m2->add(new FileAsset(__DIR__.'/../Resources/public/css/homepage2.css'));
        $m2->setTargetPath($m2->getTargetPath() . '.css');
        $writer = $this->assets->getWriter();
        $writer->writeAsset($m2);

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
