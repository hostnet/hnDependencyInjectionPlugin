<?php
namespace Hostnet\HnDependencyInjectionPlugin;

/**
 * Reads database configuration from the Symfony2 doctrine configuration
 * Symfony 2 config is located in apps/<app>/config/config.yml
 *
 * You can remove databases.yml when using this plugin
 *
 * @author Nico Schoenmaker <nico@hostnet.nl>
 */
class ConfigCache extends \sfConfigCache
{

    // @codingStandardsIgnoreStart
    /**
     * Constructor overridden to enforce stricter typing
     *
     * @param ApplicationConfiguration $configuration
     */
    public function __construct(ApplicationConfiguration $configuration)
    {
        parent::__construct($configuration);
    }
    // @codingStandardsIgnoreEnd

    /**
     * @see sfConfigCache::checkConfig()
     * @return string The cached file that was just written
     */
    public function checkConfig($config_path, $optional = false)
    {
        if ($config_path === 'config/databases.yml') {
            if (! $this->configuration->isFresh()) {
                $this->writeDatabaseCache($config_path);
            }
            return $this->getCacheName($config_path);
        }
        return parent::checkConfig($config_path, $optional);
    }

    private function writeDatabaseCache($config_path)
    {
        $handler = $this->createDatabaseHandler();
        if (! ($handler instanceof HnDatabaseConfigHandler)) {
            throw new \RuntimeException(
                'Someone created a handler of incorrect type ' . get_class($handler)
            );
        }
        $full_path = \sfConfig::get('sf_root_dir') . '/' . $config_path;
        $data      = $handler->execute();

        $cache = $this->getCacheName($config_path);
        $this->writeCacheFile($config_path, $cache, $data);
    }

    /**
     * Creates the database config handler
     * Override this hook if you prefer a different one :)
     *
     * @return \Hostnet\HnDependencyInjectionPlugin\HnDatabaseConfigHandler
     */
    protected function createDatabaseHandler()
    {
        if ($this->configuration instanceof ApplicationConfiguration) {
            return new HnDatabaseConfigHandler($this->configuration->getContainer());
        }
    }
}
