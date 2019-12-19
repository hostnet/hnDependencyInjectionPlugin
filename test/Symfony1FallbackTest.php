<?php
namespace Hostnet\HnDependencyInjectionPlugin;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @covers \Hostnet\HnDependencyInjectionPlugin\Symfony1Fallback
 */
class Symfony1FallbackTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Symfony1Fallback
     */
    private $fallback;

    protected function setUp(): void
    {
        $this->container = new Container();
        $sf1_kernel      = $this
            ->getMockBuilder('Hostnet\HnDependencyInjectionPlugin\Symfony1Kernel')
            ->disableOriginalConstructor()
            ->getMock();

        $sf1_kernel
            ->expects($this->any())
            ->method('getContainer')
            ->willReturn($this->container);

        $this->fallback = $this
            ->getMockBuilder('Hostnet\HnDependencyInjectionPlugin\Symfony1Fallback')
            ->setConstructorArgs([$sf1_kernel])
            ->setMethods(['fallbackToSymfony1'])
            ->getMock();
    }

    public function testOnKernelExceptionWrongException(): void
    {
        $this->fallback
            ->expects($this->never())
            ->method('fallbackToSymfony1');

        $event = $this->buildResponseEvent(new \Exception('henk'));
        $this->fallback->onKernelException($event);

        self::assertFalse($event->isPropagationStopped());
    }

    public function testOnKernelExceptionSymfony2FourOFourEnabled(): void
    {
        $this->container->setParameter('hn_entities_enable_symfony2_404', true);

        $event = $this->buildResponseEvent(new NotFoundHttpException('henk'));
        $this->fallback->onKernelException($event);

        self::assertFalse($event->isPropagationStopped());
    }

    public function testOnKernelExceptionNeverInit(): void
    {
        $this->fallback
            ->expects($this->once())
            ->method('fallbackToSymfony1')
            ->willThrowException(new NotFoundHttpException('hans'));

        $event = $this->buildResponseEvent(new NotFoundHttpException('henk'));
        $this->fallback->onKernelException($event);

        self::assertFalse($event->isPropagationStopped());
    }

    public function testOnKernelExceptionSf2Init(): void
    {
        $this->fallback
            ->expects($this->never())
            ->method('fallbackToSymfony1');

        $event1 = $this->buildControllerEvent([clone $this->fallback, 'fallbackToSymfony1']);
        $this->fallback->onKernelController($event1);

        $event2 = $this->buildControllerEvent([$this->fallback, 'fallbackToSymfony1']);
        $this->fallback->onKernelController($event2);

        $event3 = $this->buildResponseEvent(new NotFoundHttpException('henk'));
        $this->fallback->onKernelException($event3);

        self::assertFalse($event3->isPropagationStopped());
    }

    public function testOnKernelExceptionSf1InitResponse(): void
    {
        $this->fallback
            ->expects($this->once())
            ->method('fallbackToSymfony1')
            ->willReturn(new Response());

        $event2 = $this->buildResponseEvent(new NotFoundHttpException('henk'));
        $this->fallback->onKernelException($event2);

        self::assertTrue($event2->isPropagationStopped());
    }

    public function testOnKernelExceptionControllerSf1Init(): void
    {
        $this->fallback
            ->expects($this->never())
            ->method('fallbackToSymfony1');

        $event1 = $this->buildControllerEvent(function () {
        });
        $this->fallback->onKernelController($event1);

        $event2 = $this->buildResponseEvent(new NotFoundHttpException('henk'));
        $this->fallback->onKernelException($event2);

        self::assertFalse($event2->isPropagationStopped());
    }

    public function testOnKernelExceptionController(): void
    {
        $this->fallback
            ->expects($this->never())
            ->method('fallbackToSymfony1');

        $event1 = $this->buildControllerEvent([$this->fallback, 'fallbackToSymfony1']);
        $this->fallback->onKernelController($event1);

        $event2 = $this->buildResponseEvent(new NotFoundHttpException('henk'));

        self::assertFalse($event2->isPropagationStopped());
    }


    private function buildControllerEvent($controller): ControllerEvent
    {
        return new ControllerEvent(
            $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface'),
            $controller,
            new Request(),
            500
        );
    }

    private function buildResponseEvent($ex): ExceptionEvent
    {
        return new ExceptionEvent(
            $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface'),
            new Request(),
            500,
            $ex
        );
    }
}
