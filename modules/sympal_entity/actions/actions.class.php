<?php

/**
 * sympal_entity actions.
 *
 * @package    sympal
 * @subpackage sympal_entity
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z jwage $
 */
class sympal_entityActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $sympalContext = sfSympalContext::getInstance();
    $this->renderer = $sympalContext->getRenderer($this);
  }
}