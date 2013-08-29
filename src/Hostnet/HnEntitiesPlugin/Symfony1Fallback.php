<?php
namespace Hostnet\HnEntitiesPlugin;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 * Does a fallback to Symfony 1 if there was no route found in Symfony 2
 * @author Nico Schoenmaker <nico@hostnet.nl>
 */
class Symfony1Fallback
{
  private $kernel;

  /**
   * @param Symfony1Kernel $kernel
   */
  public function __construct(Symfony1Kernel $kernel)
  {
    $this->kernel = $kernel;
  }

  /**
   * Fires on the kernel.exception event
   * @param GetResponseForExceptionEvent $event
   */
  public function onKernelException(GetResponseForExceptionEvent $event)
  {
    if($event->getException() instanceof NotFoundHttpException) {
      $configuration = $this->kernel->getConfiguration();
      \sfContext::createInstance($configuration)->dispatch();

      // Symfony1 will usually send headers for us, lets keep Symfony2 busy with an empty response :p
      // For some ajax requests it doesn't, dunno why, but thats why the 200 status code.
      $response = new Response();
      $response->headers->set('X-Status-Code', 200);
      $event->setResponse($response);
    }
  }
}