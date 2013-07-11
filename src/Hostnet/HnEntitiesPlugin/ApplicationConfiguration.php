<?php

namespace Hostnet\HnEntitiesPlugin;

use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

use Symfony\Component\Config\ConfigCache as Symfony2ConfigCache;

use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

use Symfony\Component\Config\Loader\LoaderResolver;

use Symfony\Component\Config\FileLocator;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\DoctrineExtension;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Use this class as your superclass for your ApplicationConfiguration
 *
 * Adds a ->getContainer() function
 */
class ApplicationConfiguration extends \sfApplicationConfiguration
{
  private $container;

  public function getConfigCache()
  {
    if(null === $this->configCache) {
      // Isn't this cyclic dependency lovely?
      $this->configCache = new ConfigCache($this);
    }
    return $this->configCache;
  }

  /**
   * Gets and possibly generates a container.
   * @return ContainerBuilder
   */
  public final function getContainer()
  {
    if(! $this->container) {
      $file = \sfConfig::get('sf_config_cache_dir') . '/container_dump.php';
      $debug = in_array(\sfConfig::get('sf_environment'), array('dev', 'ontw'));

      $container_config_cache = new Symfony2ConfigCache($file, $debug);
      if (!$container_config_cache->isFresh()) {
        $container = $this->createNewContainer($debug);
        $dumper = new PhpDumper($container);
        $container_config_cache->write(
            $dumper->dump(array('class' => 'MyCachedContainer')),
            $container->getResources()
        );
      }
      require_once $file;
      $this->container = new \MyCachedContainer();
    }
    return $this->container;
  }

  private function createNewContainer($debug)
  {
    $container = new ContainerBuilder();
    $container->registerExtension(new DoctrineExtension());
    $this->addResourcesToContainer($container);

    $container->setParameter('kernel.debug', $debug);
    $path = \sfConfig::get('sf_config_dir');
    $locator = new FileLocator($path);

    $resolver = new LoaderResolver();
    $resolver->addLoader(new YamlFileLoader($container, $locator));

    $resource = 'config-' . \sfConfig::get('sf_environment') . '.yml';
    if(! file_exists($path . '/' . $resource)) {
      $resource = 'config.yml';
    }
    $resolver->resolve($resource)->load($resource);

    $container->compile();
    return $container;
  }

  /**
   * Hook a subclass can use to add custom extensions, or more resources, to the container
   * @param ContainerBuilder $container
   */
  protected function addResourcesToContainer(ContainerBuilder $container)
  {
  }
}