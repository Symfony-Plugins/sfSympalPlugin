<?php

/**
 * Represents a theme
 * 
 * @package     sfSympalRenderingPlugin
 * @subpackage  theme
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @since       2010-03-27
 * @version     svn:$Id$ $Author$
 */
class sfSympalTheme
{
  /**
   * Whether or not this theme has been loaded 
   * @see load()
   * 
   * @var boolean
   */
  protected
    $_isLoaded = false;
  
  /**
   * Dependencies needed by this class
   */
  protected
    $_sympalContext,
    $_configuration,
    $_request,
    $_response;

  public function __construct(sfSympalContext $sympalContext, sfSympalThemeConfiguration $configuration)
  {
    $this->_sympalContext = $sympalContext;
    $this->_configuration = $configuration;
    $this->_request = $this->_sympalContext->getSymfonyContext()->getRequest();
    $this->_response = $this->_sympalContext->getSymfonyContext()->getResponse();
  }

  public function getConfiguration()
  {
    return $this->_configuration;
  }

  public function getName()
  {
    return $this->_configuration->getName();
  }

  public function getLayoutPath()
  {
    return $this->_configuration->getLayoutPath();
  }

  public function getStylesheets()
  {
    return $this->_configuration->getStylesheets();
  }

  public function getJavascripts()
  {
    return $this->_configuration->getJavascripts();
  }

  /**
   * Loads a theme - effectively making it active
   */
  public function load()
  {
    // Change the layout
    $this->changeLayout();

    if ($this->_isLoaded === false)
    {
      $this->_sympalContext->unloadPreviousTheme();

      // Add theme stylesheets to response
      $this->addStylesheets();

      // Add theme javascripts to response
      $this->addJavascripts();

      // Invoke any callables
      $this->invokeCallables();

      // Set loaded flag
      $this->_isLoaded = true;
    }
  }

  public function addStylesheets()
  {
    foreach ($this->_configuration->getStylesheets() as $stylesheet)
    {
      $this->_response->addStylesheet(sfSympalConfig::getAssetPath($stylesheet), 'last');
    }
  }

  public function addJavascripts()
  {
    foreach ($this->_configuration->getJavascripts() as $javascript)
    {
      $this->_response->addJavascript(sfSympalConfig::getAssetPath($javascript));
    }
  }

  public function invokeCallables()
  {
    foreach ($this->_configuration->getCallables() as $callable)
    {
      if (count($callable) > 1)
      {
        call_user_func($callable);
      } else {
        call_user_func($callable[0]);
      }
    }
  }

  /**
   * Unloads this theme by removing its stylesheets and javascripts
   */
  public function unload()
  {
    // if we're not loaded, no need to unload
    if (!$this->_isLoaded)
    {
      return;
    }
    
    // Set the loaded flag to false
    $this->_isLoaded = false;

    // Remove theme stylesheets
    $this->removeStylesheets();

    // Remove theme javascripts
    $this->removeJavascripts();
  }

  public function removeStylesheets()
  {
    foreach ($this->_configuration->getStylesheets() as $stylesheet)
    {
      $this->_response->removeStylesheet(sfSympalConfig::getAssetPath($stylesheet));
    }
  }

  public function removeJavascripts()
  {
    foreach ($this->_configuration->getJavascripts() as $javascript)
    {
      $this->_response->removeJavascript(sfSympalConfig::getAssetPath($javascript));
    }
  }

  /**
   * Changes the current layout to the layout of this theme
   */
  public function changeLayout()
  {
    $info = pathinfo($this->_configuration->getLayoutPath());
    $path = $info['dirname'].'/'.$info['filename'];
    $name = $info['filename'];

    $actionEntry = $this->_sympalContext->getSymfonyContext()->getController()->getActionStack()->getLastEntry();
    $module = $actionEntry ? $actionEntry->getModuleName():$this->_request->getParameter('module');
    $action = $actionEntry ? $actionEntry->getActionName():$this->_request->getParameter('action');

    sfConfig::set('symfony.view.'.$module.'_'.$action.'_layout', $path);
    sfConfig::set('symfony.view.sympal_default_error404_layout', $path);
    sfConfig::set('symfony.view.sympal_default_secure_layout', $path);
  }

  public function __toString()
  {
    return $this->_configuration->getName();
  }
}