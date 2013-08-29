<?php
namespace Hostnet\HnDependencyInjectionPlugin;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
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
 *
 * @author Nico Schoenmaker <nico@hostnet.nl>
 */
class ApplicationConfiguration extends \sfApplicationConfiguration
{

    private $kernel;

    /**
     * @return CachedKernelInterface
     */
    protected function createKernel()
    {
        return new Symfony1Kernel($this);
    }

    /**
     * @see sfApplicationConfiguration::getConfigCache()
     */
    public function getConfigCache()
    {
        if (null === $this->configCache) {
            // Isn't this cyclic dependency lovely?
            $this->configCache = new ConfigCache($this);
        }
        return $this->configCache;
    }

    /**
     * @see HttpKernelInterface::handle();
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(
        Request $request,
        $type = HttpKernelInterface::MASTER_REQUEST,
        $catch = true)
    {
        return $this->getKernel()->handle($request, $type, $catch);
    }

    /**
     * Get the current kernel
     *
     * @throws \RuntimeException
     * @return \Hostnet\HnDependencyInjectionPlugin\CachedKernelInterface
     */
    private function getKernel()
    {
        if (! $this->kernel) {
            $this->kernel = $this->createKernel();
            if (! $this->kernel instanceof CachedKernelInterface) {
                throw new \RuntimeException(
                    sprintf(
                        'The kernel that was built should have been of CachedKernelInterface, got %s',
                        get_class($this->kernel)));
            }
        }
        return $this->kernel;
    }

    /**
     * Whether the existing container cache was fresh.
     * Not fresh config has potentially changed, and should be re-read
     *
     * @return boolean
     */
    public function isFresh()
    {
        return $this->getKernel()->isFresh();
    }

    /**
     * Gets and possibly generates a container.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->getKernel()->getContainer();
    }
}