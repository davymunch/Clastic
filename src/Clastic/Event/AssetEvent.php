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

use Symfony\Component\EventDispatcher\Event;
use Clastic\Asset\Assets;


/**
 * Asset event.
 * Used to alter Assets
 */
class AssetEvent extends Event
{
    /**
     * Holds the assets.
     *
     * @var Assets
     */
    private $assets;

    /**
     * Constructor for the event.
     *
     * @param Assets
     */
    public function __construct(Assets &$assets)
    {
        $this->assets = &$assets;
    }

    /**
     * Getter for the assets.
     *
     * @return Assets
     */
    public function &getAssets()
    {
        return $this->assets;
    }
}