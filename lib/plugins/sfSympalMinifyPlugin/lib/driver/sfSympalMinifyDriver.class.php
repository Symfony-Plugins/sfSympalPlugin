<?php

/**
 * Base class for minification drivers
 * 
 * A minification driver is anything that does this:
 *   - Takes in a file path
 *   - Minimizes its contents and returns them
 * 
 * Taken in spirit from the npAssetsOptmizerPlugin by Nicolas Perriault
 * 
 * @package     sfSympalMinifyPlugin
 * @subpackage  driver
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-04-01
 * @version     svn:$Id$ $Author$
 */
abstract class sfSympalMinifyDriver
{
  /**
   * @var array An array of options for the driver
   */
  protected $_options = array();

  /**
   * Class constructor
   * 
   * @param array $options
   */
  public function __construct(array $options = array())
  {
    $this->_options = $options;
  }

  /**
   * Processes the given file and returns the processed/minified contents
   * 
   * @param string $path The absolute file path to the file to minify
   */
  public function process($data)
  {
    return $this->doProcess($data);
  }

  /**
   * Does the actual processing of the raw data
   */
  protected abstract function doProcess($data);

  /**
   * Returns an option of a default if the option doesn't exist
   * 
   * @param string $name The name of the option
   * @param mixed $default The value to return if the option doesn't exist
   */
  public function getOption($name, $default = null)
  {
    return isset($this->_options[$name]) ? $this->_options[$name] : $default;
  }
}