<?php
class sfSympalContentRenderer
{
  protected
    $_dispatcher,
    $_configuration,
    $_menuItem,
    $_content,
    $_format = 'html';

  public function __construct(MenuItem $menuItem, $format = 'html')
  {
    $this->_configuration = sfProjectConfiguration::getActive();
    $this->_dispatcher = $this->_configuration->getEventDispatcher();
    $this->_configuration->loadHelpers(array('Tag', 'Url', 'Partial'));

    $this->_menuItem = $menuItem;
    $this->_format = $format ? $format:'html';
  }

  public function getMenuItem()
  {
    return $this->_menuItem;
  }

  public function getContent()
  {
    return $this->_content;
  }

  public function setContent($content)
  {
    $this->_content = $content;
  }

  public function getFormat()
  {
    return $this->_format;
  }

  public function setFormat($format)
  {
    $this->_format = $format;
  }

  public function initialize()
  {
    $context = sfContext::getInstance();
    $request = $context->getResponse();
    $response = $context->getResponse();

    sfSympalToolkit::setCurrentMenuItem($this->_menuItem);

    sfSympalToolkit::setCurrentContent($this->_content);
    sfSympalToolkit::changeLayout($this->_content->getLayout());

    if (!$response->getTitle())
    {
      $title = $this->_menuItem->getBreadcrumbs()->getPathAsString();
      $title = $title instanceof sfOutputEscaper ? $title->getRawValue():$title;
      $title = $title ? $this->_menuItem->Site->title.sfSympalConfig::get('breadcrumbs_separator', null, ' / ').$title:$this->_menuItem->Site->title;
      $response->setTitle($title);
    }
  }

  public function render()
  {
    $menuItem = $this->_menuItem;
    $content = $this->_content;
    $format = $this->_format;

    $typeVarName = strtolower($content['Type']['name'][0]).substr($content['Type']['name'], 1, strlen($content['Type']['name']));

    $variables = array(
      'format' => $format,
      'content' => $content,
      'menuItem' => $menuItem,
      $typeVarName => $content->getRecord(),
      'contentRecord' => $content->getRecord()
    );

    $event = $this->_dispatcher->filter(new sfEvent($this, 'sympal.content_renderer.filter_variables'), $variables);
    $variables = $event->getReturnValue();

    $return = null;

    if ($format == 'html')
    {
      $return = $this->_getContentViewHtml($content, $variables);
    } else {
      switch ($format)
      {
        case 'xml':
        case 'json':
        case 'yml':
          $return = $content->exportTo($format, true);
        default:
          $event = $this->_dispatcher->notifyUntil(new sfEvent($this, 'sympal.content_renderer.unknown_format', $variables));

          if ($event->isProcessed())
          {
            $this->setFormat($event['format']);
            $return = $event->getReturnValue();
          }
      }
    }
    
    if (!$return)
    {
      $this->_throwUnknownFormat404($this->_format);
    }

    return $return;
  }

  protected function _getContentViewHtml(Content $content, $variables = array())
  {
    if ($content->content_template_id)
    {
      $template = $content->getTemplate();
    } else {
      $template = $content->getType()->getTemplate();
    }

    $eventName = sfInflector::tableize($content->getType()->getName());

    $this->_dispatcher->notify(new sfEvent($this, 'sympal.pre_render_'.$eventName.'_content', array('content' => $content, 'template' => $template)));

    if ($template && $partialPath = $template->getPartialPath())
    {
      $return = get_partial($partialPath, $variables);
    }
    else if ($template && $componentPath = $template->getComponentPath())
    {
      list($module, $action) = explode('/', $componentPath);
      $return = get_component($module, $action, $variables);
    }
    else if ($template && $body = $template->getBody())
    {
      $return = sfSympalToolkit::processTemplate($body, $variables);;
    } else {
      $return = get_sympal_breadcrumbs($this->_menuItem, $content).$this->_renderDoctrineData($content);
    }

    $this->_dispatcher->notify(new sfEvent($this, 'sympal.post_render_'.$eventName.'_content', array('content' => $content, 'template' => $template)));

    $event = $this->_dispatcher->filter(new sfEvent($this, 'sympal.filter_'.$eventName.'_content'), $return);
    $return = $event->getReturnValue();

    return $return;
  }

  protected function _renderDoctrineData($content)
  {
    $html  = '<h1>Content Data</h1>';
    $html .= $this->_renderData($content->toArray(), false);

    $html .= '<h1>'.get_class($content->getRecord()).' Data</h1>';
    $html .= $this->_renderData($content->getRecord()->toArray(), false);

    $html .= '<h1>Slots</h1>';
    $html .= '<table>';
    foreach ($content->getSlots() as $key => $slot)
    {
      $html .= '<tr><th>'.$key.'</th><td>'.get_sympal_content_slot($content, $slot['name']).'</td></tr>';
    }
    $html .= '</table>';

    return $html;
  }

  protected function _renderData(array $content, $deep = true)
  {
    $html  = '';
    $html .= '<table>';  
    foreach ($content as $key => $value)
    {
      if (strstr($key, '_id'))
      {
        continue;
      }
      $val = null;
      if (is_array($value) && $deep)
      {
        $val = '<td>' . $this->_renderData($value) . '</td>';
      } else if (!is_array($value)) {
        $val = '<td>' . $value . '</td>';
      }
      if (isset($val) && $val)
      {
        $html .= '<tr>';
        $html .= '<th>' . Doctrine_Inflector::classify(str_replace('_id', '', $key)) . '</th>';
        $html .= $val;
        $html .= '</tr>';
      }
    }
    $html .= '</table>';
    return $html;
  }

  protected function _throwUnknownFormat404($format)
  {
    sfContext::getInstance()->getController()->getActionStack()->getLastEntry()->getActionInstance()->forward404();
  }

  public function __call($method, $arguments)
  {
    return sfSympalExtendClass::extendEvent($this, $method, $arguments);
  }
}