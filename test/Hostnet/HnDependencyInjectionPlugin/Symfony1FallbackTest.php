<?php

namespace Hostnet\HnDependencyInjectionPlugin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * @covers Hostnet\HnDependencyInjectionPlugin\Symfony1Fallback
 * @author Iltar van der Berg <ivanderberg@hostnet.nl>
 */
class Symfony1FallbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Symfony1Fallback
     */
    private $fallback;

    public function setUp()
    {
        $sf1_kernel = $this
            ->getMockBuilder('Hostnet\HnDependencyInjectionPlugin\Symfony1Kernel')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fallback = $this
            ->getMockBuilder('Hostnet\HnDependencyInjectionPlugin\Symfony1Fallback')
            ->setConstructorArgs([$sf1_kernel])
            ->setMethods(['fallbackToSymfony1'])
            ->getMock();
    }

    public function testOnKernelExceptionWrongException()
    {
        $this->fallback
            ->expects($this->never())
            ->method('fallbackToSymfony1');

        $event = $this->buildResponseEvent(new \Exception('henk'));
        $this->fallback->onKernelException($event);

        $this->assertFalse($event->isPropagationStopped());
    }

    public function testOnKernelExceptionNeverInit()
    {
        $this->fallback
            ->expects($this->once())
            ->method('fallbackToSymfony1');

        $event = $this->buildResponseEvent(new NotFoundHttpException('henk'));
        $this->fallback->onKernelException($event);

        $this->assertFalse($event->isPropagationStopped());
    }

    public function testOnKernelExceptionSf2Init()
    {
        $this->fallback
            ->expects($this->never())
            ->method('fallbackToSymfony1');

        $event1 = $this->buildControllerEvent(array(clone $this->fallback, 'fallbackToSymfony1'));
        $this->fallback->onKernelController($event1);

        $event2 = $this->buildControllerEvent(array($this->fallback, 'fallbackToSymfony1'));
        $this->fallback->onKernelController($event2);

        $event3 = $this->buildResponseEvent(new NotFoundHttpException('henk'));
        $this->fallback->onKernelException($event3);

        $this->assertFalse($event3->isPropagationStopped());
    }
    public function testOnKernelExceptionSf1InitResponse()
    {
        $this->fallback
            ->expects($this->once())
            ->method('fallbackToSymfony1')
            ->willReturn(new Response());

        $event2 = $this->buildResponseEvent(new NotFoundHttpException('henk'));
        $this->fallback->onKernelException($event2);

        $this->assertTrue($event2->isPropagationStopped());
    }

    public function testOnKernelExceptionControllerSf1Init()
    {
        $this->fallback
            ->expects($this->never())
            ->method('fallbackToSymfony1');

        $event1 = $this->buildControllerEvent(function () {});
        $this->fallback->onKernelController($event1);

        $event2 = $this->buildResponseEvent(new NotFoundHttpException('henk'));
        $this->fallback->onKernelException($event2);

        $this->assertFalse($event2->isPropagationStopped());
    }

    public function testOnKernelExceptionController()
    {
        $this->fallback
            ->expects($this->never())
            ->method('fallbackToSymfony1');

        $event1 = $this->buildControllerEvent(array($this->fallback, 'fallbackToSymfony1'));
        $this->fallback->onKernelController($event1);

        $event2 = $this->buildResponseEvent(new NotFoundHttpException('henk'));

        $this->assertFalse($event2->isPropagationStopped());
    }


    private function buildControllerEvent($controller)
    {
        return new FilterControllerEvent(
            $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'),
            $controller,
            new Request(),
            'henk'
        );
    }

    private function buildResponseEvent($ex)
    {
        return new GetResponseForExceptionEvent(
            $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'),
            new Request(),
            'henk',
            $ex
        );
    }
}
