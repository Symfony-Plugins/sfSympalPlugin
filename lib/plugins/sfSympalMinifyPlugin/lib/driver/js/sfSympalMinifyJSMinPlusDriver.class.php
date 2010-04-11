<?php

/**
 * Javascript minification driver for JSMin
 * 
 * @package     sfSympalMinifyPlugin
 * @subpackage  driver
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-04-01
 * @version     svn:$Id$ $Author$
 */
class sfSympalMinifyJSMinPlusDriver extends sfSympalMinifyJsDriver
{
  protected function doProcess($data)
  {
    return JSMinPlus::minify($data);
  }
}