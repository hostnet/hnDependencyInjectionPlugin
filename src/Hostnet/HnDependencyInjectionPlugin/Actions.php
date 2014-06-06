<?php
namespace Hostnet\HnDependencyInjectionPlugin;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Hostnet\HnDependencyInjectionPlugin\ApplicationConfiguration;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Subclass of sfActions, adds functions to access the DI controller
 *
 * @author Nico Schoenmaker <nico@hostnet.nl>
 */
class Actions extends \sfActions
{
    protected function get($id, $invalid_behavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        $config = $this->getContext()->getConfiguration();

        if ($config instanceof ApplicationConfiguration) {
            return $config->getContainer()->get($id, $invalid_behavior);
        }
        throw new \DomainException(
            'Your app config should extend ApplicationConfiguration');
    }

    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @return Registry
     *
     * @throws \LogicException If DoctrineBundle is not available
     */
    protected function getDoctrine()
    {
        return $this->get('doctrine');
    }

    /**
     * Get a parameter from the service container
     * @throws \DomainException if the configuration is no ApplicationConfiguration
     * @throws \InvalidArgumentException if the parameter is not found
     */
    protected function getParameter($name)
    {
        $config = $this->getContext()->getConfiguration();

        if ($config instanceof ApplicationConfiguration) {
            return $config->getContainer()->getParameter($name);
        }
        throw new \DomainException(
            'Your app config should extend ApplicationConfiguration');
    }
}
