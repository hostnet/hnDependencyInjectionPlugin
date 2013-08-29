<?php
namespace Hostnet\HnEntitiesPlugin;

use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Represents a cached kernel
 *
 * @author Nico Schoenmaker <nico@hostnet.nl>
 */
interface CachedKernelInterface extends KernelInterface
{

    /**
     * @return bool Whether the cache is still fresh
     */
    public function isFresh();
}