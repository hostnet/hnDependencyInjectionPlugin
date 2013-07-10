<?php
namespace Hostnet\HnEntitiesPlugin;

/**
 * We need a custom config cache
 * - We want to skip the symfony 1 cache, and only use the Symfony2 cache!
 * - If you use this plugin your config will house in apps/<app>/config/config.yml
 *   Didn't want to leave an empty file. The default \sfConfigCache checks whether it exists..
 */
class ConfigCache extends \sfConfigCache
{
  /**
   * @see sfConfigCache::checkConfig()
   * @todo Do not always write the cache file
   * @return string The cached file that was just written
   */
  public function checkConfig($configPath, $optional = false)
  {
    if($configPath === 'config/databases.yml') {
      $handler = $this->createDatabaseHandler();
      if(!($handler instanceof HnDatabaseConfigHandler)) {
        throw new \RuntimeException('Someone created a handler of incorrect type '.get_class($handler));
      }
      $full_path = \sfConfig::get('sf_root_dir') . '/' . $configPath;
      $data = $handler->execute();

      $cache = $this->getCacheName($configPath);
      $this->writeCacheFile($configPath, $cache, $data);
      return $cache;
    }
    return parent::checkConfig($configPath, $optional);
  }

  /**
   * Override this hook in order to override the config handler if you need to
   * Override ApplicationConfiguration::getConfigCache to be able to override this class
   * @return \Hostnet\HnEntitiesPlugin\HnDatabaseConfigHandler
   */
  protected function createDatabaseHandler()
  {
    if($this->configuration instanceof ApplicationConfiguration) {
      return new HnDatabaseConfigHandler($this->configuration->getContainer());
    }
  }
}