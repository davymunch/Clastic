<?php
/*
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Modules\Homepage;

use Clastic\Module\ModuleController;
use Clastic\Block\Block;
use Clastic\Clastic;
use Clastic\Event\BlockCollectionEvent;
use Symfony\Component\HttpFoundation\Response;

class HomepageController extends ModuleController
{

	protected $controllerName = 'Homepage';

	public function handle()
	{
		$this->getDispatcher()->addListener(Clastic::EVENT_PRE_RENDER, array($this, 'addBlocks'));

		$response = new Response($this->render('@Homepage/homepage.html.twig', array(
      'rand' => rand(),
    )));
		$response->setPrivate();
		$response->prepare($this->getRequest());
		return $response;
	}

	public function addBlocks(BlockCollectionEvent $event)
	{
		$collection = $event->getCollection();
		$block = new Block('test');
		$block->setContent('testblock');
		$collection->addBlock($block);
	}



}