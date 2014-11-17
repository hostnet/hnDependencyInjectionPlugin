<?php
namespace Hostnet\HnDependencyInjectionPlugin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
    private $fallback_on_404 = true;

    /**
     * @param Symfony1Kernel $kernel
     */
    public function __construct(Symfony1Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * When a controller has been called, it shouldn't
     * fall back to Symfony1 on kernel exceptions
     *
     * This makes it only possible for an unmatched route
     * to trigger the kernel.exception
     *
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $this->fallback_on_404 = false;
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
        // it should not try to go into sf1 because we explicitly threw
        // a 404 exception in our controller (or code). Initialization
        // means that it didn't match a "sf1" route and didn't 404
        if (false === $this->fallback_on_404) {
            return;
        }

        try {
            $response = $this->fallbackToSymfony1();
        } catch (NotFoundHttpException $e) {
            // in case sf1 can't allocate the route, it gets wrapped in this $e
            return;
        }

        $event->setResponse($response);
    }

    /**
     * This method serves as action and is called from kernel.exception if the
     * route was not found in the first controller.
     *
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
            // The page was actually not found in sf1, to trigger this case,
            // change sf1 to use a different front controller that doesn't
            // catch this exception. This will prevent the 404 forward in sf1
            throw new NotFoundHttpException('Unable to allocate route in symfony1 fallback', $e);
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
        // Check if found response code is a known SF2 response code
        if (!isset(Response::$statusTexts[$code])) {
            // Lets keep sf2 busy with an empty response. For some ajax
            // requests it doesn't give a valid code, but thats why the
            // 200 status code.
            $code = 200;
        }

        $response->headers->set('X-Status-Code', $code);
        return $response;
    }
}
