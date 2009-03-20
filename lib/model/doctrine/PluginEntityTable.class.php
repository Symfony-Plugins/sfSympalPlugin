<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PluginEntityTable extends Doctrine_Table
{
  public function getTypeQuery($params)
  {
    $request = sfContext::getInstance()->getRequest();

    if (is_string($params))
    {
      $typeSlug = $params;
    } else if (is_array($params) && isset($params['type'])) {
      $typeSlug = $params['type'];
    } else if ($request->hasParameter('type')) {
      $typeSlug = $request->getParameter('type');
    }

    // Try and get the information we need without having to query the database
    // See if we can find the type table class
    $typeClass = isset($typeSlug) ? Doctrine_Inflector::classify($typeSlug):false;
    if ($typeClass && class_exists($typeClass.'Table'))
    {
      $table = Doctrine::getTable($typeClass);
      $typeName = $typeClass;
    } else {
      if (isset($typeSlug))
      {
        $q = Doctrine::getTable('EntityType')->createQuery('t')
          ->andWhere('t.slug = ? OR t.name = ?', array($typeSlug, $typeSlug));
      } else if (isset($params['slug'])) {
        $q = Doctrine_Query::create()
          ->select('t.*')
          ->from('EntityType t')
          ->leftJoin('t.Entities e')
          ->where('e.slug = ?', $params['slug']);
      }

      $type = $q->fetchOne();
      $typeName = $type['name'];

      $table = Doctrine::getTable($type->getName());
    }

    $defaultQuery = true;
    if ($typeName)
    {
      if (method_exists($table, 'getEntityQuery'))
      {
        $defaultQuery = false;
        $q = $table->getEntityQuery();
      }
    }

    if ($defaultQuery)
    {
      $q = $this->getBaseQuery('e')
        ->innerJoin('e.'.$typeName);

      if (sfConfig::get('sf_logging_enabled'))
      {
        sfContext::getInstance()->getLogger()->notice('To improve performance '.get_class($table).' should have a callable method named "getEntityQuery()" that efficiently selects all the required data for your entity type with joins and specific selects.');
      }
    }

    return $q;
  }

  public function getEntity($params)
  {
    $request = sfContext::getInstance()->getRequest();
    $id = $request->getParameter('id');
    $slug = $request->getParameter('slug');

    $q = $this->getTypeQuery(array('slug' => $slug))
      ->andWhere('e.slug = ?', $slug);

    return $this->fetchEntity($q);
  }

  public function getEntityForSite($params)
  {
    $q = $this->getTypeQuery($params)
      ->andWhere('e.slug = ?', $params['slug']);

    return $this->fetchEntity($q);
  }

  public function getEntitiesForSite($params)
  {
    $q = $this->getTypeQuery($params);
    $q->addOrderBy('e.date_published DESC');

    $pager = new sfDoctrinePager('Entity', sfSympalConfig::get('rows_per_page'));
    $pager->setQuery($q);

    return $pager;
  }

  public function fetchEntity($q)
  {
    $entity = $q->fetchOne();

    return $entity;
  }

  public function getBaseQuery()
  {
    $q = Doctrine_Query::create()
      ->from('Entity e')
      ->leftJoin('e.Slots sl')
      ->leftJoin('sl.Type sty')
      ->leftJoin('e.Type ty')
      ->leftJoin('ty.Templates t')
      ->leftJoin('e.MasterMenuItem m')
      ->leftJoin('e.MenuItem mm')
      ->leftJoin('e.Site esi');

    if (!sfSympalTools::isEditMode())
    {
      $q->andWhere('m.is_published = 1 OR mm.is_published = 1')
        ->andWhere('e.is_published = 1');
    }

    $sympalContext = sfSympalContext::getInstance();
    $q->andWhere('esi.slug = ?', $sympalContext->getSite());

    return $q;
  }
}