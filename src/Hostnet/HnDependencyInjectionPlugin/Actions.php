<?php
namespace Hostnet\HnDependencyInjectionPlugin;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Subclass of sfActions, adds functions to access the DI controller
 *
 * @author Nico Schoenmaker <nico@hostnet.nl>
 */
class Actions extends \sfActions
{
    /**
     * Get a service form the Dependency Injection container.
     *
     * @param string $id               the service id
     * @param int    $invalid_behavior what to do when a service is not found
     *
     * @throws \DomainException when the app config is not setup correctly
     * @return object
     */
    protected function get($id, $invalid_behavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        $config = $this->getContext()->getConfiguration();

        if ($config instanceof ApplicationConfiguration) {
            return $config->getContainer()->get($id, $invalid_behavior);
        }
        throw new \DomainException(
            'Your app config should extend ApplicationConfiguration'
        );
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
     *
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
            'Your app config should extend ApplicationConfiguration'
        );
    }

    /**
     * Return the object repository for the class.
     *
     * A manager can be specified, if none is given
     * the repository for the default manager will
     * be returned.
     *
     * @param string      $class_name
     * @param string|null $manager_name
     *
     * @throws \InvalidArgumentException when the manager is not available
     * @throws MappingException when the class metadata is not available
     * @throws \LogicException when the doctrine registry is not available
     *
     * @returns ObjectRepository
     */
    protected function getRepository($class_name, $manager_name = null)
    {
        $doctrine = $this->getDoctrine();
        if ($doctrine instanceof Registry) {
            return $doctrine->getManager($manager_name)->getRepository($class_name);
        } else {
            throw new \LogicException('Doctrine Registry not available in Container');
        }
    }
}
