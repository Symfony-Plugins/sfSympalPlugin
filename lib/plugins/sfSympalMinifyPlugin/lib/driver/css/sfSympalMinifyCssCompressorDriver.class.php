<?php

/**
 * Driver for Minify_CSS_Compressor
 * 
 * @package     sfSympalMinifyPlugin
 * @subpackage  driver
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-04-01
 * @version     svn:$Id$ $Author$
 */
class sfSympalMinifyCssCompressorDriver extends sfSympalMinifyCssDriver
{
  public function doProcess($data)
  {
    return Minify_CSS_Compressor::process($data);
  }
}