<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginPlugin extends BasePlugin
{
  public function getRoute()
  {
    return '@sympal_plugin_manager_view?plugin='.$this->getName();
  }

  public function getActionRoute($action)
  {
    return '@sympal_plugin_manager_'.$action.'?plugin='.$this->getName();
  }

  public function getAuthorName()
  {
    return $this->getAuthor()->getName();
  }

  public function getAuthorEmail()
  {
    return $this->getAuthor()->getEmail();
  }

  public function isDownloaded()
  {
    return $this->getIsDownloaded();
  }

  public function isInstalled()
  {
    return $this->getIsInstalled();
  }

  public function getImage()
  {
    if (!$image = $this->_get('image'))
    {
      return 'http://www.symfony-project.org/images/plugin_default.png';
    } else {
      return $image;
    }
  }
}