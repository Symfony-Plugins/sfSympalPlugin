<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseMenuItem extends sfSympalDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('menu_item');
        $this->hasColumn('site_id', 'integer', null, array('type' => 'integer', 'notnull' => true));
        $this->hasColumn('content_type_id', 'integer', null, array('type' => 'integer'));
        $this->hasColumn('content_id', 'integer', null, array('type' => 'integer'));
        $this->hasColumn('name', 'string', 255, array('type' => 'string', 'notnull' => true, 'length' => '255'));
        $this->hasColumn('label', 'string', 255, array('type' => 'string', 'length' => '255'));
        $this->hasColumn('custom_path', 'string', 255, array('type' => 'string', 'length' => '255'));
        $this->hasColumn('is_content_type_list', 'boolean', null, array('type' => 'boolean', 'default' => false));
        $this->hasColumn('requires_auth', 'boolean', null, array('type' => 'boolean'));
        $this->hasColumn('requires_no_auth', 'boolean', null, array('type' => 'boolean'));
        $this->hasColumn('is_primary', 'boolean', null, array('type' => 'boolean'));
        $this->hasColumn('is_published', 'boolean', null, array('type' => 'boolean'));
        $this->hasColumn('date_published', 'timestamp', null, array('type' => 'timestamp'));
    }

    public function setUp()
    {
        $this->hasOne('Content as RelatedContent', array('local' => 'content_id',
                                                         'foreign' => 'id'));

        $this->hasOne('Site', array('local' => 'site_id',
                                    'foreign' => 'id',
                                    'onDelete' => 'CASCADE'));

        $this->hasOne('ContentType', array('local' => 'content_type_id',
                                           'foreign' => 'id',
                                           'onDelete' => 'CASCADE'));

        $this->hasMany('Group as Groups', array('refClass' => 'MenuItemGroup',
                                                'local' => 'menu_item_id',
                                                'foreign' => 'group_id'));

        $this->hasMany('Permission as Permissions', array('refClass' => 'MenuItemPermission',
                                                          'local' => 'menu_item_id',
                                                          'foreign' => 'permission_id'));

        $this->hasMany('MenuItemGroup as MenuItemGroups', array('local' => 'id',
                                                                'foreign' => 'menu_item_id'));

        $this->hasMany('MenuItemPermission as MenuItemPermissions', array('local' => 'id',
                                                                          'foreign' => 'menu_item_id'));

        $this->hasOne('Content as MasterContent', array('local' => 'id',
                                                        'foreign' => 'master_menu_item_id'));

        $sluggable0 = new Doctrine_Template_Sluggable(array('fields' => array(0 => 'name'), 'unique' => true));
        $nestedset0 = new Doctrine_Template_NestedSet(array('hasManyRoots' => true, 'rootColumnName' => 'root_id'));
        $i18n0 = new Doctrine_Template_I18n(array('fields' => array(0 => 'label')));
        $this->actAs($sluggable0);
        $this->actAs($nestedset0);
        $this->actAs($i18n0);
    }
}