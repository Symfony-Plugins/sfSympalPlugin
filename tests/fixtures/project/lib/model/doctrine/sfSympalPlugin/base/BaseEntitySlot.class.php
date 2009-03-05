<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseEntitySlot extends sfSympalDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('entity_slot');
        $this->hasColumn('id', 'integer', 4, array('type' => 'integer', 'primary' => true, 'autoincrement' => true, 'length' => '4'));
        $this->hasColumn('entity_id', 'integer', 4, array('type' => 'integer', 'notnull' => true, 'length' => '4'));
        $this->hasColumn('entity_slot_type_id', 'integer', 4, array('type' => 'integer', 'notnull' => true, 'length' => '4'));
        $this->hasColumn('name', 'string', 255, array('type' => 'string', 'notnull' => true, 'length' => '255'));
        $this->hasColumn('value', 'clob', null, array('type' => 'clob'));
    }

    public function setUp()
    {
        $this->hasOne('Entity as RelatedEntity', array('local' => 'entity_id',
                                                       'foreign' => 'id',
                                                       'onDelete' => 'CASCADE'));

        $this->hasOne('EntitySlotType as Type', array('local' => 'entity_slot_type_id',
                                                      'foreign' => 'id',
                                                      'onDelete' => 'CASCADE'));

        $i18n0 = new Doctrine_Template_I18n(array('fields' => array(0 => 'value')));
        $this->actAs($i18n0);
    }
}