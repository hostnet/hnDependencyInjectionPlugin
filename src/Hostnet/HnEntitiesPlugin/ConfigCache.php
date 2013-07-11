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
   * Constructor overridden to enforce stricter typing
   * @param ApplicationConfiguration $configuration
   */
  public function __construct(ApplicationConfiguration $configuration)
  {
    parent::__construct($configuration);
  }

  /**
   * @see sfConfigCache::checkConfig()
   * @return string The cached file that was just written
   */
  public function checkConfig($config_path, $optional = false)
  {
    if($config_path === 'config/databases.yml') {
      if(!$this->configuration->isFresh()) {
        $this->writeDatabaseCache($config_path);
      }
      return $this->getCacheName($config_path);
    }
    return parent::checkConfig($config_path, $optional);
  }

  private function writeDatabaseCache($config_path)
  {
    $handler = $this->createDatabaseHandler();
    if(!($handler instanceof HnDatabaseConfigHandler)) {
      throw new \RuntimeException('Someone created a handler of incorrect type '.get_class($handler));
    }
    $full_path = \sfConfig::get('sf_root_dir') . '/' . $config_path;
    $data = $handler->execute();

    $cache = $this->getCacheName($config_path);
    $this->writeCacheFile($config_path, $cache, $data);
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