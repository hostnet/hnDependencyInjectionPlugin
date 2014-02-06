<?php
namespace Hostnet\HnDependencyInjectionPlugin;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Makes sure the Symfony1Panel gets the profiler debug URL.
 *
 * A scenario
 * - New (dev) request for Symfony 2
 * - Routing misses, fall back to Symfony 1
 * - Symfony 1 runs and sends it response, including Symfony1Panel
 * - Symfony 2 kernel.response event is triggered
 * - Prio -100: - The ProfilerListener collects dev info
 * -            - Profiler adds the x-debug-token in the headers
 * - Prio -128: - WebDebugToolbarListener puts the x-debug-token-link in the response
 * - Prio -129: - DebugUrlTracker (us) inserts the link to the Symfony1Panel
 *
 * Only registered in dev mode for efficiency
 * @author Nico Schoenmaker <nschoenmaker@hostnet.nl>
 */
class DebugUrlTracker
{
    private $url;

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($this->url || ! $event->isMasterRequest() || ! \sfContext::hasInstance()) {
            return;
        }
        $response_headers = $event->getResponse()->headers;
        if ($response_headers->has('x-debug-token-link')
            // Only add when the symfony 1 content type is not javascrupt
            && strpos(\sfContext::getInstance()->getResponse()->getContentType(), 'javascript') === false
        ) {
            $this->url = $response_headers->get('x-debug-token-link');
            $link = json_encode($response_headers->get('x-debug-token-link'));
            echo <<<JAVASCRIPT
<script>
(function() {
  var element = document.querySelector("#sfWebDebugDetails a[title=sf2]");
  element.href= $link;
  element.onclick='';
}())
</script>
JAVASCRIPT;
        }
    }
}