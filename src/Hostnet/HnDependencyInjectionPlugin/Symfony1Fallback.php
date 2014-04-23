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

        $code = 0;
        $response = new Response();

        if ($context->getResponse() instanceof \sfWebResponse) {
            $web_response = $context->getResponse();
            /* @var $web_response \sfWebResponse */
            $code = $web_response->getStatusCode();

            // If we're trying to redirect to another location, set the statuscode and header in the symfony2 response
            // properly, because this response is overwriting the sf1 response if the content of the body is less than
            // 4kb large due to output buffering.
            if (($code === 302 || $code === 304)) {
                $response->setStatusCode($code, Response::$statusTexts[$code]);
                $response->headers->set('Location', $web_response->getHttpHeader('Location'));
            }
        }

        // Symfony1 will usually send headers for us
        //Check if found response code is a known SF2 response code
        if (!isset(Response::$statusTexts[$code])) {
            // But for some reason it appears not to be, lets keep Symfony2
            // busy with an empty response :p
            // For some ajax requests it doesn't, dunno why, but thats why the
            // 200 status code.
            $code = 200;
        }

        $response->headers->set('X-Status-Code', $code);
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
