<?php
namespace Hostnet\HnDependencyInjectionPlugin;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Kernel;

/**
 * A nice starting point if you want to build a kernel for a Symfony1
 * application
 *
 * You probably want to override registerBundles() to add your own bundles
 *
 * @author Nico Schoenmaker <nico@hostnet.nl>
 */
class Symfony1Kernel extends Kernel implements CachedKernelInterface
{

    private $configuration;

    private $is_fresh = true;

    /**
     * @param \sfApplicationConfiguration $configuration
     */
    public function __construct(\sfApplicationConfiguration $configuration)
    {
        $environment = \sfConfig::get('sf_environment');
        $debug       = in_array(
            $environment,
            array(
                    'dev',
                    'ontw',
                    'test'
            )
        );
            parent::__construct($environment, $debug);
            $this->configuration = $configuration;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function isFresh()
    {
        $this->boot();
        return $this->is_fresh;
    }

    /**
     * @see \Symfony\Component\HttpKernel\Kernel::getCacheDir()
     */
    public function getCacheDir()
    {
        return \sfConfig::get('sf_config_cache_dir');
    }

    /**
     * @see \Symfony\Component\HttpKernel\Kernel::getLogDir()
     */
    public function getLogDir()
    {
        return \sfConfig::get('sf_log_dir');
    }

    /**
     * The directory where the configuration files are located
     *
     * @return string
     */
    private function getConfigDir()
    {
        return \sfConfig::get('sf_app_config_dir');
    }

    /**
     * If you wish to override this method, please also call
     * parent::registerBundles();
     *
     * @see \Symfony\Component\HttpKernel\KernelInterface::registerBundles()
     */
    public function registerBundles()
    {
        return array(new DoctrineBundle());
    }

    /**
     * Ensures stuff from the apps/<app>/config/ dir can be loaded
     *
     * @see \Symfony\Component\HttpKernel\Kernel::getContainerLoader()
     */
    protected function getContainerLoader(ContainerInterface $container)
    {
        $locator = new FileLocator($this->getConfigDir());

        $resolver = new LoaderResolver();
        $resolver->addLoader(new YamlFileLoader($container, $locator));
        return new DelegatingLoader($resolver);
    }

    /**
     * Loads apps/<app>/config/config_<env>.yml, with a fallback to
     * apps/<app>/config/config.yml
     * Also loads our own services.yml
     *
     * @see \Symfony\Component\HttpKernel\KernelInterface::registerContainerConfiguration()
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/services.yml');
        if ($this->isDebug()) {
            // Also add the debug services
            $loader->load(__DIR__ . '/config/services_debug.yml');
        }

        $path     = $this->getConfigDir();
        $resource = 'config_' . $this->environment . '.yml';
        if (! file_exists($path . '/' . $resource)) {
            $resource = 'config.yml';
        }
        $loader->load($resource);
    }

    /**
     * When this method is called, the cache was invalidated
     *
     * @see \Symfony\Component\HttpKernel\Kernel::buildContainer()
     */
    protected function buildContainer()
    {
        $this->is_fresh = false;
        return parent::buildContainer();
    }
}
