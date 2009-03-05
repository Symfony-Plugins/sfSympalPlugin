<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginSite extends BaseSite
{
  public function setTitle($title)
  {
    if ($title)
    {
      $result = Doctrine_Query::create()
        ->from('Site s')
        ->where('s.title = ?', $title)
        ->fetchArray();

      if ($result)
      {
        $this->assignIdentifier($result[0]['id']);
        $this->hydrate($result[0]);
      } else {
        $this->_set('title', $title);
      }
    }
  }
}