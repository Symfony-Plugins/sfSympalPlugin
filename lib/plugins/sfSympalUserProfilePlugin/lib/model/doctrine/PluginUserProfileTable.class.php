<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PluginUserProfileTable extends Doctrine_Table
{
  public function getEntityQuery()
  {
    $q = Doctrine::getTable('Entity')->getBaseQuery()
      ->innerJoin('e.UserProfile p')
      ->innerJoin('p.User u');

    return $q;
  }
}