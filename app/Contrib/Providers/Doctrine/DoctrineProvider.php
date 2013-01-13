<?php
/**
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Contrib\Providers\Doctrine;

use Clastic\Provider\ProviderInterface;
use Clastic\Clastic;
use Clastic\Module\ModuleManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DoctrineProvider implements ProviderInterface
{
    protected $entityManager;

    public static function register(ContainerInterface &$container)
    {
        $container->set('doctrine', new self());
    }

    public function __construct()
    {
        $path = CLASTIC_ROOT . '/cache/doctrine/entities';
        if (true || !is_dir($path)) {
            ModuleManager::collectDatabaseEntities($path);
        }
        $config = Setup::createAnnotationMetadataConfiguration(array($path), Clastic::$debug);
        $config->setEntityNamespaces(ModuleManager::getModuleNamespaces('Entities'));
        $this->entityManager = EntityManager::create(
            array(
                'driver'   => Clastic::$config['database']['driver'],
                'host'     => Clastic::$config['database']['host'],
                'user'     => Clastic::$config['database']['user'],
                'password' => Clastic::$config['database']['password'],
                'dbname'   => Clastic::$config['database']['dbname'],
            ),
            $config
        );
    }

    public function getEntityManager()
    {
        return $this->entityManager;
    }


}