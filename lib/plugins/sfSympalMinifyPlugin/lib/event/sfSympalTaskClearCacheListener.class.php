<?php

/**
 * Adds a listener on the task.cache.clear to clear the combiner css
 * and js cache in the web/cache directory
 * 
 * @package     sfSympalPlugin
 * @subpackage  listener
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-03-18
 * @version     svn:$Id$ $Author$
 */
class sfSympalTaskClearCacheListener extends sfSympalListener
{  
  public function getEventName()
  {
    return 'task.cache.clear';
  }

  public function run(sfEvent $event)
  {
    // Instantiate a new clear object and run it
    $minifyClear = new sfSympalMinifyClear();
    $minifyClear->setTask($event->getSubject());
    $minifyClear->clear();
  }
}