<?php

/**
 * PluginContentSlot form.
 *
 * @package    form
 * @subpackage ContentSlot
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginContentSlotForm extends BaseContentSlotForm
{
  public function setup()
  {
    parent::setup();

    unset($this['content_id'], $this['name'], $this['is_column'], $this['render_function']);

    $this->widgetSchema['content_slot_type_id']->setLabel('Slot Type');
    $this->widgetSchema['content_slot_type_id']->setAttribute('onChange', "change_content_slot_type('".$this->object['id']."', this.value)");

    if (isset($this['value']))
    {
      sfSympalFormToolkit::changeContentSlotValueWidget($this->object, $this);
    }
  }

  protected $_breadcrumbs = array();
  protected $_widgets = array();

  public function getBreadcrumbs()
  {
    return get_sympal_breadcrumbs($this->_breadcrumbs);
  }

  protected function _findWidgets($form)
  {
    foreach ($form as $key => $value)
    {
      if ($value->isHidden())
      {
        continue;
      }
      if ($value instanceof sfFormFieldSchema)
      {
        $label = strip_tags($value->renderLabel());
        $this->_breadcrumbs[$label] = null;

        foreach ($value as $k => $v)
        {
          if ($v instanceof sfFormFieldSchema)
          {
            $label = strip_tags($v->renderLabel());
            $this->_breadcrumbs[$label] = null;

            $this->_findWidgets($v);
          } else {
            $this->_widgets[] = $v;
          }
        }
      } else {
        $this->_widgets[] = $value;
      }
    }
    return $this->_widgets;
  }

  public function renderSlotForm()
  {
    $class = get_class($this->object);
    $this->_breadcrumbs[$class] = null;

    $widgets = $this->_findWidgets($this);

    $return = '';

    if ($this->hasGlobalErrors())
    {
      $return .= $this->renderGlobalErrors();
    }

    $return .= $this->renderHiddenFields();
    $return .= '<table>';
    foreach ($this->_widgets as $widget)
    {
      $return .= $widget->renderRow();
    }
    $return .= '</table>';

    return $return;
  }

  public function __toString()
  {
    return '<table>'.parent::__toString().'</table>';
  }
}