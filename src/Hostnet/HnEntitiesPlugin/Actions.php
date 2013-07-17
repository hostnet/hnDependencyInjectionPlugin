<?php
namespace Hostnet\HnEntitiesPlugin;

use Hostnet\HnEntitiesPlugin\ApplicationConfiguration;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Actions extends \sfActions
{
  protected function get($id, $invalid_behavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
  {
    $config = $this->getContext()->getConfiguration();

    if($config instanceof ApplicationConfiguration) {
      return $config->getContainer()->get($id, $invalid_behavior);
    }
    throw new \DomainException('Your app config should extend ApplicationConfiguration');
  }
}