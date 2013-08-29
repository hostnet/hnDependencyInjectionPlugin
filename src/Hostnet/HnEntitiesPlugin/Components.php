<?php
namespace Hostnet\HnEntitiesPlugin;

use Hostnet\HnEntitiesPlugin\ApplicationConfiguration;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Subclass of sfComponents, adds functions to access the DI controller
 *
 * @author Rick Prent <rprent@hostnet.nl>
 */
class Components extends \sfComponents
{

    /**
     * Access a service of the container
     *
     * @param string $id
     *            The service name
     * @param int $invalid_behavior
     * @throws \DomainException If the application config doesn't extend the
     *         proper class
     * @return object
     */
    protected function get($id, $invalid_behavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        $config = $this->getContext()->getConfiguration();

        if ($config instanceof ApplicationConfiguration) {
            return $config->getContainer()->get($id, $invalid_behavior);
        }
        throw new \DomainException(
            'Your app config should extend ApplicationConfiguration');
    }
}
