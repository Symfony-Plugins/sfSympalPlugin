<?php

/**
 * BaseEntityGroup
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $entity_id
 * @property integer $group_id
 * @property Entity $Entity
 * @property sfGuardGroup $Group
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 5441 2009-01-30 22:58:43Z jwage $
 */
abstract class BaseEntityGroup extends sfSympalDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('entity_group');
        $this->hasColumn('entity_id', 'integer', 4, array('primary' => true, 'type' => 'integer', 'length' => '4'));
        $this->hasColumn('group_id', 'integer', 4, array('primary' => true, 'type' => 'integer', 'length' => '4'));
    }

    public function setUp()
    {
        $this->hasOne('Entity', array('local' => 'entity_id',
                                      'foreign' => 'id',
                                      'onDelete' => 'CASCADE'));

        $this->hasOne('sfGuardGroup as Group', array('local' => 'group_id',
                                                     'foreign' => 'id',
                                                     'onDelete' => 'CASCADE'));
    }
}