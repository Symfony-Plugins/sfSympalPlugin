<?php

class sfSympalMenuSiteManager
{
  protected
    $_currentUri = null,
    $_menus = array(),
    $_menuItems = array(),
    $_rootSlugs = array(),
    $_rootMenuItems = array(),
    $_hierarchies = array(),
    $_initialized = false;

  protected static $_instance;

  public function __construct()
  {
    $this->_currentUri = sfContext::getInstance()->getRequest()->getUri();
    if ($cache = $this->_getCache())
    {
      $cachedRootSlugs = $cache->get('SYMPAL_MENU_ROOT_SLUGS');
      if (is_array($cachedRootSlugs))
      {
        $this->_rootSlugs = $cachedRootSlugs;
      } else {
        $this->initialize();
      }
    }
  }

  public static function getInstance()
  {
    if (!self::$_instance)
    {
      $className = sfConfig::get('app_sympal_config_menu_manager_class');
      self::$_instance = new $className();
    }
    return self::$_instance;
  }

  public function getMenus()
  {
    $menus = array();
    foreach ($this->_rootSlugs as $slug)
    {
      $menus[$slug] = $this->getMenu($slug);
    }
    return $menus;
  }

  public function findCurrentMenuItem($menu = null)
  {
    if (is_null($menu))
    {
      foreach ($this->getMenus() as $menu)
      {
        if ($found = $this->findCurrentMenuItem($menu))
        {
          return $found;
        }
      }
    } else {
      if ($menu->getUrl(array('absolute' => true)) === $this->_currentUri)
      {
        return $menu->getMenuItem();
      }
      foreach ($menu->getChildren() as $child)
      {
        if ($found = $this->findCurrentMenuItem($child))
        {
          return $found;
        }
      }
    }
  }

  public function clear()
  {
    $this->_menuItems = array();
    $this->_rootSlugs = array();
    $this->_rootMenuItems = array();
    $this->_hierarchies = array();
    $this->_initialized = false;
  }

  public function refresh()
  {
    $this->clear();
    $this->initialize();
  }

  public static function getMenu($name, $showChildren = null, $class = null)
  {
    return self::getInstance()->_getMenu($name, $showChildren, $class);
  }

  protected function _getCache()
  {
    return sfSympalConfig::get('menu_cache', 'enabled', true) ? sfSympalConfiguration::getActive()->getCache() : false;
  }

  protected function _getMenu($name, $showChildren = null, $class = null)
  {
    if ($showChildren === null)
    {
      $showChildren = true;
    }
    if (is_scalar($name) && isset($this->_rootSlugs[$name]))
    {
      $name = $this->_rootSlugs[$name];
    }

    if (!$name)
    {
      return false;
    }
    
    $showChildren = (bool) $showChildren;

    $cacheKey = 'SYMPAL_MENU_'.md5((string) $name.var_export($showChildren, true).$class);
    if (isset($this->_menus[$cacheKey]))
    {
      return $this->_menus[$cacheKey];
    }

    $cache = $this->_getCache();
    if ($cache && $cache->has($cacheKey))
    {
      $menu = $cache->get($cacheKey);
    } else {
      $this->initialize();
      $menu = $this->_buildMenu($name, $class);
      if ($cache)
      {
        $cache->set($cacheKey, $menu);
      }
    }

    $this->_menus[$cacheKey] = $menu;

    if ($menu)
    {
      $menu->callRecursively('showChildren', $showChildren);

      $menu->setCacheKey($cacheKey);
    }

    return $menu;
  }

  protected function _buildMenu($name, $class)
  {
    if ($name instanceof sfSympalMenuItem)
    {
      $menuItem = $name;
      $rootId = $name['root_id'];
      $name = (string) $name;
    } else {
      $rootId = array_search($name, (array) $this->_rootSlugs);
    }

    if (!$rootId)
    {
      return false;
    }

    $rootMenuItem = $this->_rootMenuItems[$rootId];

    $class = $class ? $class:sfSympalConfig::get('menu_class', null, 'sfSympalMenuSite');
    $menu = new $class($name);
    $menu->setMenuItem($rootMenuItem);

    $hierarchy = $this->_hierarchies[$rootId];
    $this->_buildMenuHierarchy($hierarchy, $menu);

    if (isset($menuItem))
    {
      return $menu->getMenuItemSubMenu($menu->findMenuItem($menuItem)->getTopLevelParent()->getMenuItem());
    } else {
      return $menu;
    }
  }

  public static function split($menu, $max, $split = false)
  {
    $count = 0;
    $primaryChildren = array();
    $primary = clone $menu;

    if ($split)
    {
      $secondaryChildren = array();
      $secondary = clone $menu;
      $secondary->setName('secondary');
    }

    foreach ($menu->getChildren() as $child)
    {
      if (!$child->checkUserAccess())
      {
        continue;
      }

      $count++;
      if ($count > $max)
      {
        if ($split)
        {
          $secondaryChildren[] = $child;
          continue;
        } else {
          break;
        }
      }
      $primaryChildren[] = $child;
    }

    $primary->setChildren($primaryChildren);

    if ($split)
    {
      $secondary->setChildren($secondaryChildren);

      return array('primary' => $primary, 'secondary' => $secondary);
    } else {
      return $primary;
    }
  }

  public function initialize()
  {
    if (!$this->_initialized)
    {
      $this->_menuItems = Doctrine_Core::getTable('sfSympalMenuItem')->getMenuHierarchies();

      if (count($this->_menuItems) > 0)
      {
        foreach ($this->_menuItems as $menuItem)
        {
          $this->_rootSlugs[$menuItem['root_id']] = $menuItem['slug'];
          $this->_rootMenuItems[$menuItem['root_id']] = $menuItem;
          $this->_hierarchies[$menuItem['root_id']] = $menuItem['__children'];
        }
      }

      if ($cache = $this->_getCache())
      {
        $cache->set('SYMPAL_MENU_ROOT_SLUGS', $this->_rootSlugs);
      }

      // Mark the process as done so it is cached
      $this->_initialized = true;
    }
  }

  protected function _buildMenuHierarchy($hierarchy, $menu)
  {
    $user = sfContext::getInstance()->getUser();

    foreach ($hierarchy as $menuItem)
    {
      $new = $menu->addChild($menuItem->getSlug());
      $new->setName($menuItem->getName());
      $new->setMenuItem($menuItem);

      if (isset($menuItem['__children']) && !empty($menuItem['__children']))
      {
        $this->_buildMenuHierarchy($menuItem['__children'], $new);
      }
    }
  }
}