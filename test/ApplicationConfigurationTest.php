<?php
use Hostnet\HnEntitiesPlugin\ApplicationConfiguration;

class ApplicationConfigurationTest extends PHPUnit_Framework_TestCase
{
  public function testGetConfigCache()
  {
    $config = new ApplicationConfiguration('test', true);
    $cache = $config->getConfigCache();
    $this->assertInstanceOf('Hostnet\HnEntitiesPlugin\ConfigCache', $cache);

    // And only one is made
    $this->assertTrue($cache === $config->getConfigCache());
  }
}