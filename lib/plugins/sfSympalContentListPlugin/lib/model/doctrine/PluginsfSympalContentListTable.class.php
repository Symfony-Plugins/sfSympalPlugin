<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PluginsfSympalContentListTable extends sfSympalDoctrineTable
{
  public function getContentQuery($q)
  {
    return $q->leftJoin('cr.ContentType crct');
  }
}