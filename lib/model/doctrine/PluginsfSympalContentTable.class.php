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
      $q->innerJoin($alias.'.'.$typeModelName.' cr WITH '.$alias.'.content_type_id = '.$contentTypeId);
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

  public function getContentRecordsTypeBy($by, $value)
  {
    return Doctrine_Core::getTable('sfSympalContentType')
      ->createQuery('t')
      ->innerJoin('t.Content c')
      ->where('c.'.$by.' = ?', $value)
      ->fetchOne();
  }

  public function getContent($params = array())
  {
    $request = sfContext::getInstance()->getRequest();
    $contentType = $request->getParameter('sympal_content_type');
    $contentTypeId = $request->getParameter('sympal_content_type_id');
    $contentId = $request->getParameter('sympal_content_id');
    $contentSlug = $request->getParameter('sympal_content_slug');

    if (($contentId || $contentSlug) && !$contentType && !$contentTypeId)
    {
      if ($contentId)
      {
        $type = $this->getContentRecordsTypeBy('id', $contentId);
      } else if ($contentSlug) {
        $type = $this->getContentRecordsTypeBy('slug', $contentSlug);
      }

      if ($type)
      {
        $contentType = $type->getName();
        $contentTypeId = $type->getId();
      } else {
        return false;
      }
    }
    $q = $this->getFullTypeQuery($contentType, 'c', $contentTypeId);

    // If we have an explicit content id
    if ($contentId)
    {
      $q->andWhere('c.id = ?', $contentId);

    // If we have an explicit content slug
    } else if ($contentSlug) {
      if ($this->hasRelation('Translation') && $this->getRelation('Translation')->getTable()->hasField('slug'))
      {
        $q->andWhere('c.slug = ? OR ct.i18n_slug = ?', array($contentSlug, $contentSlug));
      } else {
        $q->andWhere('c.slug = ?', $contentSlug);
      }

    // Try and find the content record based on the params in the route
    } else {
      // Loop over all other request parameters and see if they can be used to add a where condition
      // to find the content record
      $paramFound = false;
      foreach ($params as $key => $value)
      {
        if ($key == 'slug' && $this->hasRelation('Translation'))
        {
          $paramFound = true;
          $q->andWhere('c.slug = ? OR ct.i18n_slug = ?', array($value, $value));
          continue;
        }

        if ($this->hasField($key))
        {
          $paramFound = true;
          $q->andWhere('c.'.$key.' = ?', $value);
        }
        else if ($this->hasRelation('Translation') && $this->getRelation('Translation')->getTable()->hasField($key))
        {
          $paramFound = true;
          $q->andWhere('ct.'.$key, $value);
        }
        else if ($this->getRelation($contentType)->getTable()->hasField($key))
        {
          $paramFound = true;
          $q->andWhere('cr.'.$key.' = ?', $value);
        }
      }

      // If no params were found to add a condition on lets find where slug = action_name
      if (!$paramFound)
      {
        $q->andWhere('c.slug = ?', $request->getParameter('action'));
      }
    }

    $q->enableSympalResultCache('sympal_get_content');

    return $q->fetchOne();
  }

  public function getBaseQuery($alias = 'c')
  {
    $sympalContext = sfSympalContext::getInstance();
    $q = Doctrine_Query::create()
      ->from('sfSympalContent '.$alias)
      ->leftJoin($alias.'.Groups g')
      ->leftJoin('g.Permissions gp')
      ->leftJoin($alias.'.EditGroups eg')
      ->leftJoin('eg.Permissions egp')
      ->leftJoin($alias.'.Slots s')
      ->leftJoin($alias.'.MenuItem m')
      ->leftJoin($alias.'.Links l')
      ->leftJoin('l.Type lt')
      ->leftJoin($alias.'.Assets a')
      ->leftJoin($alias.'.CreatedBy u')
      ->innerJoin($alias.'.Type t')
      ->innerJoin($alias.'.Site si')
      // Don't use param to work around Doctrine pgsql bug
      // with limit subquery and number of params
      ->andWhere(sprintf("si.slug = '%s'", $sympalContext->getSiteSlug()))
      ->orderBy('l.slug ASC, a.slug ASC');

    $user = sfContext::getInstance()->getUser();

    if (!$user->isEditMode())
    {
      $q = $this->addPublishedQuery($alias, $q);
    }

    if (sfSympalConfig::isI18nEnabled('sfSympalContentSlot'))
    {
      $q->leftJoin('s.Translation slt');
    }

    if (sfSympalConfig::isI18nEnabled('sfSympalContent'))
    {
      $q->leftJoin($alias.'.Translation ct');
    }

    return $q;
  }

  public function getAdminGenQuery($q)
  {
    $q = Doctrine_Core::getTable('sfSympalContent')
      ->getFullTypeQuery(sfContext::getInstance()->getRequest()->getAttribute('content_type'), 'r');

    return $q;
  }
  
  /**
   * Adds the necessary where clause to only return published content
   * 
   * @param string          $alias  The alias to use to refer to sfSympalContent
   * @param Doctrine_Query  $q      An optional query to add to
   */
  public function addPublishedQuery($alias = 'c', Doctrine_Query $q = null)
  {
    if ($q === null)
    {
      $q = $this->createQuery($alias);
    }
    
    $expr = new Doctrine_Expression('NOW()');
    $q->andWhere($alias.'.date_published <= '.$expr);
    
    return $q;
  }
}