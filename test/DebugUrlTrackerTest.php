<?php
namespace Hostnet\HnDependencyInjectionPlugin;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @covers \Hostnet\HnDependencyInjectionPlugin\DebugUrlTracker
 */
class DebugUrlTrackerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider dataProvider
     */
    public function testOnKernelResponse($headers, $has_xdebug_token_link, $is_master_request, $expect_debug_bar): void
    {
        $symfony1_response = $this->prophesize(\sfWebResponse::class);

        //Use supplied Content-Type for sf1 Context
        $symfony1_response->getHttpHeader('Content-Disposition')->willReturn($headers['Content-Disposition']);
        $symfony1_response->getContentType()->willReturn($headers['Content-Type']);

        $symfony1_context = $this->prophesize(Symfony1Context::class);
        $symfony1_context->isInitialized()->willReturn(true);
        $symfony1_context->getResponse()->willReturn($symfony1_response->reveal());

        $debug_url_tracker = new DebugUrlTracker($symfony1_context->reveal());

        $response = new Response("", 200, $headers);

        if ($has_xdebug_token_link) {
            $response->headers->set('x-debug-token-link', "420xx0");
        }

        $debug_url_tracker->onKernelResponse(
            new ResponseEvent(
                $this->prophesize(KernelInterface::class)->reveal(),
                Request::create('/some/path'),
                $is_master_request ? HttpKernelInterface::MASTER_REQUEST : HttpKernelInterface::SUB_REQUEST,
                $response
            )
        );

        if ($expect_debug_bar) {
            self::expectOutputRegex("/We have a debug bar/");
        } else {
            self::expectOutputString("");
        }
    }

    public function dataProvider(): iterable
    {
        return [
            [['Content-Type' => 'text/html', 'Content-Disposition' => null], true, true, true],
            [
                ['Content-Type' => 'text/html', 'Content-Disposition' => 'attachment; filename="attachment.txt"'],
                true,
                true,
                false
            ],
            [['Content-Type' => 'text/html', 'Content-Disposition' => null], true, false, false],
            [['Content-Type' => null, 'Content-Disposition' => null], true, false, false],
            [['Content-Type' => 'application/octet-stream', 'Content-Disposition' => null], true, false, false],
        ];
    }
}
