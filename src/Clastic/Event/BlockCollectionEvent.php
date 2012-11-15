<?php
/**
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clastic\Event;

use Clastic\Block\BlockCollection;
use Symfony\Component\EventDispatcher\Event;

/**
 * BlockCollection event.
 * Used to alter the blockCollection.
 */
class BlockCollectionEvent extends Event
{
	/**
	 * Holds the blockCollection.
	 *
	 * @var \Clastic\Block\BlockCollection
	 */
	private $collection;

	/**
	 * Constructor for the event.
	 *
	 * @param \Clastic\Block\BlockCollection $collection
	 */
	public function __construct(BlockCollection $collection)
	{
		$this->collection = $collection;
	}

	/**
	 * Getter for the blockCollection.
	 *
	 * @return \Clastic\Block\BlockCollection
	 */
	public function &getCollection()
	{
		return $this->collection;
	}
}