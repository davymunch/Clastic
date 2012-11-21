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


/**
 * BlockCollection event.
 * Used to alter the blockCollection.
 */
class ThemeEvent extends Event
{
    /**
     * Holds the theme name.
     *
     * @var string
     */
    private $theme;

    /**
     * Constructor for the event.
     *
     * @param string
     */
    public function __construct($theme)
    {
        $this->theme = $theme;
    }

    /**
     * Getter for the theme name.
     *
     * @return string
     */
    public function &getTheme()
    {
        return $this->theme;
    }
}