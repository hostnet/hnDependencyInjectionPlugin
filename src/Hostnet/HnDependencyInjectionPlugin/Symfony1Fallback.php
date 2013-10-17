<?php
namespace Hostnet\HnDependencyInjectionPlugin;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 * Does a fallback to Symfony 1 if there was no route found in Symfony 2
 *
 * @author Nico Schoenmaker <nico@hostnet.nl>
 */
class Symfony1Fallback
{
    /**
     * @var Symfony1Kernel
     */
    private $kernel;

    /**
     * @param Symfony1Kernel $kernel
     */
    public function __construct(Symfony1Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * To be able to create routes to Symfony 1 from Symfony 2,
     * you can create a Symfony 2 routing rule like this:
     * sf1:
     *     path: /{module}/{action}/{params}
     *     defaults: { action: index, params: '', _controller: "kernel.listener.symfony1_fallback:fallbackToSymfony1" }
     *     requirements:
     *         params: "[a-zA-Z0-9_\/]+"
     * @return Response
     */
    public function fallbackToSymfony1()
    {
        $configuration = $this->kernel->getConfiguration();
        $context = \sfContext::createInstance($configuration);
        try {
            $context->dispatch();
        } catch(\sfStopException $e) {
        }

        // Symfony1 will usually send headers for us, lets keep Symfony2
        // busy with an empty response :p
        // For some ajax requests it doesn't, dunno why, but thats why the
        // 200 status code.
        $response = new Response();
        $response->headers->set('X-Status-Code', 200);
        return $response;
    }

    /**
     * Fires on the kernel.exception event
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($event->getException() instanceof NotFoundHttpException) {
            $response = $this->fallbackToSymfony1();
            $event->setResponse($response);
        }
    }
}
