<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseMenuItem extends sfSympalDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('menu_item');
        $this->hasColumn('id', 'integer', 4, array('type' => 'integer', 'primary' => true, 'autoincrement' => true, 'length' => '4'));
        $this->hasColumn('site_id', 'integer', 4, array('type' => 'integer', 'notnull' => true, 'length' => '4'));
        $this->hasColumn('entity_type_id', 'integer', 4, array('type' => 'integer', 'length' => '4'));
        $this->hasColumn('entity_id', 'integer', 4, array('type' => 'integer', 'length' => '4'));
        $this->hasColumn('name', 'string', 255, array('type' => 'string', 'notnull' => true, 'length' => '255'));
        $this->hasColumn('label', 'string', 255, array('type' => 'string', 'length' => '255'));
        $this->hasColumn('route', 'string', 255, array('type' => 'string', 'length' => '255'));
        $this->hasColumn('has_many_entities', 'boolean', null, array('type' => 'boolean', 'default' => false));
        $this->hasColumn('requires_auth', 'boolean', null, array('type' => 'boolean'));
        $this->hasColumn('requires_no_auth', 'boolean', null, array('type' => 'boolean'));
        $this->hasColumn('is_primary', 'boolean', null, array('type' => 'boolean'));
        $this->hasColumn('is_published', 'boolean', null, array('type' => 'boolean'));
        $this->hasColumn('date_published', 'timestamp', null, array('type' => 'timestamp'));
    }

    public function setUp()
    {
        $this->hasOne('Entity as RelatedEntity', array('local' => 'entity_id',
                                                       'foreign' => 'id'));

        $this->hasOne('Site', array('local' => 'site_id',
                                    'foreign' => 'id',
                                    'onDelete' => 'CASCADE'));

        $this->hasOne('EntityType', array('local' => 'entity_type_id',
                                          'foreign' => 'id',
                                          'onDelete' => 'CASCADE'));

        $this->hasMany('sfGuardGroup as Groups', array('refClass' => 'MenuItemGroup',
                                                       'local' => 'menu_item_id',
                                                       'foreign' => 'group_id'));

        $this->hasMany('sfGuardPermission as Permissions', array('refClass' => 'MenuItemPermission',
                                                                 'local' => 'menu_item_id',
                                                                 'foreign' => 'permission_id'));

        $this->hasMany('MenuItemGroup as MenuItemGroups', array('local' => 'id',
                                                                'foreign' => 'menu_item_id'));

        $this->hasMany('MenuItemPermission as MenuItemPermissions', array('local' => 'id',
                                                                          'foreign' => 'menu_item_id'));

        $this->hasOne('Entity as MasterEntity', array('local' => 'id',
                                                      'foreign' => 'master_menu_item_id'));

        $sluggable0 = new Doctrine_Template_Sluggable(array('fields' => array(0 => 'name'), 'unique' => true));
        $nestedset0 = new Doctrine_Template_NestedSet(array('hasManyRoots' => true, 'rootColumnName' => 'root_id'));
        $i18n0 = new Doctrine_Template_I18n(array('fields' => array(0 => 'label')));
        $this->actAs($sluggable0);
        $this->actAs($nestedset0);
        $this->actAs($i18n0);
    }
}