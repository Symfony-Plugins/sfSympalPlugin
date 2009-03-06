<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/BaseFormFilterDoctrine.class.php');

/**
 * EntityType filter form base class.
 *
 * @package    filters
 * @subpackage EntityType *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BaseEntityTypeFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'           => new sfWidgetFormFilterInput(),
      'label'          => new sfWidgetFormFilterInput(),
      'list_route_url' => new sfWidgetFormFilterInput(),
      'view_route_url' => new sfWidgetFormFilterInput(),
      'layout'         => new sfWidgetFormFilterInput(),
      'slug'           => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'name'           => new sfValidatorPass(array('required' => false)),
      'label'          => new sfValidatorPass(array('required' => false)),
      'list_route_url' => new sfValidatorPass(array('required' => false)),
      'view_route_url' => new sfValidatorPass(array('required' => false)),
      'layout'         => new sfValidatorPass(array('required' => false)),
      'slug'           => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('entity_type_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'EntityType';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'name'           => 'Text',
      'label'          => 'Text',
      'list_route_url' => 'Text',
      'view_route_url' => 'Text',
      'layout'         => 'Text',
      'slug'           => 'Text',
    );
  }
}