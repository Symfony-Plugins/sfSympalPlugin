<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseEntity extends sfSympalDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('entity');
        $this->hasColumn('id', 'integer', 4, array('type' => 'integer', 'primary' => true, 'autoincrement' => true, 'length' => '4'));
        $this->hasColumn('site_id', 'integer', 4, array('type' => 'integer', 'notnull' => true, 'length' => '4'));
        $this->hasColumn('entity_type_id', 'integer', 4, array('type' => 'integer', 'notnull' => true, 'length' => '4'));
        $this->hasColumn('entity_template_id', 'integer', 4, array('type' => 'integer', 'length' => '4'));
        $this->hasColumn('master_menu_item_id', 'integer', 4, array('type' => 'integer', 'length' => '4'));
        $this->hasColumn('last_updated_by', 'integer', 4, array('type' => 'integer', 'length' => '4'));
        $this->hasColumn('created_by', 'integer', 4, array('type' => 'integer', 'length' => '4'));
        $this->hasColumn('locked_by', 'integer', 4, array('type' => 'integer', 'length' => '4'));
        $this->hasColumn('is_published', 'boolean', null, array('type' => 'boolean'));
        $this->hasColumn('date_published', 'timestamp', null, array('type' => 'timestamp'));
        $this->hasColumn('custom_path', 'string', 255, array('type' => 'string', 'length' => '255'));
        $this->hasColumn('layout', 'string', 255, array('type' => 'string', 'length' => '255'));
    }

    public function setUp()
    {
        $this->hasOne('MenuItem as MasterMenuItem', array('local' => 'master_menu_item_id',
                                                          'foreign' => 'id',
                                                          'onDelete' => 'CASCADE'));

        $this->hasOne('sfGuardUser as LastUpdatedBy', array('local' => 'last_updated_by',
                                                            'foreign' => 'id',
                                                            'onDelete' => 'SET NULL'));

        $this->hasOne('sfGuardUser as CreatedBy', array('local' => 'created_by',
                                                        'foreign' => 'id',
                                                        'onDelete' => 'SET NULL'));

        $this->hasOne('sfGuardUser as LockedBy', array('local' => 'locked_by',
                                                       'foreign' => 'id',
                                                       'onDelete' => 'SET NULL'));

        $this->hasOne('Site', array('local' => 'site_id',
                                    'foreign' => 'id',
                                    'onDelete' => 'CASCADE'));

        $this->hasOne('EntityType as Type', array('local' => 'entity_type_id',
                                                  'foreign' => 'id',
                                                  'onDelete' => 'CASCADE'));

        $this->hasOne('EntityTemplate as Template', array('local' => 'entity_template_id',
                                                          'foreign' => 'id',
                                                          'onDelete' => 'SET NULL'));

        $this->hasMany('sfGuardGroup as Groups', array('refClass' => 'EntityGroup',
                                                       'local' => 'entity_id',
                                                       'foreign' => 'group_id'));

        $this->hasMany('sfGuardPermission as Permissions', array('refClass' => 'EntityPermission',
                                                                 'local' => 'entity_id',
                                                                 'foreign' => 'permission_id'));

        $this->hasOne('MenuItem', array('local' => 'id',
                                        'foreign' => 'entity_id'));

        $this->hasOne('Page', array('local' => 'id',
                                    'foreign' => 'entity_id'));

        $this->hasMany('EntitySlot as Slots', array('local' => 'id',
                                                    'foreign' => 'entity_id'));

        $this->hasMany('EntityGroup as EntityGroups', array('local' => 'id',
                                                            'foreign' => 'entity_id'));

        $sluggable0 = new Doctrine_Template_Sluggable();
        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($sluggable0);
        $this->actAs($timestampable0);
    }
}