<?php
namespace Hostnet\HnDependencyInjectionPlugin;

class TestConfigCache extends ConfigCache
{
    public $handler;

    public $args;

    protected function createDatabaseHandler()
    {
        return $this->handler;
    }

    public function writeCacheFile($config, $cache, $data)
    {
        if (is_array($this->args)) {
            throw new Exception('Only once please');
        }
        $this->args = func_get_args();
    }
}
