<?php
namespace Hostnet\HnDependencyInjectionPlugin;

/**
 * Facade for Symfony1 sfContext to provide mocking
 *
 * Class Symfony1Context
 * @package Hostnet\HnDependencyInjectionPlugin
 */
class Symfony1Context
{
    /**
     * @return \sfWebResponse
     */
    public function getResponse()
    {
        return \sfContext::getInstance()->getResponse();
    }

    /**
     * Returns true if sfContext has been created
     * @return bool
     */
    public function isInitialized()
    {
        return \sfContext::hasInstance();
    }
}
