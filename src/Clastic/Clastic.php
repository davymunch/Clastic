<?php
/*
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clastic;

use Clastic\Module\ModuleManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Core\Themes\Clean\Controller\CleanTheme;
use Clastic\Bridge\TwigExtension;
use Clastic\Asset\Assets;
use Assetic\Factory\AssetFactory;
use Clastic\Event\ThemeEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Monolog\Handler\StreamHandler;
use \Clastic\Bridge\Logger;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Clastic\Routing\ModuleMatcher;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\ConfigCache;
use Clastic\Event\RequestEvent;
use Clastic\Plugin\PluginManager;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel;
use Symfony\Component\Routing;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Clastic\Asset\AssetManager;


/**
 * Clastic FrameworkController.
 */
class Clastic extends HttpKernel\HttpKernel
{
    /**
     * Event name. Dispatched before the request is handled.
     */
    const EVENT_PRE_HANDLE = 'clastic.pre_handle';

    /**
     * Event name. Dispatched before rendering the base template.
     */
    const EVENT_PRE_RENDER = 'clastic.pre_render';

    /**
     * Event name. Dispatched when the assets are initialized.
     */
    const EVENT_ASSETS_DEFAULTS = 'clastic.assets.defaults';

    /**
     * Event name. Dispatched after resolving the theme.
     */
    const EVENT_THEME = 'clastic.theme';

    /**
     * Holds the config of the current site.
     *
     * @var array
     */
    protected static $config = array();

    /**
     * Enable debugging.
     *
     * @var bool
     */
    public static $debug = true;

    protected $container;

    /**
     * Hold doctrine's entityManager.
     *
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected static $dependencyInjection;

    /**
     * Holds the eventDispatcher.
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected static $eventDispatcher;

    /**
     * Holds the logger instance. This implements \Monolog\Logger
     *
     * @var \Clastic\Bridge\Logger
     */
    protected static $logger;

    /**
     * Holds the pdo connection.
     *
     * @todo is this needed?
     *
     * @var \PDO
     */
    protected static $pdo;

    /**
     * Holds the current request.
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected static $request;

    /**
     * Directory of the active site.
     *
     * @var string
     */
    protected static $siteDirectory;

    /**
     * ID of the active site.
     *
     * @var int
     */
    protected static $siteId;

    /**
     * Hold the template engine.
     *
     * @var \Twig_Environment
     */
    protected static $templateEngine;

    /**
     * Active theme.
     *
     * @var string
     */
    protected static $theme;

    protected static $assets;

    /**
     * Constructor
     *
     * @param Request $request
     *
     * @api
     */
    public function __construct(Request &$request)
    {
        $this->container = new ContainerBuilder();

        $this->resolveSite($request);
        $this->loadConfigAndDatabase();

        $this->container
          ->register('watchdog', '\Clastic\Bridge\Logger')
          ->setArguments(array('watchdog'))
          ->addMethodCall('pushHandler', array(new StreamHandler(CLASTIC_ROOT . '/logs/watchdog-' . static::getSiteId() . '.log')));
        $this->container
          ->register('routing.context', 'Symfony\Component\Routing\RequestContext');
        $this->container
          ->register('routing.urlMatcher', 'Symfony\Component\Routing\Matcher\UrlMatcher')
          ->setArguments(array(ModuleManager::createModuleRoutes(), new Reference('routing.context')));
        $this->container
          ->register('routing.resolver', 'Symfony\Component\HttpKernel\Controller\ControllerResolver')
          ->setArguments(array(static::$logger));
        $this->container
          ->register('listener.router', 'Symfony\Component\HttpKernel\EventListener\RouterListener')
          ->setArguments(array(new Reference('routing.urlMatcher')));
        $this->container
          ->register('listener.response', 'Symfony\Component\HttpKernel\EventListener\ResponseListener')
          ->setArguments(array('UTF-8'));
        $this->container
          ->register('dispatcher', 'Symfony\Component\EventDispatcher\EventDispatcher')
          ->addMethodCall('addSubscriber', array(new Reference('listener.router'), null, static::$logger))
          ->addMethodCall('addSubscriber', array(new Reference('listener.response')))
          ->addMethodCall('addListener', array(KernelEvents::EXCEPTION, array($this, 'handleError')));

        $this->setBindings();

        self::$request = &$request;

        PluginManager::triggerPlugins($this->container->get('dispatcher'));

        parent::__construct($this->container->get('dispatcher'), $this->container->get('routing.resolver'));
    }


    /**
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     * @param Request $request A Request instance
     * @param integer $type    The type of the request
     *                          (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param Boolean $catch   Whether to catch exceptions or not
     *
     * @return Response A Response instance
     *
     * @throws \Exception When an Exception occurs during processing
     *
     * @api
     */
    public function handle(\Symfony\Component\HttpFoundation\Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $this->prepareTheme();
        $event = new RequestEvent($request);
        $request = $this->dispatcher->dispatch(Clastic::EVENT_PRE_HANDLE, $event)->getRequest();
        return parent::handle($request, $type, $catch);
    }

    /**
     * Resolve what site we are on.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    protected function resolveSite(Request $request)
    {
        // @todo make this dynamic
        static::$siteId = 1;
        static::$siteDirectory = 'demo';
    }

    /**
     * Load the config from file if a cache exists.
     * If no cache exists, the call will first be loaded from files and append this
     * with the databases config.
     *
     * @void
     */
    protected function loadConfigAndDatabase()
    {
        $cachePath = CLASTIC_ROOT . '/cache/config-' . self::getSiteId() . '.php';
        $configCache = new ConfigCache($cachePath, self::$debug);
        if (!$configCache->isFresh()) {
            self::$config = array_merge(
                self::$config,
                Yaml::parse(CLASTIC_ROOT . '/app/Sites/' . self::$siteDirectory . '/config/config.yml')
            );
            $this->loadDatabase();
            //@todo append the database config.
            $configCache->write(Yaml::dump(self::$config));
        } else {
            self::$config = Yaml::parse($cachePath);
            $this->loadDatabase();
        }
    }

    /**
     * Initialize the database.
     *
     * @void
     */
    protected function loadDatabase()
    {
        if (!isset(self::$config['database'])) {
            return;
        }
        $path = CLASTIC_ROOT . '/cache/doctrine/entities';
        if (true || !is_dir($path)) {
            ModuleManager::collectDatabaseEntities($path);
        }
        $config = Setup::createAnnotationMetadataConfiguration(array($path), self::$debug);
        $config->setEntityNamespaces(ModuleManager::getModuleNamespaces('Entities'));
        $em = EntityManager::create(
            array(
                 'driver'   => self::$config['database']['driver'],
                 'host'     => self::$config['database']['host'],
                 'user'     => self::$config['database']['user'],
                 'password' => self::$config['database']['password'],
                 'dbname'   => self::$config['database']['dbname'],
            ),
            $config
        );
        $this->container->set('entityManager', $em);
    }

    /**
     * @return EntityManager
     */
    public static function &getEntityManager()
    {
        return static::$dependencyInjection->get('entityManager');
    }

    /**
     * Link all bindings to the these can be exposed.
     *
     * @void
     */
    private function setBindings()
    {
        self::$dependencyInjection = &$this->container;
    }

    public static function get($id, $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        return self::$dependencyInjection->get($id, $invalidBehavior);
    }

    /**
     * Gets a reference to the request.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public static function &getRequest()
    {
        return self::$request;
    }

    /**
     * Gets a reference to the template engine.
     *
     * @return \Twig_Environment
     */
    public static function &getTemplateEngine()
    {
        if (is_null(self::$templateEngine)) {
            $paths = array_filter(
                static::getPaths('/Themes/' . static::getTheme()->getName() . '/Resources/templates'),
                function ($path) {
                    return is_dir($path);
                }
            );
            $loader = new Twig_Loader_Filesystem($paths);
            self::$templateEngine = new Twig_Environment($loader, array(
                'cache' => static::$debug ? false : CLASTIC_ROOT . '/cache/templates',
            ), array(
                'debug' => static::$debug,
            ));
            self::$templateEngine->addExtension(new TwigExtension());
        }
        return self::$templateEngine;
    }


    /**
     * Getter for the site's ID.
     *
     * @return int
     */
    public static function getSiteId()
    {
        return static::$siteId;
    }

    /**
     * Getter for the site's directory.
     *
     * @return string
     */
    public static function getSiteDirectory()
    {
        return static::$siteDirectory;
    }

    /**
     * Getter for the site's theme.
     *
     * @return todo
     */
    public static function getTheme()
    {
        return static::$theme;
    }

	  /**
     * Handles the request if something went wrong.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     */
    public function handleError(GetResponseForExceptionEvent $event)
    {
      switch (get_class($event->getException())) {
        case 'Symfony\Component\HttpKernel\Exception\NotFoundHttpException':
          return $event->setResponse(new Response($event->getException()->getMessage(), $event->getException()->getStatusCode()));
      }
      var_dump($event->getException());
    }

    /**
     * Get a list of all the paths, they will be appended with a $suffix.
     *
     * @param string $suffix
     * @return string[]
     */
    public static function getPaths($suffix = '')
    {
        return array(
             CLASTIC_ROOT . '/app/Core' . $suffix,
             CLASTIC_ROOT . '/app/Contrib' . $suffix,
             CLASTIC_ROOT . '/app/Sites/' . Clastic::getSiteDirectory() . $suffix,
        );
    }

    /**
     * Getter for the Assetic bridge package.
     *
     * @return Assets
     */
    public static function &getAssets()
    {
        if (is_null(static::$assets)) {
            static::$assets = new Assets();
        }
        return static::$assets;
    }

    public static function prepareTheme()
    {
        static::$theme = new CleanTheme();
        static::$theme = self::$dependencyInjection->get('dispatcher')->dispatch(static::EVENT_THEME, new ThemeEvent(static::$theme))->getTheme();
    }
}