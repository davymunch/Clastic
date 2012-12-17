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
use Twig_Environment;


/**
 * Asset event.
 * Used to alter Twig
 */
class TwigEvent extends Event
{
    /**
     * Holds the assets.
     *
     * @var Twig_Environment
     */
    private $twig;

    /**
     * Constructor for the event.
     *
     * @param Twig_Environment
     */
    public function __construct(Twig_Environment &$twig)
    {
        $this->twig = &$twig;
    }

    /**
     * Getter for the assets.
     *
     * @return Twig_Environment
     */
    public function &getTwig()
    {
        return $this->twig;
    }
}