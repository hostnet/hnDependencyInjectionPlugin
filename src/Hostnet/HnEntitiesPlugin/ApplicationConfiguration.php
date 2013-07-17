<?php

namespace Hostnet\HnEntitiesPlugin;

use Symfony\Component\DependencyInjection\ContainerInterface;

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
  private $kernel;

  /**
   * @return CachedKernelInterface
   */
  protected function createKernel()
  {
    return new Symfony1Kernel();
  }

  public function getConfigCache()
  {
    if(null === $this->configCache) {
      // Isn't this cyclic dependency lovely?
      $this->configCache = new ConfigCache($this);
    }
    return $this->configCache;
  }

  /**
   * Get the current kernel
   * @throws \RuntimeException
   * @return \Hostnet\HnEntitiesPlugin\CachedKernelInterface
   */
  private function getKernel()
  {
    if(!$this->kernel) {
      $this->kernel = $this->createKernel();
      if(!$this->kernel instanceof CachedKernelInterface) {
        throw new \RuntimeException(sprintf(
            'The kernel that was built should have been of CachedKernelInterface, got %s',
            get_class($this->kernel)));
      }
    }
    return $this->kernel;
  }

  /**
   * Whether the existing container cache was fresh.
   * Not fresh config has potentially changed, and should be re-read
   * @return boolean
   */
  public function isFresh()
  {
    return $this->getKernel()->isFresh();
  }

  /**
   * Gets and possibly generates a container.
   * @return ContainerInterface
   */
  public function getContainer()
  {
    return $this->getKernel()->getContainer();
  }
}