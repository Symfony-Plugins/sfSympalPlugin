<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PluginContentTypeTable extends Doctrine_Table
{
  protected $_findAllResults;

  public function findAll($hydrationMode = null)
  {
    if (!$this->_findAllResults)
    {
      $this->_findAllResults = $this->createQuery('dctrn_find')
        ->orderBy('name ASC')
        ->execute(array(), $hydrationMode);
    }
    return $this->_findAllResults;
  }
}