<?php
class sfSympalToolkit
{
  protected static
    $_currentMenuItem,
    $_currentContent;

  public static function getCurrentMenuItem()
  {
    return self::$_currentMenuItem;
  }

  public static function setCurrentMenuItem(MenuItem $menuItem)
  {
    self::$_currentMenuItem = $menuItem;
  }

  public static function getCurrentContent()
  {
    return self::$_currentContent;
  }

  public static function setCurrentContent(Content $content)
  {
    self::$_currentContent = $content;
  }

  public static function processPhpCode($code, $variables = array())
  {
    $sf_context = sfContext::getInstance();
    $vars = array(
      'sf_request' => $sf_context->getRequest(),
      'sf_response' => $sf_context->getResponse(),
      'sf_user' => $sf_context->getUser()
    );
    $variables = array_merge($variables, $vars);
    foreach ($variables as $name => $variable)
    {
      $$name = $variable;
    }

    ob_start();
    $code = str_replace('[?php', '<?php', $code);
    $code = str_replace('?]', '?>', $code);
    eval("?>" . $code);
    $rendered = ob_get_contents();
    ob_end_clean();

    return $rendered;
  }

  public static function loadDefaultLayout()
  {
    return self::changeLayout(sfSympalConfig::get('default_layout'));
  }

  public static function changeLayout($name)
  {
    $context = sfContext::getInstance();
    $request = $context->getRequest();
    $response = sfContext::getInstance()->getResponse();
    $configuration = $context->getConfiguration();

    $actionEntry = $context->getController()->getActionStack()->getLastEntry();
    $module = $actionEntry ? $actionEntry->getModuleName():$request->getParameter('module');
    $action = $actionEntry ? $actionEntry->getActionName():$request->getParameter('action');

    $paths = array(
      $configuration->getPluginConfiguration('sfSympalPlugin')->getRootDir() . '/templates/' . $name,
      sfConfig::get('sf_app_dir').'/templates/'.$name,
      sfConfig::get('sf_root_dir').'/'.$name,
      $name,
    );

    foreach ($paths as $path)
    {
      $checkPath = strstr($path, '.php') ? $path:$path.'.php';
      if (file_exists($checkPath))
      {
        $path = str_replace('.php', '', $path);
        break;
      }
    }

    $info = pathinfo($path);
    $name = $info['filename'];

    sfConfig::set('symfony.view.'.$module.'_'.$action.'_layout', $path);
    sfConfig::set('symfony.view.sympal_default_error404_layout', $path);
    sfConfig::set('symfony.view.sympal_default_secure_layout', $path);

    if (strstr($path, 'sfSympalPlugin/templates'))
    {
      $response->addStylesheet('/sfSympalPlugin/css/global');
      $response->addStylesheet('/sfSympalPlugin/css/default');
      $response->addStylesheet('/sfSympalPlugin/css/' . $name);
    } else {
      $response->addStylesheet($name, 'last');
    }

    return true;
  }

  public static function isEditMode()
  {
    $user = sfContext::getInstance()->getUser();

    return $user->isAuthenticated() && $user->getAttribute('sympal_edit', false);
  }

  public static function generateBreadcrumbs($breadcrumbsArray)
  {
    $breadcrumbs = new sfSympalMenuBreadcrumbs('Breadcrumbs');

    $count = 0;
    $total = count($breadcrumbsArray);
    foreach ($breadcrumbsArray as $name => $route)
    {
      $count++;
      if ($count == $total)
      {
        $breadcrumbs->addChild($name);
      } else {
        $breadcrumbs->addChild($name, $route);
      }
    }

    return $breadcrumbs;
  }

  protected static $_contentTypesCache = null;

  public static function getContentTypesCache()
  {
    if (is_null(self::$_contentTypesCache))
    {
      $cachePath = sfConfig::get('sf_cache_dir').'/sympal/content_types.cache';
      if (file_exists($cachePath))
      {
        self::$_contentTypesCache = unserialize(file_get_contents($cachePath));
      } else {
        self::$_contentTypesCache = array();
      }
    }

    return self::$_contentTypesCache;
  }

  protected static $_helperAutoloadCache = null;

  public static function autoloadHelper($functionName)
  {
    if (is_null(self::$_helperAutoloadCache))
    {
      $cachePath = sfConfig::get('sf_cache_dir').'/sympal/helper_autoload.cache';
      if (file_exists($cachePath))
      {
        self::$_helperAutoloadCache = unserialize(file_get_contents($cachePath));
      } else {
        self::$_helperAutoloadCache = array();
      }
    }
    if (isset(self::$_helperAutoloadCache[$functionName]))
    {
      require_once(self::$_helperAutoloadCache[$functionName]);
    } else {
      throw new sfException('Could not autoload helper for function "'.$functionName.'"');
    }
  }
}