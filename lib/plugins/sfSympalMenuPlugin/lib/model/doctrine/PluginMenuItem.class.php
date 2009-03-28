<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginMenuItem extends BaseMenuItem
{
  protected
    $_breadcrumbs,
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
          $route = '@sympal_content_type_'.$type['slug'];
        } else {
          throw new sfException($this['name'].' menu item is not mapped to any route/url');
        }
      } else if (!$this->getContent() instanceof Doctrine_Null && $this->getContent()) {
        $route = $this->getContent()->getRoute();
      }
    }
    return $route;
  }

  public function getBreadcrumbs($content = null, $subItem = null)
  {
    if (!$this->_breadcrumbs)
    {
      $breadcrumbs = array();

      if ($this->getLevel() > 0)
      {
        $tree = $this->getTable()->getTree();

        $q = Doctrine_Query::create()
          ->addSelect('m.*, e.*')
          ->from('MenuItem m')
          ->leftJoin('m.RelatedContent e');

        $tree->setBaseQuery($q);
        $ancestors = $this->getNode()->getAncestors();
        $tree->resetBaseQuery();

        $breadcrumbs = array();
        if ($ancestors)
        {
          foreach ($ancestors as $ancestor)
          {
            $breadcrumbs[$ancestor->getLabel()] = $ancestor->getItemRoute();
          }
        }

        if ($content)
        {
          if ($this->is_content_type_list)
          {
            $breadcrumbs[$this->getLabel()] = $this->getItemRoute();
          }

          $breadcrumbs[$content->getHeaderTitle()] = $content->getRoute();
        } else {
          $breadcrumbs[$this->getLabel()] = $this->getItemRoute();
        }

        if ($subItem)
        {
          $breadcrumbs[$subItem] = null;
        }
      }
      $this->_breadcrumbs = sfSympalTools::generateBreadcrumbs($breadcrumbs);
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