<?php
namespace Hostnet\HnDependencyInjectionPlugin;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Hostnet\HnDependencyInjectionPlugin\ApplicationConfiguration
 */
class ApplicationConfigurationTest extends TestCase
{
    public function testGetConfigCache()
    {
        $config = new ApplicationConfiguration('test', true);
        $cache  = $config->getConfigCache();
        $this->assertInstanceOf('Hostnet\HnDependencyInjectionPlugin\ConfigCache', $cache);

      // And only one is made
        $this->assertTrue($cache === $config->getConfigCache());
    }
}
