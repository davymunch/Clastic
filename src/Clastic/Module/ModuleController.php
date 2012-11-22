<?php
/*
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clastic\Module;

use Clastic\Clastic;
use Clastic\Event\BlockCollectionEvent;
use Clastic\Block\BlockCollection;
use Clastic\Block\Block;
use Clastic\Controller;
use ReflectionClass;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abstract version of the ModuleController.
 */
abstract class ModuleController extends Controller
{

    /**
     * Holds a reference the template engine.
     *
     * @var \Twig_Environment
     */
    private $templateEngine;

    /**
     * Basic handler.
     *
     * @throws Exception
     */
    public function handle($method)
    {
        throw new \Exception('No implementation of handle found.');
    }

    /**
     * @return \Twig_Environment
     */
    protected function &getTemplateEngine()
    {
        if (is_null($this->templateEngine)) {
            $this->templateEngine = Clastic::getTemplateEngine();
            foreach ($this->getModulePaths() as $path) {
                $themePath = str_replace(
                    Clastic::getPaths(),
                    Clastic::getPaths('/Themes/' . Clastic::getTheme()),
                    $path
                );
                if (is_dir($themePath . '/templates')) {
                    $this->templateEngine->getLoader()->addPath($themePath . '/templates', $this->getControllerName());
                }
                if (is_dir($path . '/Resources/templates')) {
                    $this->templateEngine->getLoader()->addPath($path . '/Resources/templates', $this->getControllerName());
                }
            }
        }
        return $this->templateEngine;
    }

    /**
     * Renders a template.
     *
     * @param string $name    The template name
     * @param array  $context An array of parameters to pass to the template
     *
     * @api
     *
     * @return string The rendered template
     */
    protected function render($name, array $context = array(), $baseTemplate = 'page.html.twig')
    {
        if (is_null($baseTemplate)) {
            return $this->getTemplateEngine()->render($name, $context);
        }
        $collection = new BlockCollection();
        $contentBlock = new Block('_main_');
        $contentBlock->setContent($this->getTemplateEngine()->render($name, $context));
        $collection->addBlock($contentBlock);
        $this->getDispatcher()->dispatch(Clastic::EVENT_PRE_RENDER, new BlockCollectionEvent($collection));
        return $this->getTemplateEngine()->render($baseTemplate, $collection->renderBlocks());
    }

    /**
     * Get all module paths, excluding abstracts and interfaces.
     *
     * @return array
     */
    protected function getModulePaths()
    {
        return Clastic::getPaths('/Modules/' . $this->getControllerName());

        $paths = array();
        $class = new ReflectionClass($this);
        $paths[] = dirname($class->getFileName());
        while ($class = $class->getParentClass()) {
            $paths[] = dirname($class->getFileName());
        }
        $paths = array_filter(
            $paths,
            function ($path) {
                return strpos($path, '/src/') === false;
            }
        );
        return $paths;
    }

}