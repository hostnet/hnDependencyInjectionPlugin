<?php
namespace Hostnet\HnDependencyInjectionPlugin;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
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
     * @var bool
     */
    private $router_matched_sf1 = false;

    /**
     * @param Symfony1Kernel $kernel
     */
    public function __construct(Symfony1Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Keeps track of whether symfony1 has been initialized already
     *
     * This can be used to track back on whether to handle the 404 here or in sf1
     *
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if ($event->getController()[0] === $this) {
            $this->router_matched_sf1 = true;
        }
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
        $context       = \sfContext::createInstance($configuration);
        try {
            $context->dispatch();
        } catch(\sfError404Exception $e) {
            // the page was actually not found in sf1
            return;
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
        if (!$event->getException() instanceof NotFoundHttpException) {
            return;
        }

        // Unique case here; If symfony2 has been initialized properly,
        // that means a 404 exception and no sf1 matching route via "sf1",
        // it should not try to go into sf1 because we explicitly threw
        // a 404 exception in our controller (or code).
        if (!$this->router_matched_sf1) {
            return;
        }

        // check if symfony1 doesn't want to handle the 404, need to continue here otherwise
        if (null === ($response = $this->fallbackToSymfony1())) {
            return;
        }

        $event->setResponse($response);
    }
}
