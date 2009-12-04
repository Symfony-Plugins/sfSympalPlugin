<?php

class Basesympal_editorActions extends sfActions
{
  public function executePublish_content(sfWebRequest $request)
  {
    $content = $this->getRoute()->getObject()->publish();
    
    $this->getUser()->setFlash('notice', 'Content published successfully!');
    $this->redirect($request->getReferer());
  }

  public function executeUnpublish_content(sfWebRequest $request)
  {
    $content = $this->getRoute()->getObject()->unpublish();
    
    $this->getUser()->setFlash('notice', 'Content un-published successfully!');
    $this->redirect($request->getReferer());
  }

  public function executeSave_panel_position(sfWebRequest $request)
  {
    $x = $request->getParameter('x');
    $y = $request->getParameter('y');
    $this->getUser()->setAttribute('sympal_editor_panel_x', $x);
    $this->getUser()->setAttribute('sympal_editor_panel_y', $y);

    return sfView::NONE;
  }

  public function executeSave_form_current_tab(sfWebRequest $request)
  {
    if ($request->getParameter('name'))
    {
      $this->getUser()->setAttribute($request->getParameter('name').'.current_form_tab', $request->getParameter('id'), 'admin_module');
    }

    return sfView::NONE;
  }

  public function executeSave_tools_state(sfWebRequest $request)
  {
    $this->getUser()->setAttribute('editor_tools_state', $request->getParameter('state'), 'sympal');

    return sfView::NONE;
  }

  public function executeRevert_data(sfWebRequest $request)
  {
    $version = $this->getRoute()->getObject();

    $this->askConfirmation('Revert to version #'.$version['version'], 'sympal_editor/confirm_revert', array('version' => $version));

    $version->revert();

    $this->getUser()->setFlash('notice', 'Record was successfully reverted back to version #'.$version['version']);

    $this->redirect($request->getParameter('redirect_url'));
  }

  public function executeVersion_history(sfWebRequest $request)
  {
    $type = $request->getParameter('record_type');
    $id = $request->getParameter('record_id');
    $parentType = str_replace('Translation', '', $type);
    Doctrine_Core::initializeModels($parentType);

    $this->record = Doctrine_Core::getTable($type)
      ->createQuery()
      ->andWhere('id = ?', $id)
      ->fetchOne();

    $this->versions = Doctrine_Core::getTable('Version')
      ->createQuery('v')
      ->andWhere('record_type = ?', $type)
      ->andWhere('record_id = ?', $id)
      ->orderBy('v.version ASC')
      ->execute();
  }
}