<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PluginContentTable extends Doctrine_Table
{
  public function getTypeQuery($typeName)
  {
    $table = Doctrine::getTable($typeName);

    $q = $this->getBaseQuery();

    $q->innerJoin('c.'.$typeName.' cr');

    if (sfSympalConfig::isI18nEnabled($typeName))
    {
      $q->leftJoin('cr.Translation crt');
    }

    $q = sfProjectConfiguration::getActive()->getEventDispatcher()->filter(new sfEvent($this, 'sympal.load_'.sfInflector::tableize($typeName).'_query'), $q)->getReturnValue();

    if (method_exists($table, 'getContentQuery'))
    {
      $q = $table->getContentQuery($q);
    }

    return $q;
  }

  public function getContent($params = array())
  {
    $request = sfContext::getInstance()->getRequest();
    $contentType = $request->getParameter('sympal_content_type');
    $contentId = $request->getParameter('sympal_content_id');
    $contentSlug = $request->getParameter('sympal_content_slug');
    $q = $this->getTypeQuery($contentType);

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
      } else if ($this->hasRelation('Translation')) {
        if ($this->getRelation('Translation')->getTable()->hasField($key))
        {
          $q->andWhere('ct.'.$key, $value);
        }
      } else if ($this->getRelation($contentType)->getTable()->hasField($key)) {
        $q->andWhere('cr.'.$key.' = ?', $value);
      }
    }

    $q = sfProjectConfiguration::getActive()->getEventDispatcher()->filter(new sfEvent($this, 'sympal.filter_get_content_query'), $q)->getReturnValue();

    $content = $q->fetchOne();

    $content = sfProjectConfiguration::getActive()->getEventDispatcher()->filter(new sfEvent($this, 'sympal.filter_get_content'), $content)->getReturnValue();

    return $content;
  }

  public function getBaseQuery()
  {
    $sympalContext = sfSympalContext::getInstance();
    $q = Doctrine_Query::create()
      ->from('Content c')
      ->leftJoin('c.Permissions p')
      ->leftJoin('c.Groups g')
      ->leftJoin('c.Template cte')
      ->leftJoin('c.Slots sl')
      ->leftJoin('sl.Type sty')
      ->leftJoin('c.Type ty')
      ->leftJoin('ty.ContentTemplates t')
      ->leftJoin('c.MasterMenuItem m')
      ->leftJoin('c.MenuItem mm')
      ->leftJoin('c.CreatedBy u')
      ->innerJoin('c.Site csi')
      ->andWhere('csi.slug = ?', $sympalContext->getSite());

    if (!sfSympalToolkit::isEditMode())
    {
      $expr = new Doctrine_Expression('NOW()');
      $q->andWhere('c.is_published = ?', true)
        ->andWhere('c.date_published < '.$expr);
    }

    if (sfSympalConfig::isI18nEnabled('ContentSlot'))
    {
      $q->leftJoin('sl.Translation slt');
    }

    if (sfSympalConfig::isI18nEnabled('Content'))
    {
      $q->leftJoin('c.Translation ct');
    }

    $q = sfProjectConfiguration::getActive()->getEventDispatcher()->filter(new sfEvent($this, 'sympal.filter_content_base_query'), $q)->getReturnValue();

    return $q;
  }

  public function getAdminGenQuery($q)
  {
    $sympalContext = sfSympalContext::getInstance();

    $q->leftJoin('r.Type t')
      ->leftJoin('r.MasterMenuItem m')
      ->leftJoin('r.MenuItem mm')
      ->leftJoin('r.CreatedBy u')
      ->innerJoin('r.Site csi WITH csi.slug = ?', $sympalContext->getSite());

    if (sfSympalConfig::isI18nEnabled('Content'))
    {
      $q->leftJoin('r.Translation ct');
    }

    $types = sfSympalToolkit::getContentTypesCache();
    foreach ($types as $type)
    {
      $q->leftJoin('r.'.$type.' '.$type);
      if (sfSympalConfig::isI18nEnabled($type))
      {
        $q->leftJoin($type.'.Translation '.$type.'tr');
      }
    }

    return $q;
  }
}