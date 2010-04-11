<?php

/**
 * Responsible for cleaning out the cached assets
 * 
 * This class is built so that if you pass it an sfTask object via setTask(),
 * it will log in richer ways. Useful if you're calling this object from
 * a task.
 * 
 * @package     sfSympalMinifyPlugin
 * @subpackage  util
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-04-01
 * @version     svn:$Id$ $Author$
 */
class sfSympalMinifyClear
{

  /**
   * A boolean for if this process has been run yet, since the task.cache.clear
   * event will be called multiple times (once per environment) on a cache
   * clear (and this task only needs to be called once)
   */
  protected static $_isProcessed = false;

  /*
   * @var Optional sfTask instance
   */
  protected $_task;
  
  /**
   * @var sfFilesystem instance
   */ 
  protected $_filesystem;


  /**
   * Class constructor
   */
  public function __construct()
  {
  }

  /**
   * Sets the task that is using this object.
   * 
   * This supplies an optional sfTask dependency. If using this object
   * from within a task, setting the task allows for the log data to
   * be correctly output
   */
  public function setTask(sfTask $task)
  {
    $this->_task = $task;
  }

  /**
   * Clears the asset cache
   * 
   * @param boolean $force Whether to rerun the clear if it's already be run on this request
   */
  public function clear($force = false)
  {
    if (self::$_isProcessed && !$force)
    {
      return;
    }
    
    $this->logSection('sympal', 'Clearing web cache folder');

    $failures = array();
    $cacheDir = sfConfig::get('sf_web_dir').'/cache';
    if (is_dir($cacheDir))
    {
      $filesystem = $this->getFilesystem();
      
      $finder = sfFinder::type('file')->ignore_version_control()->discard('.sf');
      foreach ($finder->in($cacheDir) as $file)
      {
        @$filesystem->remove($file);
        
        if (file_exists($file))
        {
          $failures[] = $file;
        }
      }
    }
    self::$_isProcessed = true;
    
    if (count($failures) > 0)
    {
      $this->logBlock(array_merge(
        array('Could not clear cache on the following files:', ''),
        array_map(create_function('$f', 'return \' - \'.sfDebug::shortenFilePath($f);'), $failures)
      ), 'ERROR_LARGE');
    }
  }

  /**
   * Returns the sfFilesystem object to use
   * 
   * @return sfFilesystem
   */
  protected function getFilesystem()
  {
    if ($this->_filesystem === null)
    {
      if ($this->_task)
      {
        $this->_filesystem = $this->_task->getFilesystem();
      }
      else
      {
        $this->_filesystem = new sfFilesystem();
      }
    }
    
    return $this->_filesystem;
  }

  /**
   * @see sfTask
   */
  public function logSection($section, $message, $size = null, $style = 'INFO')
  {
    if ($this->_task)
    {
      $this->_task->logSection($section, $message, $size, $style);
    }
  }

  /**
   * @see sfTask
   */
  public function logBlock($messages, $style)
  {
    if ($this->_task)
    {
      $this->_task->logBlock($messages, $style);
    }
  }
}