<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginComment extends BaseComment
{
  public function getAuthorName()
  {
    if ($this->user_id)
    {
      return $this->getAuthor()->getusername();
    } else {
      return $this->name;
    }
  }
}