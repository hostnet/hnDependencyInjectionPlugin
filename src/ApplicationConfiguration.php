<?php
namespace Hostnet\HnDependencyInjectionPlugin;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

/**
 * Use this class as your superclass for your ApplicationConfiguration
 *
 * Adds a ->getContainer() function
 *
 * @author Nico Schoenmaker <nico@hostnet.nl>
 */
class ApplicationConfiguration extends \sfApplicationConfiguration implements TerminableInterface
{

    /**
     * @var \Hostnet\HnDependencyInjectionPlugin\CachedKernelInterface
     */
    private $kernel;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Console\Application
     */
    private $cli_application;

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
        $catch = true
    ) {
    
        return $this->getKernel()->handle($request, $type, $catch);
    }

    public function getCLIApplication()
    {
        if (! $this->cli_application) {
            $this->cli_application = new Application($this->getKernel());
        }
        return $this->cli_application;
    }

    public function terminate(
        Request $request,
        Response $response
    ) {
    
        return $this->getKernel()->terminate($request, $response);
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
                        get_class($this->kernel)
                    )
                );
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
