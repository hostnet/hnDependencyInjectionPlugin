<?php
namespace Hostnet\HnDependencyInjectionPlugin;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Hostnet\HnDependencyInjectionPlugin\ConfigCache
 */
class ConfigCacheTest extends TestCase
{
    public function setUp()
    {
        \sfConfig::set('sf_root_dir', '../cache/meh/the_root');
        \sfConfig::set('sf_config_cache_dir', '../cache/meh/the_root/cache');
    }

    public function testCheckConfig()
    {
        $app_config = $this->getMockBuilder('Hostnet\HnDependencyInjectionPlugin\ApplicationConfiguration')
            ->disableOriginalConstructor()
            ->getMock();

        $handler = $this->getMockBuilder('Hostnet\HnDependencyInjectionPlugin\HnDatabaseConfigHandler')
            ->disableOriginalConstructor()
            ->getMock();

        $handler->expects($this->once())
                ->method('execute')
                ->with()
                ->will($this->returnValue('muhaha'));

        $configuration          = new TestConfigCache($app_config);
        $configuration->handler = $handler;

        $this->assertEquals(
            '../cache/meh/the_root/cache/config_databases.yml.php',
            $configuration->checkConfig('config/databases.yml')
        );

        $expected = array('config/databases.yml', '../cache/meh/the_root/cache/config_databases.yml.php', 'muhaha');
        $this->assertEquals($expected, $configuration->args);
    }
}
