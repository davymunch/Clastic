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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel;
use Symfony\Component\Routing;
use Symfony\Component\EventDispatcher\EventDispatcher;


/**
 * Clastic FrameworkController.
 */
class Clastic extends HttpKernel\HttpKernel
{
	/**
	 * Event name. Dispatched before the request is handled.
	 */
	const EVENT_PRE_HANDLE = 'clastic.pre_handle';

	protected static $config = array();

	/**
	 * Enable debugging.
	 *
	 * @var bool
	 */
	public static $debug = true;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected static $entityManager;

	/**
	 * @var \Symfony\Component\EventDispatcher\EventDispatcher
	 */
	protected static $eventDispatcher;

	/**
	 * @var \Clastic\Bridge\Logger
	 */
	protected static $logger;

	/**
	 * @var \PDO
	 */
	protected static $pdo;

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	protected static $request;

	/**
	 * @var string Directory of the active site.
	 */
	protected static $siteDirectory;

	/**
	 * @var int ID of the active site.
	 */
	protected static $siteId;

	/**
	 * @var \Twig_Environment
	 */
	protected static $templateEngine;

	/**
	 * Active front-end theme
	 *
	 * @var string
	 */
	protected static $theme = 'Default';

	/**
	 * Active admin theme
	 *
	 * @var string
	 */
	protected static $adminTheme = 'Backoffice';

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

		PluginManager::triggerPlugins($dispatcher);

		$this->setBindings();
		self::$request = &$request;

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
  * @param Boolean $catch Whether to catch exceptions or not
  *
  * @return Response A Response instance
  *
  * @throws \Exception When an Exception occurs during processing
  *
  * @api
  */
	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
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

	protected function loadConfigAndDatabase()
	{
		$cachePath = CLASTIC_ROOT . '/cache/config-' . self::getSiteId() . '.php';
		$configCache = new ConfigCache($cachePath, self::$debug);
		if (!$configCache->isFresh()) {
			self::$config = array_merge(self::$config, Yaml::parse(CLASTIC_ROOT . '/Sites/' . self::$siteDirectory . '/config/config.yml'));
			$this->loadDatabase();
			$configCache->write(Yaml::dump(self::$config));
		}
		else {
			self::$config = Yaml::parse($cachePath);
			$this->loadDatabase();
		}
	}

	protected function loadDatabase()
	{
		$path = CLASTIC_ROOT . '/cache/doctrine/yaml';
		if (!is_dir($path)) {
			ModuleManager::collectDatabaseMetadata($path);
		}
		self::$entityManager = EntityManager::create(array(
		    'driver' => self::$config['database']['driver'],
		    'host' => self::$config['database']['host'],
		    'user' => self::$config['database']['user'],
		    'password' => self::$config['database']['password'],
		    'dbname' => self::$config['database']['dbname'],
		), Setup::createYAMLMetadataConfiguration(array($path), self::$debug));
	}

	protected function loadLogger()
	{
		static::$logger = new Logger('watchdog');
		$fileHandler = new StreamHandler(CLASTIC_ROOT . '/logs/watchdog-' . static::getSiteId() . '.log');
		static::$logger->pushHandler($fileHandler);
	}

	/**
	 * Link all bindings to the these can be exposed.
	 */
	private function setBindings()
	{
		self::$eventDispatcher = &$this->dispatcher;
	}

	/**
	 * @return \Symfony\Component\EventDispatcher\EventDispatcher
	 */
	public static function &getDispatcher()
	{
		return self::$eventDispatcher;
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Request
	 */
	public static function &getRequest()
	{
		return self::$request;
	}

	/**
	 * @return \Twig_Environment
	 */
	public static function &getTemplateEngine()
	{
		if (is_null(self::$templateEngine)) {
			$loader = new Twig_Loader_Filesystem(array());
			self::$templateEngine = new Twig_Environment($loader, array(
				'cache' => CLASTIC_ROOT . '/cache/templates',
			), array(
				'debug' => false,
	    ));
		}
		return self::$templateEngine;
	}

	public static function getSiteId()
	{
		return static::$siteId;
	}

	public static function getSiteDirectory()
	{
		return static::$siteDirectory;
	}

	public static function getTheme()
	{
		return static::$theme;
	}

	public static function getAdminTheme()
	{
		return static::$adminTheme;
	}

}