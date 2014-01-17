<?php

namespace Hostnet\HnDependencyInjectionPlugin;

/**
 * To get a link to the Symfony 2 profiler in the Symfony 1 dev toolbar.
 *
 * Requires the DebugUrlTracker to be registered, which is the case if you're using the plugin.
 * @see Hostnet\HnDependencyInjectionPlugin\DebugUrlTracker
 */
class Symfony1Panel extends \sfWebDebugPanel
{

  public function getTitle()
  {
    return 'Symfony 2';
  }

  public function getPanelTitle()
  {
    return 'sf2'; // Used by the DebugUrlTracker
  }

  public function getPanelContent()
  {
    return 'Did not find a Symfony 2 debug url. Does the AppKernel register the WebProfilerBundle?';
  }
}