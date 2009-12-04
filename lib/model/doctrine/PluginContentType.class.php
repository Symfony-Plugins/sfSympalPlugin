<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginContentType extends BaseContentType
{
  public function construct()
  {
    if  (sfContext::hasInstance())
    {
      $this->mapValue('schema', $this->getSchemaYaml());
    }
  }

  public function setSchema($value)
  {
    return $this->mapValue('schema', $value);
  }

  public function __toString()
  {
    return (string) $this->getLabel();
  }

  public function getSchemaPath()
  {
    if ($this->plugin_name)
    {
      $search = glob(sfContext::getInstance()->getConfiguration()->getPluginConfiguration($this->plugin_name)->getRootDir().'/config/doctrine/*.yml');
      return current($search);
    } else {
      return false;
    }
  }

  public function getSchemaYaml()
  {
    if ($path = $this->getSchemaPath())
    {
      return file_get_contents($path);
    } else {
      return false;
    }
  }

  public function saveSchema()
  {
    if ($this->getSchemaPath() && $this->schema)
    {
      file_put_contents($this->getSchemaPath(), $this->schema);
    }
  }

  public function save(Doctrine_Connection $conn = null)
  {
    if  (sfContext::hasInstance())
    {
      $this->saveSchema();
    }

    parent::save($conn);
  }

  public function setName($name)
  {
    $result = Doctrine_Query::create()
      ->from('ContentType t')
      ->where('t.name = ?', $name)
      ->fetchArray();

    if ($result)
    {
      $this->assignIdentifier($result[0]['id']);
      $this->hydrate($result[0]);
    } else {
      $this->_set('name', $name);
    }
  }

  public function getTemplate()
  {
    if ($this->hasReference('ContentTemplates'))
    {
      foreach ($this->ContentTemplates as $template)
      {
        if ($template->is_default)
        {
          return $template;
        }
      }

      foreach ($this->ContentTemplates as $template)
      {
        if (!$template->is_default && $template->content_type_id == $this->getId())
        {
          return $template;
        }
      }

      return $this->ContentTemplates->getFirst();
    }
  }

  public function getSingularUpper()
  {
    return Doctrine_Core::getTable($this->getName())->getOption('name');
  }

  public function getSingularLower()
  {
    return Doctrine_Inflector::tableize(Doctrine_Core::getTable($this->getName())->getOption('name'));
  }

  public function getPluralUpper()
  {
    return Doctrine_Inflector::classify(Doctrine_Core::getTable($this->getName())->getTableName()) . 's';
  }

  public function getPluralLower()
  {
    return Doctrine_Core::getTable($this->getName())->getTableName() . 's';
  }

  public function getRoute($action = null)
  {
    return 'sympal_' . ($action ? $this->getPluralLower() . '_' . $action : $this->getPluralLower());
  }

  public function preValidate($event)
  {
    $invoker = $event->getInvoker();

    if (!$invoker->site_id)
    {
      $invoker->site_id = sfSympalToolkit::getCurrentSiteId();
    }
  }
}