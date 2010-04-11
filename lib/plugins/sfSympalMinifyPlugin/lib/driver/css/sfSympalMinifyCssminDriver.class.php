<?php

/**
 * Driver for cssmin
 * 
 * @package     sfSympalMinifyPlugin
 * @subpackage  driver
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-04-01
 * @version     svn:$Id$ $Author$
 */
class sfSympalMinifyCssminDriver extends sfSympalMinifyCssDriver
{
  public function doProcess($data)
  {
    return cssmin::minify($data);
  }
}