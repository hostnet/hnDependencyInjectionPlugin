<?php
namespace Hostnet\HnDependencyInjectionPlugin;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Hostnet\HnDependencyInjectionPlugin\ApplicationConfiguration
 */
class ApplicationConfigurationTest extends TestCase
{
    public function testGetConfigCache(): void
    {
        $config = new ApplicationConfiguration('test', true);
        $cache  = $config->getConfigCache();
        self::assertInstanceOf('Hostnet\HnDependencyInjectionPlugin\ConfigCache', $cache);

      // And only one is made
        self::assertSame($cache, $config->getConfigCache());
    }
}
