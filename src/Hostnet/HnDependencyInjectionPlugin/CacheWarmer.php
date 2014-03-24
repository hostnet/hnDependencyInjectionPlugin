<?php
namespace Hostnet\HnDependencyInjectionPlugin;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Makes sure that the sfContext is initialized when the cache is being warmed.
 *
 * This will trigger the generation of the Symfony 1 cache files, which
 * - Will make the first request faster (yay!)
 * - Will generate the config_databases.yml.php in the cache.
 *
 * The second thing is very important, since the config_databases.yml.php is only created
 * when the container is rebuilt. Which, in prod mode, does not happen.
 */
class CacheWarmer implements CacheWarmerInterface
{
    /**
     * @var \sfApplicationConfiguration
     */
    private $configuration;

    public function __construct(KernelInterface $kernel)
    {
        if ($kernel instanceof Symfony1Kernel) {
            $this->configuration = $kernel->getConfiguration();
        }
    }

    /**
     * @see \Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface::isOptional()
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * @see \Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface::warmUp()
     */
    public function warmUp($cacheDir)
    {
        if ($this->configuration && ! \sfContext::hasInstance()) {
            \sfContext::createInstance($this->configuration);
        }
    }
}