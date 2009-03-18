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
          $this->_allPermissions = $permission->name;
        }
      }
      foreach ($this->Permissions as $permission)
      {
        $this->_allPermissions = $permission->name;
      }
    }
    return $this->_allPermissions;
  }

  public function getMainEntity()
  {
    $entity = $this->getMasterEntity();
    if ($entity && $entity instanceof Doctrine_Record && $entity->exists())
    {
      return $entity;
    } else {
      return $this->getEntity();
    }
  }

  public function getParentId()
  {
    if (!$this->getNode()->isValidNode() || $this->getNode()->isRoot())
    {      
      return null;
    }
    
    $parent = $this->getNode()->getParent();
    
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
  }

  public function __toString()
  {
    return $this->getIndentedName();
  }

  public function getLabel()
  {
    if ($this->getTable()->hasRelation('Translation'))
    {
      return $this->Translation['en']['label'];
    } else {
      return $this->_get('label') ? $this->_get('label'):$this->name;
    }
  }

  public function getIndented()
  {
    return (string) $this;
  }

  public function getEntity()
  {
    return $this->getRelatedEntity();
  }

  public function setEntity(Entity $entity)
  {
    $this->RelatedEntity = $entity;
  }

  public function getItemRoute()
  {
    if (!$route = $this->getRoute())
    {
      if ($this->getHasManyEntities())
      {
        $type = $this->getEntityType();
        if ($type->list_route_url)
        {
          $route = '@sympal_entity_type_'.$type['id'];
        } else {
          throw new sfException('MenuItem has invalid route.');
        }
      } else if (!$this->getEntity() instanceof Doctrine_Null && $this->getEntity()) {
        $route = $this->getEntity()->getRoute();
      }
    }
    return $route;
  }

  public function getBreadcrumbs($entity = null)
  {
    if (!$this->_breadcrumbs)
    {
      $this->_breadcrumbs = new sfSympalMenuBreadcrumbs('Breadcrumbs');

      if ($this->getLevel() > 0)
      {
        $tree = $this->getTable()->getTree();

        $q = Doctrine_Query::create()
          ->addSelect('m.*, e.*')
          ->from('MenuItem m')
          ->leftJoin('m.RelatedEntity e');

        $tree->setBaseQuery($q);
        $ancestors = $this->getNode()->getAncestors();
        $tree->resetBaseQuery();

        if ($ancestors)
        {
          foreach ($ancestors as $ancestor)
          {
            $this->_breadcrumbs->addNode($ancestor->getLabel(), $ancestor->getItemRoute());
          }
        }

        if ($this->getHasManyEntities())
        {
          if ($entity)
          {
            $this->_breadcrumbs->addNode($this->getLabel(), $this->getItemRoute());
            $this->_breadcrumbs->addNode($entity->getHeaderTitle());
          } else {
            $this->_breadcrumbs->addNode($this->getLabel());
          }
        } else {
          if ($entity)
          {
            $this->_breadcrumbs->addNode($this->getLabel(), $this->getItemRoute());
            $this->_breadcrumbs->addNode($entity->getHeaderTitle());
          } else {
            $this->_breadcrumbs->addNode($this->getLabel());
          }
        }
      }
    }
    return $this->_breadcrumbs;
  }

  public function getLayout()
  {
    if ($layout = $this->getEntityType()->getLayout())
    {
      return $layout;
    } else if ($layout = $this->getSite()->getLayout()) {
      return $layout;
    } else {
      return sfSympalConfig::get('default_layout', null, $this->getSite()->getSlug());
    }
  }
}