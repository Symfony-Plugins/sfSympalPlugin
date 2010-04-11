<?php

/**
 * Configuration class for the minifier plugin
 * 
 * @package     sfSympalMinifyPlugin
 * @subpackage  config
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-03-28
 * @version     svn:$Id$ $Author$
 */
class sfSympalMinifyPluginConfiguration extends sfPluginConfiguration
{
  
  public function initialize()
  {
    // register a listener on task.cache.clear to clear the minified files
    new sfSympalTaskClearCacheListener($this->dispatcher, $this);

    $this->dispatcher->connect('sympal.load', array($this, 'bootstrap'));
  }

  /**
   * Listens to the sympal.load event
   */
  public function bootstrap(sfEvent $event)
  {
    $this->configuration->loadHelpers(array('SympalMinify'));
  }
}