<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PluginsfSympalContentTable extends sfSympalDoctrineTable
{
  public function getTypeQuery($typeName, $alias = 'c')
  {
    Doctrine_Core::initializeModels(array($typeName));
    return $this->createQuery($alias)
      ->innerJoin($alias.'.'.$typeName.' cr');
  }

  public function getFullTypeQuery($type, $alias = 'c', $contentTypeId = null)
  {
    if ($type instanceof sfSympalContentType)
    {
      $table = $type->getTable();
      $typeModelName = $type->getName();
    } else {
      $table = Doctrine_Core::getTable($type);
      $typeModelName = $type;
    }

    Doctrine_Core::initializeModels(array($typeModelName));

    $q = $this->getBaseQuery($alias);

    if ($type instanceof sfSympalContentType)
    {
      $contentTypeId = $type->getId();
    }

    if ($contentTypeId)
    {
      $q->innerJoin($alias.'.'.$typeModelName.' cr WITH '.$alias.'.content_type_id = ?', $contentTypeId);
    } else {
      $q->innerJoin($alias.'.'.$typeModelName.' cr');
    }

    if (sfSympalConfig::isI18nEnabled($typeModelName))
    {
      $q->leftJoin('cr.Translation crt');
    }

    if (method_exists($table, 'getContentQuery'))
    {
      $table->getContentQuery($q);
    }

    return $q;
  }

  public function getContent($params = array())
  {
    $request = sfContext::getInstance()->getRequest();
    $contentType = $request->getParameter('sympal_content_type');
    $contentTypeId = $request->getParameter('sympal_content_type_id');
    $contentId = $request->getParameter('sympal_content_id');
    $contentSlug = $request->getParameter('sympal_content_slug');
    $q = $this->getFullTypeQuery($contentType, 'c', $contentTypeId);

    if ($contentId)
    {
      $q->andWhere('c.id = ?', $contentId);
    } else if ($contentSlug) {
      if ($this->hasRelation('Translation') && $this->getRelation('Translation')->getTable()->hasField('slug'))
      {
        $q->andWhere('c.slug = ? OR ct.i18n_slug = ?', array($contentSlug, $contentSlug));
      } else {
        $q->andWhere('c.slug = ?', $contentSlug);
      }
    }

    foreach ($params as $key => $value)
    {
      if ($key == 'slug' && $this->hasRelation('Translation'))
      {
        $q->andWhere('c.slug = ? OR ct.i18n_slug = ?', array($value, $value));
        continue;
      }

      if ($this->hasField($key))
      {
        $q->andWhere('c.'.$key.' = ?', $value);
      } else if ($this->hasRelation('Translation') && $this->getRelation('Translation')->getTable()->hasField($key)) {
        $q->andWhere('ct.'.$key, $value);
      } else if ($this->getRelation($contentType)->getTable()->hasField($key)) {
        $q->andWhere('cr.'.$key.' = ?', $value);
      }
    }

    return $q->fetchOne();
  }

  public function getBaseQuery($alias = 'c')
  {
    $sympalContext = sfSympalContext::getInstance();
    $q = Doctrine_Query::create()
      ->from('sfSympalContent '.$alias)
      ->leftJoin($alias.'.Groups g')
      ->leftJoin($alias.'.Slots s INDEXBY sl.name')
      ->leftJoin($alias.'.MenuItem m')
      ->innerJoin($alias.'.Type t')
      ->innerJoin($alias.'.CreatedBy u')
      ->innerJoin($alias.'.Site si')
      ->andWhere('si.slug = ?', $sympalContext->getSiteSlug());

    $user = sfContext::getInstance()->getUser();

    if (!$user->isEditMode())
    {
      $expr = new Doctrine_Expression('NOW()');
      $q->andWhere($alias.'.date_published <= '.$expr);
    }

    if (sfSympalConfig::isI18nEnabled('sfSympalContentSlot'))
    {
      $q->leftJoin('sl.Translation slt');
    }

    if (sfSympalConfig::isI18nEnabled('sfSympalContent'))
    {
      $q->leftJoin($alias.'.Translation ct');
    }

    if (sfSympalConfig::isI18nEnabled('sfSympalMenuItem'))
    {
      $q->leftJoin('m.Translation mt');
    }

    return $q;
  }

  public function getAdminGenQuery($q)
  {
    $q = Doctrine_Core::getTable('sfSympalContent')
      ->getFullTypeQuery(sfContext::getInstance()->getRequest()->getAttribute('content_type'), 'r');

    return $q;
  }
}