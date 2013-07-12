<?php
namespace Hostnet\HnEntitiesPlugin;

use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Represents a cached kernel
 */
interface CachedKernelInterface extends KernelInterface
{
  /**
   * @return bool Whether the cache is still fresh
   */
  public function isFresh();
}