<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginMenuItem extends BaseMenuItem
{
  protected
    $_breadcrumbs = null,
    $_allPermissions;

  public function getAllPermissions()
  {
    if (!$this->_allPermissions)
    {
      $this->_allPermissions = array();
      foreach ($this->Groups as $group)
      {
        foreach ($group->Permissions as $permission)
        {
          $this->_allPermissions[] = $permission->name;
        }
      }
      foreach ($this->Permissions as $permission)
      {
        $this->_allPermissions[] = $permission->name;
      }
    }
    return $this->_allPermissions;
  }

  public function getMainContent()
  {
    $content = $this->getMasterContent();
    if ($content && $content instanceof Doctrine_Record && $content->exists())
    {
      return $content;
    } else {
      $content = $this->getContent();
      if ($content && $content instanceof Doctrine_Record && $content->exists())
      {
        return $content;
      } else {
        return false;
      }
    }
  }

  public function getParentId()
  {
    $node = $this->getNode();

    if (!$node->isValidNode() || $node->isRoot())
    {      
      return null;
    }

    $parent = $node->getParent();

    return $parent['id'];
  }
  
  public function getIndentedName()
  {
    return str_repeat('-', $this->getLevel()) . ' ' . $this->getLabel();
  }

  public function preValidate($event)
  {
    $invoker = $event->getInvoker();
    $modified = $invoker->getModified();
    if (isset($modified['is_published']) && $modified['is_published'] && !isset($modified['date_published']))
    {
      $invoker->date_published = new Doctrine_Expression('NOW()');
    }

    if (sfContext::hasInstance())
    {
      $invoker->site_id = sfSympalContext::getInstance()->getSiteRecord()->getId();
    }
  }

  public function __toString()
  {
    return $this->getIndentedName();
  }

  public function getLabel()
  {
    return $this->_get('label') ? $this->_get('label'):$this->name;
  }

  public function getIndented()
  {
    return (string) $this;
  }

  public function getContent()
  {
    return $this->getRelatedContent();
  }

  public function setContent(Content $content)
  {
    $this->RelatedContent = $content;
  }

  public function getRoute()
  {
    return $this->getCustomPath();
  }

  public function setRoute($route)
  {
    $this->setCustomPath($route);
  }

  public function getItemRoute()
  {
    if (!$route = $this->getRoute())
    {
      if ($this->getIsContentTypeList())
      {
        $type = $this->getContentType();
        if ($type->list_path)
        {
          $route = '@sympal_content_type_'.str_replace('-', '_', $type['slug']);
        }
      } else if (!$this->getContent() instanceof Doctrine_Null && $this->getContent()) {
        $route = $this->getContent()->getRoute();
      }
    }
    return $route;
  }

  public function getBreadcrumbs()
  {
    if (is_null($this->_breadcrumbs))
    {
      $menu = sfSympalMenuSiteManager::getMenu('primary');
      if ($menu)
      {
        $node = $menu->findMenuItem($this);

        if ($node)
        {
          $this->_breadcrumbs = $node->getBreadcrumbs();
        }
      }
      if (is_null($this->_breadcrumbs))
      {
        $this->_breadcrumbs = sfSympalToolkit::generateBreadcrumbs(array());
      }
    }

    return $this->_breadcrumbs;
  }

  public function getLayout()
  {
    if ($layout = $this->getContentType()->getLayout())
    {
      return $layout;
    } else if ($layout = $this->getSite()->getLayout()) {
      return $layout;
    } else {
      return sfSympalConfig::get('default_layout', null, $this->getSite()->getSlug());
    }
  }
}