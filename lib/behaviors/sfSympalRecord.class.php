<?php

class sfSympalRecord extends Doctrine_Template
{
  protected $_eventName;

  public function setInvoker(Doctrine_Record_Abstract $invoker)
  {
    parent::setInvoker($invoker);
    $this->_eventName = sfInflector::tableize(get_class($invoker));
  }

  public function setTableDefinition()
  {
    $this->_table->unshiftFilter(new sfSympalRecordEventFilter());

    if ($this->isSluggable())
    {
      $this->sympalActAs('Doctrine_Template_Sluggable', $this->getSluggableOptions());
    }

    if ($this->isVersioned())
    {
      $this->sympalActAs('sfSympalVersionable');
    }

    if ($this->isI18ned())
    {
      $this->sympalActAs('sfSympalI18n', array('fields' => $this->getI18nedFields()), 'Doctrine_Template_I18n');
    }

    if ($this->isContent())
    {
      $this->sympalActAs('sfSympalContentTemplate');
    }

    sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this->getInvoker(), 'sympal.'.$this->_eventName.'.set_table_definition', array('object' => $this)));
  }

  public function sympalActAs($tpl, $options = array(), $name = null)
  {
    if (is_string($tpl))
    {
      $tpl = new $tpl($options);
    }

    if (is_null($name))
    {
      $name = get_class($tpl);
    }

    $this->_table->addTemplate($name, $tpl);

    $tpl->setInvoker($this->getInvoker());
    $tpl->setTable($this->_table);
    $tpl->setUp();
    $tpl->setTableDefinition();
  }

  public function setUp()
  {
    sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this->getInvoker(), 'sympal.'.$this->_eventName.'.setup', array('object' => $this)));
  }

  public function isI18ned()
  {
    $i18nedModels = sfSympalConfig::get('internationalized_models', null, array());
    return sfSympalConfig::get('i18n') && isset($i18nedModels[$this->_table->getOption('name')]);
  }

  public function getI18nedFields()
  {
    if ($this->isI18ned())
    {
      $i18nedModels = sfSympalConfig::get('internationalized_models', null, array());
      return $i18nedModels[$this->_table->getOption('name')];
    } else {
      return array();
    }
  }

  public function isSluggable()
  {
    $sluggableModels = sfSympalConfig::get('sluggable_models', null, array());
    return array_key_exists($this->_table->getOption('name'), $sluggableModels);
  }

  public function getSluggableOptions()
  {
    if ($this->isSluggable())
    {
      $sluggableModels = sfSympalConfig::get('sluggable_models', null, array());
      return $sluggableModels[$this->_table->getOption('name')] ? $sluggableModels[$this->_table->getOption('name')]:array();
    } else {
      return array();
    }
  }

  public function isVersioned()
  {
    $versionedModels = sfSympalConfig::get('versioned_models', null, array());
    return isset($versionedModels[$this->_table->getOption('name')]);
  }

  public function isContent()
  {
    return $this->_table->getOption('name') == 'Content' ? true:false;
  }

  public function __call($method, $arguments)
  {
    return sfSympalExtendClass::extendEvent($this->getInvoker(), $method, $arguments);
  }
}

class sfSympalContentTemplate extends Doctrine_Template
{
  public function setTableDefinition()
  {
    $this->_table->unshiftFilter(new sfSympalContentFilter());
  }

  public function __call($method, $arguments)
  {
    try {
      return call_user_func_array(array($this->getInvoker()->getRecord(), $method), $arguments);
    } catch (Exception $e) {
      return null;
    }
  }
}

class sfSympalContentFilter extends Doctrine_Record_Filter
{
  protected $_i18nFilter;

  public function init()
  {
    $this->_i18nFilter = new sfDoctrineRecordI18nFilter();
    $this->_i18nFilter->setTable($this->getTable());
    $this->_i18nFilter->init();
  }

  public function filterSet(Doctrine_Record $record, $name, $value)
  {
    try {
      return $this->_i18nFilter->filterSet($record, $name, $value);
    } catch (Exception $e) {}

    try {
      if ($record->getRecord())
      {
        $record->getRecord()->$name = $value;
        return $record;
      }
    } catch (Exception $e) {}

    throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
  }

  public function filterGet(Doctrine_Record $record, $name)
  {
    try {
      return $this->_i18nFilter->filterGet($record, $name);
    } catch (Exception $e) {}

    try {
      if ($record->getRecord())
      {
        return $record->getRecord()->$name;
      }
    } catch (Exception $e) {}

    throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
  }
}