<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginsfSympalContentSlot extends BasesfSympalContentSlot
{
  protected
    $_contentRenderedFor,
    $_rendered;

  public function setContentRenderedFor(sfSympalContent $content)
  {
    $this->_contentRenderedFor = $content;
  }

  public function getContentRenderedFor()
  {
    return $this->_contentRenderedFor;
  }

  public function resetRenderCache()
  {
    $this->_rendered = null;
  }

  public function postUpdate($event)
  {
    if ($this->_contentRenderedFor)
    {
      $this->_contentRenderedFor->deleteLinkAndAssetReferences();
    }
  }

  /**
   * Returns the "resource" used to render the form fields for a slog.
   * 
   * A resource is a module/action that can represent a component or
   * a partial
   * 
   * @return string
   */
  public function getSlotEditFormRenderer()
  {
    $contentSlotTypes = sfSympalConfig::get('content_slot_types');
    return isset($contentSlotTypes[$this->type]['form_renderer']) ? $contentSlotTypes[$this->type]['form_renderer'] : sfSympalConfig::get('inline_editing', 'default_form_renderer', 'sympal_edit_slot/slot_editor_renderer');
  }

  /**
   * Retrieves the form class that will be used to edit this slot
   * 
   * @return sfForm
   */
  public function getEditForm()
  {
    $contentSlotTypes = sfSympalConfig::get('content_slot_types');
    $className = isset($contentSlotTypes[$this->type]['form']) ? $contentSlotTypes[$this->type]['form'] : sfSympalConfig::get('inline_editing', 'default_slot_form', 'sfSympalInlineEditContentSlotForm');
    
    $form = new $className($this);
    $form->setDefault('value', $this->getRawValue());
    $form->getWidgetSchema()->setNameFormat('sf_sympal_content_slot_'.$this->id.'[%s]');

    return $form;
  }
  
  /**
   * Retrieves the form class used to edit slot for "column slots".
   * 
   * This is used by sfSympalFormToolkit::changeContentSlotValueWidget()
   * to extract the widget & validator out so we can put it into the 
   * content slot form
   * 
   * @return sfForm
   */
  public function getContentSlotColumnForm()
  {
    $content = $this->getContentRenderedFor();
    $contentTable = $content->getTable();

    if ($contentTable->hasField($this->name))
    {
      $formClass = sfSympalConfig::get('inline_editing', 'default_column_form');
      $form = new $formClass($content);
      $form->useFields(array($this->name));
    }

    if (sfSympalConfig::isI18nEnabled('sfSympalContent'))
    {
      $contentTranslationTable = Doctrine::getTable('sfSympalContentTranslation');
      if ($contentTranslationTable->hasField($this->name))
      {
        $formClass = sfSympalConfig::get('inline_editing', 'default_column_form');
        $form = new $formClass($content);
        $form->useFields(array(sfContext::getInstance()->getUser()->getEditCulture()));
      }      
    }

    $contentTypeClassName = $content->getContentTypeClassName();
    $contentTypeFormClassName = sfSympalConfig::get($contentTypeClassName, 'default_inline_editing_column_form', $contentTypeClassName.'Form');
    $contentTypeTable = Doctrine_Core::getTable($contentTypeClassName);
    if ($contentTypeTable->hasField($this->name))
    {
      $form = new $contentTypeFormClassName($content->getRecord());
      $form->useFields(array($this->name));
    }

    if (sfSympalConfig::isI18nEnabled($contentTypeClassName))
    {
      $contentTypeTranslationClassName = $contentTypeClassName.'Translation';
      $contentTypeTranslationFormClassName = sfSympalConfig::get($contentTypeTranslationClassName, 'default_inline_editing_column_form', $contentTypeTranslationClassName.'Form');
      $contentTypeTranslationTable = Doctrine_Core::getTable($contentTypeTranslationClassName);
      if ($contentTypeTranslationTable->hasField($this->name))
      {
        $form = new $contentTypeFormClassName($content->getRecord()); 
        $i18nForm = $form->getEmbeddedForm($language = sfContext::getInstance()->getUser()->getEditCulture()); 
        $i18nForm->useFields(array($this->name)); 
        unset($form[$language]); 
        $form->embedForm($language, $i18nForm); 
        $form->useFields(array($language)); 
      }
    }

    if (!$form)
    {
      throw new InvalidArgumentException('Invalid content slot');
    }

    return $form;
  }
  /**
   * Renders this slot, which uses the slot's renderer class and runs
   * it through the transformers
   * 
   * @return string
   */
  public function render()
  {
    if (!$this->_rendered)
    {
      $slotTypeConfig = sfSympalConfig::get('content_slot_types', $this->type, array());
      $rendererClass = isset($slotTypeConfig['renderer']) ? $slotTypeConfig['renderer'] : 'sfSympalContentSlotTransformer';
      
      $renderer = new $rendererClass($this);
      $this->_rendered = $renderer->render($this->getRawValue());
    }

    return $this->_rendered;
  }
  
  /**
   * Retrieves the value that should be used for rendering and transforming.
   * 
   * Looks for a getXXXSlotValue() method (XXX is the camelized name of
   * the slot) in the following locations:
   *  1) The content type object (e.g. sfSympalPage)
   *  2) The sfSympalContent object
   * 
   * If the method doesn't exist in either place, the raw value is returned.
   * 
   * For example, I might want to transform the created_at_id column value
   * to an actual username. This checks for the hook which would do that.
   * 
   */
  public function getValueForRendering()
  {
    $method = sprintf('get%sSlotValue', sfInflector::camelize($this->name));
    
    if (method_exists($this->getContentRenderedFor()->getRecord(), $method))
    {
      return $this->getContentRenderedFor()->getRecord()->$method($this);
    }
    elseif (method_exists($this->getContentRenderedFor(), $method))
    {
      return $this->getContentRenderedFor()->$method($this);
    }
    
    return $this->getRawValue();
  }
  
  /**
   * Returns an instance of the renderer class for this content slot
   * 
   * @return sfSympalContentSlotRenderer
   */
  public function getSlotRenderer()
  {
    $contentSlotTypes = sfSympalConfig::get('content_slot_types');
    $className = isset($contentSlotTypes[$this->type]['renderer']) ? $contentSlotTypes[$this->type]['renderer'] : 'sfSympalContentSlotRenderer';
    
    return new $className($this);
  }

  public function setValue($value)
  {
    $this->_rendered = null;
    
    if ($this->is_column)
    {
      $name = $this->name;

      return $this->_contentRenderedFor->$name = $value;
    }
    else
    {
      return $this->_set('value', $value);
    }
  }

  public function getRawValue()
  {
    if ($this->is_column)
    {
      return $this->_contentRenderedFor->get($this->name);
    }
    else
    {
      return $this->getValue();
    }
  }

  public function hasValue()
  {
    return trim(strip_tags($this->render()));
  }

  public function save(Doctrine_Connection $conn = null)
  {
    $result = parent::save($conn);

    // When a slot is saved and we have some content set lets update the search index
    if ($this->_contentRenderedFor)
    {
      sfSympalSearch::getInstance()->updateSearchIndex($this->_contentRenderedFor);
    }

    return $result;
  }
}