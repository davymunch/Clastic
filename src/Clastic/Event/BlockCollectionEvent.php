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

class BlockCollectionEvent extends Event
{
    private $collection;

    public function __construct(BlockCollection $collection)
    {
        $this->collection = $collection;
    }

    public function &getCollection()
    {
        return $this->collection;
    }
}