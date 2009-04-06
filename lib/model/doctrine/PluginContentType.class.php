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

  public function getTemplate($type = 'View')
  {
    $templates = $this->getTemplates();
    if (isset($templates[$type]))
    {
      return $templates[$type];
    } else {
      foreach ($templates as $template)
      {
        if ($template->getType() == $type)
        {
          return $template;
        }
      }
    }
  }

  public function getSingularUpper()
  {
    return Doctrine::getTable($this->getName())->getOption('name');
  }

  public function getSingularLower()
  {
    return Doctrine_Inflector::tableize(Doctrine::getTable($this->getName())->getOption('name'));
  }

  public function getPluralUpper()
  {
    return Doctrine_Inflector::classify(Doctrine::getTable($this->getName())->getTableName()) . 's';
  }

  public function getPluralLower()
  {
    return Doctrine::getTable($this->getName())->getTableName() . 's';
  }

  public function getRoute($action = null)
  {
    return 'sympal_' . ($action ? $this->getPluralLower() . '_' . $action : $this->getPluralLower());
  }
}