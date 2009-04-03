<?php

/**
 * Base actions for the sfSympalPlugin sympal_yui_image_uploader module.
 * 
 * @package     sfSympalPlugin
 * @subpackage  sympal_yui_image_uploader
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_yui_image_uploaderActions extends sfActions
{
  public function executeUpload(sfWebRequest $request)
  {
    sfConfig::set('sf_web_debug', false);

    $form = new sfSympalYuiImageUploadForm();
    $form->bind(array(), $request->getFiles());
    $savedPath = $form->save();
    $info = pathinfo($savedPath);
    $name = $info['basename'];

    $dirName = sfSympalConfig::get('yui_image_upload_dir', null, 'yui_images');
    $url = $request->getUriPrefix().$request->getRelativeUrlRoot().'/uploads/'.$dirName.'/'.$name;
    $message = "{status:'UPLOADED', image_url:'".$url."'}";

    return $this->renderText($message);
  }
}