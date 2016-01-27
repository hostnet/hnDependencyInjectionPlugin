<?php
namespace Hostnet\HnDependencyInjectionPlugin;

class ConfigCacheTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \sfConfig::set('sf_root_dir', '/meh/the_root');
        \sfConfig::set('sf_config_cache_dir', '/meh/the_root/cache');
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
            '/meh/the_root/cache/config_databases.yml.php',
            $configuration->checkConfig('config/databases.yml')
        );

        $expected = array('config/databases.yml', '/meh/the_root/cache/config_databases.yml.php', 'muhaha');
        $this->assertEquals($expected, $configuration->args);
    }
}
