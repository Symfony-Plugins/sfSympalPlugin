<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginContent extends BaseContent
{
  protected
    $_allPermissions;

  public static function createNew($type)
  {
    if (is_string($type))
    {
      $type = Doctrine::getTable('ContentType')->findOneByName($type);
    }

    $name = $type->name;

    $content = new Content();
    $content->Type = $type;
    $content->$name = new $name();

    return $content;
  }

  public function getIndented()
  {
    return str_repeat('-', $this->getMainMenuItem()->getLevel()).' '.$this->getTitle();
  }

  public function getUrl()
  {
    return $this->getRoute();
  }

  public function getContentTypeClassName()
  {
    $contentTypes = sfSympalToolkit::getContentTypesCache();
    if (isset($contentTypes[$this['content_type_id']]))
    {
      return $contentTypes[$this['content_type_id']];
    } else {
      throw new sfException('Invalid content type id "'.$this['content_type_id'].'". Id was not found in content type cache.');
    }
  }

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

  public function __toString()
  {
    return $this->getIndented();
  }

  public function getTitle()
  {
    return $this->getHeaderTitle();
  }

  public function getRelatedMenuItem()
  {
    if ($this->master_menu_item_id)
    {
      return $this->MasterMenuItem;
    } else {
      $menuItem = $this->_get('MenuItem');
      if ($menuItem && $menuItem->exists())
      {
        return $menuItem;
      } else {
        return false;
      }
    }
  }

  public function getMainMenuItem()
  {
    if ($menuItem = $this->getRelatedMenuItem())
    {
      return $menuItem;
    } else {
      $q = Doctrine::getTable('MenuItem')
        ->createQuery('m')
        ->innerJoin('m.Site s WITH s.slug = ?', sfConfig::get('sf_app'))
        ->andWhere('m.is_content_type_list = ?', true)
        ->andWhere('m.content_type_id = ?', $this->content_type_id)
        ->orWhere('m.is_primary = true')
        ->orderBy('m.is_primary DESC')
        ->limit(1);

      return $q->fetchOne();
    }
  }

  public function getRecord()
  {
    if ($this['Type']['name'])
    {
      Doctrine::initializeModels(array($this['Type']['name']));
      return $this[$this['Type']['name']];
    } else {
      return false;
    }
  }

  public function getTemplate()
  {
    if ($this->content_template_id)
    {
      return $this->_get('Template');
    }
    return $this->Type->getTemplate('View');
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
      $user = sfContext::getInstance()->getUser();
      if ($user->isAuthenticated())
      {
        $invoker->last_updated_by = $user->getSympalUser()->getId();
        if (!$invoker->exists() || !$invoker->created_by)
        {
          $invoker->created_by = $user->getSympalUser()->getId();
        }
      }
      $invoker->site_id = sfSympalContext::getInstance()->getSiteRecord()->getId();
    }
  }

  public function releaseLock()
  {
    if ($this->isLocked())
    {
      $this->locked_by = null;
      $this->save();
    }
  }

  public function obtainLock(sfUser $sfUser)
  {
    $lock = $sfUser->getOpenContentLock();
    if ($lock['id'] != $this['id'])
    {
      $sfUser->releaseOpenLock();
    }

    if ($this->userHasLock($sfUser))
    {
      return null;
    }

    if ($this->canLock($sfUser))
    {
      $user = $sfUser->getSympalUser();
      $this->LockedBy = $user;
      $this->save();

      return true;
    } else {
      return false;
    }
  }

  public function canLock(sfUser $sfUser)
  {
    return $sfUser->isAuthenticated() && !$this->isLocked();
  }

  public function isLocked()
  {
    return $this->locked_by ? true:false;
  }

  public function userHasLock(sfUser $sfUser = null)
  {
    if (is_null($sfUser))
    {
      $sfUser = sfContext::getInstance()->getUser();
    }

    if (!$sfUser->isAuthenticated())
    {
      return null;
    }

    $user = $sfUser->getSympalUser();

    return $user && $this['locked_by'] == $user['id'];
  }

  public function publish()
  {
    $this->is_published = true;
    $this->date_published = new Doctrine_Expression('NOW()');
    $this->save();
    $this->refresh();
  }

  public function unpublish()
  {
    $this->is_published = false;
    $this->date_published = null;
    $this->save();
  }

  public function getHeaderTitle()
  {
    if ($record = $this->getRecord())
    {
      $guesses = array('name',
                       'title',
                       'username',
                       'subject');

      // we try to guess a column which would give a good description of the object
      foreach ($guesses as $descriptionColumn)
      {
        try
        {
          return (string) $record->get($descriptionColumn);
        } catch (Exception $e) {}
      }
    }

    return sprintf('No description for object of class "%s"', $this->getTable()->getComponentName());
  }

  public function getEditRoute()
  {
    if ($this->exists())
    {
      return '@sympal_content_edit?id='.$this['id'];
    } else {
      throw new sfException('You cannot get the edit route of a object that does not exist.');
    }
  }

  public function getFeedDescription()
  {
    return sfSympalContext::getInstance()
      ->renderContent($this->getMainMenuItem(), $this)
      ->render();
  }

  public function getAuthorName()
  {
    return $this->getCreatedBy()->getName();
  }

  public function getAuthorEmail()
  {
    return $this->getCreatedBy()->getEmailAddress();
  }

  public function getUniqueId()
  {
    return $this->getId().'-'.$this->getSlug();
  }

  public function getRoute($routeString = null, $path = null)
  {
    if (!$this->exists() || !$this['slug'])
    {
      return false;
    }

    if (is_null($routeString))
    {
      if ($path = $this['custom_path'])
      {
        $routeString = '@sympal_content_' . str_replace('-', '_', $this['slug']);
      } else if ($path = $this['Type']['view_path']) {
        $routeString = '@sympal_content_view_type_' . str_replace('-', '_', $this['Type']['slug']);
      } else if ($this['slug']) {
        $path = '/content/:slug';
        $routeString = '@sympal_content_view';
      }
    }

    if (isset($path) && $path && isset($routeString) && $routeString)
    {
      $route = new sfRoute($path);
      $variables = $route->getVariables();

      $values = array();
      foreach (array_keys($variables) as $name)
      {
        try {
          $values[$name] = $this->$name;
        } catch (Exception $e) {}
      }
      if (!empty($values))
      {
        return $routeString.'?'.http_build_query($values);
      } else {
        return $routeString;
      }
    } else {
      return false;
    }
  }

  public function getLayout()
  {
    if ($layout = $this->_get('layout')) {
      return $layout;
    } else if ($layout = $this->getType()->getLayout()) {
      return $layout;
    } else if ($layout = $this->getSite()->getLayout()) {
      return $layout;
    } else {
      return sfSympalConfig::get('default_layout', null, $this->getSite()->getSlug());
    }
  }

  public function postSave($event)
  {
    $invoker = $event->getInvoker();

    if ($invoker->custom_path)
    {
      $route = Doctrine_Query::create()
        ->from('Route r')
        ->where('r.url = ?', $invoker->custom_path)
        ->fetchOne();

      if (!$route)
      {
        $route = new Route();
        $route->url = $invoker->custom_path;
        $route->name = 'sympal_content_'.str_replace('-', '_', $invoker['slug']);
        $route->type = 'View';
        $route->Content = $invoker;
        $route->ContentType = $invoker->Type;
        $route->Site = $this->Site;
        $route->save();
      }
    }
  }

  public function loadMetaData(sfWebResponse $response)
  {
    // page title
    if ($pageTitle = $this['page_title'])
    {
      $response->setTitle($pageTitle);
    } else if ($pageTitle = $this['Site']['page_title']) {
      $response->setTitle($pageTitle);
    }

    // meta keywords
    if ($metaKeywords = $this['meta_keywords'])
    {
      $response->addMeta('keywords', $metaKeywords);
    } else if ($metaKeywords = $this['Site']['meta_keywords']) {
      $response->addMeta('keywords', $metaKeywords);
    }

    // meta description
    if ($metaDescription = $this['meta_description'])
    {
      $response->addMeta('description', $metaDescription);
    } else if ($metaDescription = $this['Site']['meta_description']) {
      $response->addMeta('description', $metaDescription);
    }
  }
}