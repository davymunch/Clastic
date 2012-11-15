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
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;

/**
 * This manager handles everything that involves more than one moduleController.
 */
class ModuleManager
{
	/**
	 * Holds the routeCollection for all modules.
	 *
	 * @var \Symfony\Component\Routing\RouteCollection
	 */
	protected static $routes;

	/**
	 * Get the routeCollection from the cache.
	 * If the cache is non existing, it will try all modules.
	 *
	 * @return \Symfony\Component\Routing\RouteCollection
	 */
	public static function createModuleRoutes()
	{
		if (!is_null(static::$routes)) {
			return static::$routes;
		}
		$cachePath = CLASTIC_ROOT . '/cache/routes-' . Clastic::getSiteId() . '.php';
		$routesCache = new ConfigCache($cachePath, Clastic::$debug);
		if (!$routesCache->isFresh()) {
			static::$routes = new RouteCollection();
			$finder = new Finder();
			$iterator = $finder
				->directories()
				->depth(0)
				->in(static::getModulePaths());
			$tmpRoutes = array();
			foreach ($iterator as $module) {
				$tmpRoutes[$module->getRelativePathname()]['path'] = $module->getRealPath();
				if (file_exists($module->getRealPath() . '/conf/routes.yml')) {
					$configRoutes = Yaml::parse($module->getRealPath() . '/conf/routes.yml');
					foreach ((array)$configRoutes as $name => $route) {
						$tmpRoutes[$module->getRelativePathname()]['routes'][$name] = $route;
					}
				}
			}
			static::$routes = new RouteCollection();
			foreach ($tmpRoutes as $module => $routes) {
				$routeCollection = new RouteCollection();
				if (isset($routes['routes'])) {
					foreach ($routes['routes'] as $name => $route) {
						$params = $route;
						$controller = str_replace(array(CLASTIC_ROOT, '/'), array('', '\\'), $routes['path']) . '\\' . $module . 'Controller';
						$params['_controller'] = $controller . '::' . $route['_method'];
						unset($params['_pattern'], $params['_method']);
						$routeCollection->add($name, new Route($route['_pattern'], $params));
					}
					static::$routes->addCollection($routeCollection);
				}
			}
			$routesCache->write(serialize(static::$routes));
		}
		else {
			static::$routes = unserialize(Yaml::parse($cachePath));
		}
		return static::$routes;
	}

	/**
	 * Collect all database metadata from all modules and store them in one folder.
	 *
	 * @todo implement this
	 * @param $path string
	 */
	public static function collectDatabaseMetadata($path)
	{
		if (is_dir($path) || mkdir($path, 0777, true)) {
			// @todo collect module tables.
		}
	}

	/**
	 * Get all directories where modules are stored.
	 *
	 * @return string[]
	 */
	public static function getModulePaths()
	{
		return array_filter(array(
			CLASTIC_ROOT . '/Core/Modules',
			CLASTIC_ROOT . '/Contrib/Modules',
			CLASTIC_ROOT . '/Sites/' . Clastic::getSiteDirectory(). '/Modules',
		), function($directory) {
			return is_dir($directory);
		});
	}
}