<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginsfSympalContent extends BasesfSympalContent
{
  protected
    $_allPermissions,
    $_route,
    $_routeObject,
    $_mainMenuItem;

  public static function createNew($type)
  {
    if (is_string($type))
    {
      $type = Doctrine_Core::getTable('sfSympalContentType')->findOneByName($type);

      if (!$type)
      {
        throw new InvalidArgumentException(sprintf('Could not find Sympal Content Type named "%s"', $type));
      }
    }

    if (!$type instanceof sfSympalContentType)
    {
      throw new InvalidArgumentException(sprintf('Invalid Content Type', $type));
    }

    $name = $type->name;

    $content = new sfSympalContent();
    $content->Type = $type;
    $content->$name = new $name();

    return $content;
  }

  public function getModuleToRenderWith()
  {
    if ($module = $this->_get('module'))
    {
      return $module;
    } else {
      return $this->getType()->getModuleToRenderWith();
    }
  }

  public function hasCustomAction()
  {
    return ($this->_get('action') || sfSympalToolkit::moduleAndActionExists($this->getModuleToRenderWith(), $this->getCustomActionName()));
  }

  public function getCustomActionName()
  {
    if ($actionName = $this->_get('action'))
    {
      return $actionName;
    } else {
      return $this->getUnderscoredSlug();
    }
  }

  public function getUnderscoredSlug()
  {
    return str_replace('-', '_', $this->getSlug());
  }

  public function getActionToRenderWith()
  {
    if ($this->hasCustomAction())
    {
      return $this->getCustomActionName();
    } else {
      return $this->getType()->getActionToRenderWith();
    }
  }

  public function hasSlot($name)
  {
    return isset($this->Slots[$name]) ? true : false;
  }

  public function hasSlots()
  {
    return count($this->Slots) > 0 ? true : false;
  }

  public function getSlot($name)
  {
    if ($this->hasSlot($name))
    {
      return $this->Slots[$name];
    }
    return null;
  }

  public function removeSlot(sfSympalContentSlot $slot)
  {
    return Doctrine_Core::getTable('sfSympalContentSlotRef')
      ->createQuery()
      ->delete()
      ->where('content_slot_id = ?', $slot->id)
      ->andWhere('content_id = ?', $this->id)
      ->execute();
  }

  public function addSlot(sfSympalContentSlot $slot)
  {
    $this->removeSlot($slot);

    $contentSlotRef = new sfSympalContentSlotRef();
    $contentSlotRef->content_slot_id = $slot->id;
    $contentSlotRef->content_id = $this->id;
    $contentSlotRef->save();

    return $contentSlotRef;
  }

  public function getOrCreateSlot($name, $type = null, $renderFunction = null)
  {
    if (!$hasSlot = $this->hasSlot($name))
    {
      $isColumn = $this->hasField($name) ? true : false;
      $type = $type ? $type : 'Text';

      $slot = new sfSympalContentSlot();
      $slot->is_column = $isColumn;
      
      if ($slot->is_column && is_null($renderFunction))
      {
        $renderFunction = 'get_sympal_content_property';
      }

      $slot->render_function = $renderFunction;
      $slot->name = $name;
      $slot->type = $type;
      $slot->save();

      $this->addSlot($slot);
    } else {
      $slot = $this->getSlot($name);
    }

    $slot->setContentRenderedFor($this);

    return $slot;
  }

  public function hasField($name)
  {
    $result = $this->_table->hasField($name);
    if (!$result)
    {
      $className = $this->getType()->getName();
      $table = Doctrine_Core::getTable($className);
      if ($table->hasField($name))
      {
        $result = true;
      }
      if (sfSympalConfig::isI18nEnabled($className))
      {
        $table = Doctrine_Core::getTable($className.'Translation');
        if ($table->hasField($name))
        {
          $result = true;
        }
      }
    }
    return $result;
  }

  public function getUrl($options = array())
  {
    return sfContext::getInstance()->getController()->genUrl($this->getRoute(), $options);
  }

  public function getPubDate()
  {
    return strtotime($this->date_published);
  }

  public function getContentTypeClassName()
  {
    $contentTypes = sfSympalContext::getInstance()->getSympalConfiguration()->getContentTypes();
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
    }
    return $this->_allPermissions;
  }

  public function __toString()
  {
    return $this->getHeaderTitle();
  }

  public function getIndented()
  {
    $menuItem = $this->getMenuItem();
    if ($menuItem)
    {
      return str_repeat('-', $menuItem->getLevel()).' '.(string) $this;
    } else {
      return (string) $this;
    }
  }

  public function getTitle()
  {
    return $this->getHeaderTitle();
  }

  public function getRelatedMenuItem()
  {
    $menuItem = $this->_get('MenuItem');
    if ($menuItem && $menuItem->exists())
    {
      $this->_mainMenuItem = $menuItem;
    }
    return $this->_mainMenuItem;
  }

  public function getRecord()
  {
    if ($this['Type']['name'])
    {
      Doctrine_Core::initializeModels(array($this['Type']['name']));
      return $this[$this['Type']['name']];
    } else {
      return false;
    }
  }

  public function publish()
  {
    $this->date_published = new Doctrine_Expression('NOW()');
    $this->save();
    $this->refresh();
  }

  public function unpublish()
  {
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
      return (string) $this;
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

  public function getFeedDescriptionPotentialSlots()
  {
    return array(
      'body'
    );
  }

  public function getFeedDescription()
  {
    if (method_exists($this->getContentTypeClassName(), 'getFeedDescription'))
    {
      return $this->getRecord()->getFeedDescription();
    }

    foreach ($this->getFeedDescriptionPotentialSlots() as $slotName)
    {
      if ($this->hasSlot($slotName))
      {
        $slot = $this->getSlot($slotName);
        break;
      }
    }

    if ($this->Slots->count() > 0)
    {
      $slot = $this->Slots->getFirst();
    }

    $slot->setContentRenderedFor($this);

    return $slot->render();
  }

  public function getFormatData($format)
  {
    $method = 'get'.ucfirst($format).'FormatData';
    if (method_exists($this->getContentTypeClassName(), $method))
    {
      return $this->getRecord()->$method();
    } else if (method_exists($this, $method)) {
      $data = $this->$method();
    } else {
      $data = $this->getDefaultFormatData();
    }
    return Doctrine_Parser::dump($this->$method(), $format);
  }

  public function getDefaultFormatData()
  {
    $data = $this->toArray(true);
    unset(
      $data['MenuItem']['__children'],
      $data['MenuItem']['Groups'],
      $data['Groups'],
      $data['Links'],
      $data['Assets'],
      $data['CreatedBy'],
      $data['Site']
    );
    return $data;
  }

  public function getXmlFormatData()
  {
    return $this->getDefaultFormatData();
  }

  public function getYmlFormatData()
  {
    return $this->getDefaultFormatData();
  }

  public function getJsonFormatData()
  {
    return $this->getDefaultFormatData();
  }

  public function getIsPublished()
  {
    return ($this->getDatePublished() && strtotime($this->getDatePublished()) <= time()) ? true : false;
  }

  public function getIsPublishedInTheFuture()
  {
    return ($this->getDatePublished() && strtotime($this->getDatePublished()) > time()) ? true : false;
  }

  public function getMonthPublished($format = 'm')
  {
    return date('m', strtotime($this->getDatePublished()));
  }

  public function getDayPublished()
  {
    return date('d', strtotime($this->getDatePublished()));
  }

  public function getYearPublished()
  {
    return date('Y', strtotime($this->getDatePublished()));
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

  public function getRouteName()
  {
    if ($this->get('custom_path', false) || $this->get('module', false) || $this->get('action', false))
    {
      return '@sympal_content_' . $this->getUnderscoredSlug();
    }
    else if ($this['Type']['default_path'])
    {
      return $this['Type']['route_name'];
    }
    else if ($this['slug'])
    {
      return '@sympal_content_view';
    }
  }

  public function getRoutePath()
  {
    if ($path = $this['custom_path'])
    {
      if ($path != '/')
      {
        $path .= '.:sf_format';
      }
      return $path;
    }
    else if ($this->get('module', false) || $this->get('action', false))
    {
      $values = $this->_buildRouteValues();
      $values['sf_culture'] = ':sf_culture';
      $values['sf_format'] = ':sf_format';
      return $this->getRouteObject()->generate($values);
    }
    else if ($path = $this['Type']['route_path'])
    {
      return $path;
    }
    else if ($this['slug'])
    {
      return '/content/:slug';
    }
  }

  public function getRouteObject($path = null)
  {
    if (!$this->_routeObject)
    {
      if (is_null($path))
      {
        $path = $this->getRoutePath();
      }

      $this->_routeObject = new sfRoute($path, array('sf_format' => 'html', 'sf_culture' => sfConfig::get('default_culture')));
    }
    return $this->_routeObject;
  }

  public function getRoute($routeString = null, $path = null)
  {
    if (!$this->_route)
    {
      if (!$this->exists() || !$this['slug'])
      {
        return false;
      }

      if (method_exists($this->getContentTypeClassName(), 'getRoute'))
      {
        return $this->getRecord()->getRoute();
      }

      if (is_null($routeString))
      {
        $routeString = $this->getRouteName();
      }

      $this->_route = $this->_fillRoute($routeString);
    }

    return $this->_route;
  }

  public function getEvaluatedRoutePath()
  {
    $values = $this->_buildRouteValues();
    $values['sf_culture'] = sfContext::getInstance()->getUser()->getCulture();
    return $this->getRouteObject()->generate($values);
  }

  protected function _fillRoute($route)
  {
    $values = $this->_buildRouteValues();
    if (!empty($values))
    {
      return $route.'?'.http_build_query($values);
    } else {
      return $route;
    }
  }

  protected function _buildRouteValues()
  {
    $variables = $this->getRouteObject()->getVariables();
    $isI18nEnabled = sfSympalConfig::isI18nEnabled();

    $values = array();
    foreach (array_keys($variables) as $name)
    {
      if ($isI18nEnabled && $name == 'slug' && $this->hasField('i18n_slug') && $i18nSlug = $this->i18n_slug)
      {
        $values[$name] = $i18nSlug;
      } else if ($this->hasField($name)) {
        $values[$name] = $this->$name;
      } else if (method_exists($this, $method = 'get'.sfInflector::camelize($name))) {
        $values[$name] = $this->$method();
      }
    }
    return $values;
  }

  public function trySettingTitleProperty($value)
  {
    foreach (array('title', 'name', 'subject', 'header') as $name)
    {
      try {
        $this->$name = $value;
      } catch (Exception $e) {}
    }
  }

  public function addLink(sfSympalContent $content)
  {
    $link = new sfSympalContentLink();
    $link->content_id = $this->id;
    $link->linked_content_id = $content->id;
    $link->save();

    return $link;
  }

  public function addAsset(sfSympalAsset $asset)
  {
    $contentAsset = new sfSympalContentAsset();
    $contentAsset->content_id = $this->id;
    $contentAsset->asset_id = $asset->id;
    $contentAsset->save();

    return $contentAsset;
  }

  public function deleteLinkAndAssetReferences()
  {
    $this->deleteAssetReferences();
    $this->deleteLinkReferences();
  }

  public function deleteAssetReferences()
  {
    $count = Doctrine_Query::create()
      ->delete('sfSympalContentLink')
      ->where('content_id = ?', $this->getId())
      ->execute();
    return $count;
  }

  public function deleteLinkReferences()
  {
    $count = Doctrine_Query::create()
      ->delete('sfSympalContentAsset')
      ->where('content_id = ?', $this->getId())
      ->execute();
    return $count;
  }

  public function postInsert($event)
  {
    $event->getInvoker()->deleteLinkAndAssetReferences();
  }

  public function postUpdate($event)
  {
    $event->getInvoker()->deleteLinkAndAssetReferences();
  }

  public static function slugBuilder($text, $content)
  {
    if ($record = $content->getRecord())
    {
      try {
        return $record->slugBuilder($text);
      } catch (Doctrine_Record_UnknownPropertyException $e) {
        return Doctrine_Inflector::urlize($text);
      }
    } else {
      return Doctrine_Inflector::urlize($text);
    }
  }

  public function getTemplateToRenderWith()
  {
    if (!$template = $this->getTemplate())
    {
      $template = $this->getType()->getTemplate();
    }
    $templates = sfSympalConfiguration::getActive()->getContentTemplates($this->getType()->getSlug());
    if (isset($templates[$template]))
    {
      $template = $templates[$template]['template'];
    }
    $template = $template ? $template : sfSympalConfig::get($this->getType()->getSlug(), 'default_content_template', sfSympalConfig::get('default_content_template'));
    return $template;
  }

  public function getThemeToRenderWith()
  {
    if ($theme = $this->getTheme()) {
      return $theme;
    } else if ($theme = $this->getType()->getTheme()) {
      return $theme;
    } else if ($theme = $this->getSite()->getTheme()) {
      return $theme;
    } else {
      return sfSympalConfig::get($this->getType()->getSlug(), 'default_theme', sfSympalConfig::get('default_theme', null, $this->getSite()->getSlug()));
    }
  }

  public function getSiteId()
  {
    return $this->_get('site_id');
  }

  public function getContentTypeId()
  {
    return $this->_get('content_type_id');
  }

  public function getLastUpdatedBy()
  {
    return $this->_get('LastUpdatedBy');
  }

  public function getLastUpdatedById()
  {
    return $this->_get('last_updated_by_id');
  }

  public function getCreatedBy()
  {
    return $this->_get('CreatedBy');
  }

  public function getCreatedById()
  {
    return $this->_get('created_by_id');
  }

  public function getDatePublished()
  {
    return $this->_get('date_published');
  }

  public function getCustomPath()
  {
    return $this->_get('custom_path');
  }

  public function getPageTitle()
  {
    return $this->_get('page_title');
  }

  public function getMetaKeywords()
  {
    return $this->_get('meta_keywords');
  }

  public function getMetaDescription()
  {
    return $this->_get('meta_description');
  }

  public function getI18nSlug()
  {
    return $this->_get('i18n_slug');
  }

  public function getSite()
  {
    return $this->_get('Site');
  }

  public function getType()
  {
    return $this->_get('Type');
  }

  public function getGroups()
  {
    return $this->_get('Groups');
  }

  public function getPermissions()
  {
    return $this->_get('Permissions');
  }

  public function getMenuItem()
  {
    return $this->_get('MenuItem');
  }

  public function getSlots()
  {
    return $this->_get('Slots');
  }

  public function getContentGroups()
  {
    return $this->_get('ContentGroups');
  }
}