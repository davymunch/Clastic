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
use Clastic\Asset\Assets;
use Assetic\Factory\AssetFactory;
use Assetic\Extension\Twig\AsseticExtension;
use Clastic\Event\ThemeEvent;
use Symfony\Component\EventDispatcher\Event;
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

    /**
     * Hold doctrine's entityManager.
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected static $entityManager;

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

    /**
     * @var \Assetic\AssetManager
     */
    public static $assets;

    /**
     * Constructor
     *
     * @param EventDispatcherInterface    $dispatcher An EventDispatcherInterface instance
     * @param ControllerResolverInterface $resolver   A ControllerResolverInterface instance
     *
     * @api
     */
    public function __construct(Request &$request)
    {
        $this->resolveSite($request);
        $this->loadConfigAndDatabase();
        $this->loadLogger();

        $context = new Routing\RequestContext();
        $matcher = new UrlMatcher(ModuleManager::createModuleRoutes(), $context);
        $resolver = new HttpKernel\Controller\ControllerResolver(static::$logger);

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new HttpKernel\EventListener\RouterListener($matcher, null, static::$logger));
        $dispatcher->addSubscriber(new HttpKernel\EventListener\ResponseListener('UTF-8'));

        $dispatcher->addListener(KernelEvents::EXCEPTION, array($this, 'handleError'));

        PluginManager::triggerPlugins($dispatcher);

        $this->setBindings();
        self::$request = &$request;

        static::$assets = new Assets();

        parent::__construct($dispatcher, $resolver);
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
        $path = CLASTIC_ROOT . '/cache/doctrine/yaml';
        if (!is_dir($path)) {
            ModuleManager::collectDatabaseMetadata($path);
        }
        self::$entityManager = EntityManager::create(
            array(
                 'driver'   => self::$config['database']['driver'],
                 'host'     => self::$config['database']['host'],
                 'user'     => self::$config['database']['user'],
                 'password' => self::$config['database']['password'],
                 'dbname'   => self::$config['database']['dbname'],
            ),
            Setup::createYAMLMetadataConfiguration(array($path), self::$debug)
        );
    }

    /**
     * Instantiate the logger.
     *
     * @void
     */
    protected function loadLogger()
    {
        static::$logger = new Logger('watchdog');
        $fileHandler = new StreamHandler(CLASTIC_ROOT . '/logs/watchdog-' . static::getSiteId() . '.log');
        static::$logger->pushHandler($fileHandler);
    }

    /**
     * Link all bindings to the these can be exposed.
     *
     * @void
     */
    private function setBindings()
    {
        self::$eventDispatcher = &$this->dispatcher;
    }

    /**
     * Gets a reference to the eventDispatcher.
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcher
     */
    public static function &getDispatcher()
    {
        return self::$eventDispatcher;
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
                static::getPaths('/Themes/' . static::getTheme() . '/templates'),
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
     * @return string
     */
    public static function getTheme()
    {
        if (is_null(static::$theme)) {
            // @todo resolve theme from db of config.
            static::$theme = 'DemoTheme';
            $event = new ThemeEvent(static::$theme);
            static::$theme = self::getDispatcher()->dispatch(static::EVENT_THEME, $event)->getTheme();
        }
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

    public static function getPaths($suffix = '')
    {
        return array(
             CLASTIC_ROOT . '/app/Core' . $suffix,
             CLASTIC_ROOT . '/app/Contrib' . $suffix,
             CLASTIC_ROOT . '/app/Sites/' . Clastic::getSiteDirectory() . $suffix,
        );
    }
}