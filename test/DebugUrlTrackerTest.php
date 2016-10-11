<?php
namespace Hostnet\HnDependencyInjectionPlugin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * @covers \Hostnet\HnDependencyInjectionPlugin\DebugUrlTracker
 */
class DebugUrlTrackerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DebugUrlTracker
     */
    private $debug_url_tracker;

    /**
     * @dataProvider dataProvider
     */
    public function testOnKernelResponse($headers, $has_xdebug_token_link, $is_master_request, $expect_debug_bar)
    {

        $symfony1_response = $this->prophesize(\sfWebResponse::class);

        //Use supplied Content-Type for sf1 Context
        $symfony1_response->getHttpHeader("Content-Disposition")->willReturn($headers['Content-Disposition']);
        $symfony1_response->getContentType()->willReturn($headers['Content-Type']);

        $symfony1_context = $this->prophesize(Symfony1Context::class);
        $symfony1_context->isInitialized()->willReturn(true);
        $symfony1_context->getResponse()->willReturn($symfony1_response->reveal());

        $debug_url_tracker = new DebugUrlTracker($symfony1_context->reveal());

        $response = new Response("", 200, $headers);

        if ($has_xdebug_token_link) {
            $response->headers->set('x-debug-token-link', "420xx0");
        }

        $event = $this->prophesize(FilterResponseEvent::class);
        $event->isMasterRequest()->willReturn($is_master_request);
        $event->getResponse()->willReturn($response);
        $event->getRequest()->willReturn(new Request());

        $debug_url_tracker->onKernelResponse($event->reveal());

        if ($expect_debug_bar) {
            self::expectOutputRegex("/We have a debug bar/");
        } else {
            self::expectOutputString("");
        }
    }

    public function dataProvider()
    {
        return [
            [['Content-Type' => 'text/html'], true, true, true],
            [['Content-Type' => 'text/html', 'Content-Disposition' => 'attachment; filename="attachment.txt"'], true, true, false],
            [['Content-Type' => 'text/html'], true, false, false],
            [['Content-Type' => null], true, false, false],
            [['Content-Type' => 'application/octet-stream'], true, false, false],
        ];
    }


}
