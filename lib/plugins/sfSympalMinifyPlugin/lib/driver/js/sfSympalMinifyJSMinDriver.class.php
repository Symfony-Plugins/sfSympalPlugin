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
class sfSympalMinifyJSMinDriver extends sfSympalMinifyJsDriver
{
  protected function doProcess($data)
  {
    return JSMin::minify($data);
  }
}