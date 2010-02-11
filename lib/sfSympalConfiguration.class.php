<?php

class sfSympalConfiguration
{
  protected
    $_dispatcher,
    $_projectConfiguration,
    $_sympalContext,
    $_symfonyContext,
    $_doctrineManager,
    $_bootstrap,
    $_plugins = array(),
    $_modules = array(),
    $_themes = null,
    $_availableThemes = null,
    $_cache;

  public function __construct(sfEventDispatcher $dispatcher, ProjectConfiguration $projectConfiguration)
  {
    // We disable Symfony autoload again feature because it is too slow in dev mode
    // If you introduce a new class when using sympal you just must clear your
    // cache manually
    sfAutoloadAgain::getInstance()->unregister();

    $this->_dispatcher = $dispatcher;
    $this->_projectConfiguration = $projectConfiguration;
    $this->_doctrineManager = Doctrine_Manager::getInstance();

    $this->_initializeSymfonyConfig();
    $this->_markClassesAsSafe();
    $this->_connectEvents();
    $this->_configureSuperCache();
    $this->_configureDoctrine();
  }

  /**
   * Mark necessary Sympal classes as safe
   *
   * @return void
   */
  private function _markClassesAsSafe()
  {
    sfOutputEscaper::markClassesAsSafe(array(
      'sfSympalContent',
      'sfSympalContentTranslation',
      'sfSympalContentSlot',
      'sfSympalContentSlotTranslation',
      'sfSympalMenuItem',
      'sfSympalMenuItemTranslation',
      'sfSympalContentRenderer',
      'sfSympalMenu',
      'sfParameterHolder',
      'sfSympalDataGrid',
      'sfSympalUpgradeFromWeb',
      'sfSympalServerCheckHtmlRenderer',
      'sfSympalSitemapGenerator'
    ));
  }

  /**
   * Connect to various events required by Sympal
   *
   * @return void
   */
  private function _connectEvents()
  {
    $this->_dispatcher->connect('context.load_factories', array($this, 'bootstrap'));
    $this->_dispatcher->connect('component.method_not_found', array(new sfSympalActions(), 'extend'));
    $this->_dispatcher->connect('controller.change_action', array($this, 'initializeTheme'));
    $this->_dispatcher->connect('template.filter_parameters', array($this, 'filterTemplateParameters'));
    $this->_dispatcher->connect('form.method_not_found', array(new sfSympalForm(), 'extend'));
    $this->_dispatcher->connect('form.post_configure', array('sfSympalForm', 'listenToFormPostConfigure'));
    $this->_dispatcher->connect('form.filter_values', array('sfSympalForm', 'listenToFormFilterValues'));
    $this->_dispatcher->connect('task.cache.clear', array($this, 'listenToTaskCacheClear'));
  }

  /**
   * Configure super cache if enabled
   *
   * @return void
   */
  private function _configureSuperCache()
  {
    if (sfSympalConfig::get('page_cache', 'super') && sfConfig::get('sf_cache'))
    {
      $superCache = new sfSympalSuperCache($this);
      $this->_dispatcher->connect('response.filter_content', array($superCache, 'listenToResponseFilterContent'));
    }
  }

  /**
   * Configure the Doctrine manager for Sympal
   *
   * @return void
   */
  private function _configureDoctrine()
  {
    $this->_doctrineManager->setAttribute(Doctrine_Core::ATTR_HYDRATE_OVERWRITE, false);
    $this->_doctrineManager->setAttribute(Doctrine_Core::ATTR_TABLE_CLASS, 'sfSympalDoctrineTable');
    $this->_doctrineManager->setAttribute(Doctrine_Core::ATTR_QUERY_CLASS, 'sfSympalDoctrineQuery');
    $this->_doctrineManager->setAttribute(Doctrine_Core::ATTR_COLLECTION_CLASS, 'sfSympalDoctrineCollection');
    $this->_configureDoctrineCache();    
  }

  /**
   * Configure Doctrine cache if it is enabled
   *
   * @return void
   */
  private function _configureDoctrineCache()
  {
    if (sfSympalConfig::get('orm_cache', 'enabled', true))
    {
      $driver = sfSympalCache::getOrmCacheDriver();

      $this->_doctrineManager->setAttribute(Doctrine_Core::ATTR_QUERY_CACHE, $driver);

      if (sfSympalConfig::get('orm_cache', 'result', false))
      {
        $this->_doctrineManager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE, $driver);
        $this->_doctrineManager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE_LIFESPAN, sfSympalConfig::get('orm_cache', 'lifetime', 86400));
      }
    }
  }

  /**
   * Listen to clear cache task event so we can clear the web cache folder
   *
   * @param sfEvent $event 
   * @return void
   */
  public function listenToTaskCacheClear(sfEvent $event)
  {
    $event->getSubject()->logSection('sympal', 'Clearing web cache folder');

    $cacheDir = sfConfig::get('sf_web_dir').'/cache';
    if (is_dir($cacheDir))
    {
      $event->getSubject()->getFilesystem()->remove(sfFinder::type('file')->ignore_version_control()->discard('.sf')->in($cacheDir));
    }
  }

  /**
   * Callable attached to Symfony event context.load_factories. When this event
   * is triggered we also create the Sympal context.
   */
  public function bootstrap(sfEvent $event)
  {
    $this->_projectConfiguration = $event->getSubject()->getConfiguration();

    $record = Doctrine_Core::getTable(sfSympalConfig::get('user_model'))->getRecordInstance();
    $this->_dispatcher->notify(new sfEvent($record, 'sympal.user.set_table_definition', array('object' => $record)));

    $this->_cache = new sfSympalCache($this);

    $this->_symfonyContext = $event->getSubject();
    $this->_sympalContext = sfSympalContext::createInstance($this->_symfonyContext, $this);

    $this->_enableModules();

    $this->_checkSympalInstall();

    $this->initializeTheme();

    $this->_projectConfiguration->loadHelpers(array(
      'Sympal', 'SympalContentSlot', 'SympalMenu', 'SympalPager', 'I18N', 'Asset', 'Url', 'Partial'
    ));

    if ($this->isAdminModule())
    {
      sfConfig::set('sf_login_module', 'sympal_admin');
      $this->_projectConfiguration->loadHelpers(array('Admin'));
    }
  }

  /**
   * Filter Symfony template parameters and add some references to some variables
   *
   * @param sfEvent $event 
   * @param array $parameters
   * @return array $parameters
   */
  public function filterTemplateParameters(sfEvent $event, $parameters)
  {
    if (!$this->_sympalContext)
    {
      return $parameters;
    }
    $parameters['sf_sympal_context'] = $this->_sympalContext;
    if ($content = $this->_sympalContext->getCurrentContent())
    {
      $parameters['sf_sympal_content'] = $content;
    }
    if ($menuItem = $this->_sympalContext->getCurrentMenuItem())
    {
      $parameters['sf_sympal_menu_item'] = $menuItem;
    }
    return $parameters;
  }

  /**
   * Get the Doctrine_Manager instance
   *
   * @return Doctrine_Manager $manager
   */
  public function getDoctrineManager()
  {
    return $this->_doctrineManager;
  }

  /**
   * Get the sfSympalCache instance
   *
   * @return sfSympalCache $cache
   */
  public function getCache()
  {
    return $this->_cache;
  }

  /**
   * Get the current sfSympalContext instance
   *
   * @return sfSympalContext $sympalContext
   */
  public function getSympalContext()
  {
    return $this->_sympalContext;
  }

  /**
   * Get the current ProjectConfiguration instance
   *
   * @return ProjectConfiguration $projectConfiguration
   */
  public function getProjectConfiguration()
  {
    return $this->_projectConfiguration;
  }

  /**
   * Get array of required plugins for Sympal
   *
   * @return array $requiredPlugins
   */
  public function getRequiredPlugins()
  {
    $requiredPlugins = array();
    foreach ($this->_projectConfiguration->getPlugins() as $pluginName)
    {
      if (strpos($pluginName, 'sfSympal') !== false)
      {
        $dependencies = sfSympalPluginToolkit::getPluginDependencies($pluginName);
        $requiredPlugins = array_merge($requiredPlugins, $dependencies);
      }
    }

    return array_values(array_unique($requiredPlugins));
  }

  /**
   * Get array of core Sympal plugins
   *
   * @return array $corePlugins
   */
  public function getCorePlugins()
  {
    return sfSympalPluginConfiguration::$dependencies;
  }

  /**
   * Get array of plugins which contain a content type
   *
   * @return array $contentTypePlugins
   */
  public function getContentTypePlugins()
  {
    $contentTypePlugins = array();
    $plugins = $this->getPluginPaths();

    foreach ($plugins as $plugin => $path)
    {
      $manager = new sfSympalPluginManager($plugin, $this->_projectConfiguration, new sfFormatter());
      if ($contentType = $manager->getContentTypeForPlugin())
      {
        $contentTypePlugins[$plugin] = $plugin;
      }
    }
    return $contentTypePlugins;
  }

  /**
   * Get array of plugins that are downloaded and installed to your project
   *
   * @return array $installedPlugins
   */
  public function getInstalledPlugins()
  {
    return $this->getOtherPlugins();
  }

  /**
   * Get array of available addon plugins
   *
   * @return array $addonPlugins
   */
  public function getAddonPlugins()
  {
    return sfSympalPluginToolkit::getAvailablePlugins();
  }

  /**
   * Get array of other plugins that are not required
   *
   * @return array $otherPlugins
   */
  public function getOtherPlugins()
  {
    return array_diff($this->getPlugins(), $this->getRequiredPlugins());
  }

  /**
   * Get array of all manageable plugins that can be downloaded, installed, uninstalled, etc.
   *
   * @return array $allManageablePlugins
   */
  public function getAllManageablePlugins()
  {
    $plugins = array_merge($this->getAddonPlugins(), $this->getInstalledPlugins());
    $plugins = array_unique($plugins);

    return $plugins;
  }

  /**
   * Get array of all installed plugins
   *
   * @return array $plugins
   */
  public function getPlugins()
  {
    return array_keys($this->getPluginPaths());
  }

  /**
   * Get paths to all Sympal plugins
   *
   * @return array $pluginPaths
   */
  public function getPluginPaths()
  {
    if (!$this->_plugins)
    {
      $configuration = ProjectConfiguration::getActive();
      $pluginPaths = $configuration->getAllPluginPaths();
      $this->_plugins = array();
      foreach ($pluginPaths as $pluginName => $path)
      {
        if (strpos($pluginName, 'sfSympal') !== false)
        {
          $this->_plugins[$pluginName] = $path;
        }
      }
    }

    return $this->_plugins;
  }

  /**
   * Get array of all modules
   *
   * @return array $modules
   */
  public function getModules()
  {
    return $this->getCache()->getModules();
  }

  /**
   * Get array of all layouts
   *
   * @return array $layouts
   */
  public function getLayouts()
  {
    return $this->getCache()->getLayouts();
  }

  /**
   * Get array of all themes that are not disabled.
   *
   * @return array $themes
   */
  public function getThemes()
  {
    if ($this->_themes === null)
    {
      $themes = sfSympalConfig::get('themes', null, array());
      foreach ($themes as $name => $theme)
      {
        if (isset($theme['disabled']) && $theme['disabled'] === true)
        {
          continue;
        }
        $this->_themes[$name] = $theme;
      }
    }
    return $this->_themes;
  }

  /**
   * Get array of all themes that are not disabled and available for selection
   *
   * @return array $availableThemes
   */
  public function getAvailableThemes()
  {
    if ($this->_availableThemes === null)
    {
      $themes = $this->getThemes();
      foreach ($themes as $name => $theme)
      {
        if (!isset($theme['available']) || (isset($theme['available']) && $theme['available'] === false))
        {
          continue;
        }
        $this->_availableThemes[$name] = $theme;
      }
    }
    return $this->_availableThemes;
  }

  /**
   * Get array of configured content templates for a given moel name
   *
   * @param string $model
   * @return array $contentTemplates
   */
  public function getContentTemplates($model)
  {
    return sfSympalConfig::get($model, 'content_templates', array());
  }

  /**
   * Check if we are inside an admin module
   *
   * @return boolean
   */
  public function isAdminModule()
  {
    if (!$this->_symfonyContext)
    {
      return false;
    }
    $module = $this->_symfonyContext->getRequest()->getParameter('module');
    $adminModules = sfSympalConfig::get('admin_modules');
    return array_key_exists($module, $adminModules);
  }

  /**
   * Get the theme to use for the current request
   *
   * @return string $theme
   */
  public function getThemeForRequest()
  {
    $request = $this->_symfonyContext->getRequest();
    $module = $request->getParameter('module');

    if ($this->isAdminModule())
    {
      return sfSympalConfig::get('admin_theme', null, 'admin');
    }

    if (sfSympalConfig::get('allow_changing_theme_by_url'))
    {
      $user = $this->_symfonyContext->getUser();

      if ($theme = $request->getParameter(sfSympalConfig::get('theme_request_parameter_name', null, 'sf_sympal_theme')))
      {
        $user->setCurrentTheme($theme);
        return $theme;
      }

      if ($theme = $user->getCurrentTheme())
      {
        return $theme;
      }
    }

    if ($theme = sfSympalConfig::get($module, 'theme'))
    {
      return $theme;
    }

    if ($theme = $theme = sfSympalConfig::get(sfContext::getInstance()->getRouting()->getCurrentRouteName(), 'theme'))
    {
      return $theme;
    }

    return sfSympalConfig::get('default_theme');
  }

  /**
   * Initialize the theme for the current request
   *
   * @return void
   */
  public function initializeTheme()
  {
    if (!$this->_symfonyContext->getRequest()->isXmlHttpRequest())
    {
      $this->_sympalContext->loadTheme($this->getThemeForRequest());
    }
  }

  /**
   * Handle the enabling of modules. Either enables all modules or only the configured modules.
   *
   * @return void
   */
  private function _enableModules()
  {
    if (sfSympalConfig::get('enable_all_modules', null, true))
    {
      $modules = sfConfig::get('sf_enabled_modules', array());
      if (sfSympalConfig::get('enable_all_modules'))
      {
        $modules = array_merge($modules, $this->getModules());
      } else {
        $modules = array_merge($modules, sfSympalConfig::get('enabled_modules', null, array()));
      }

      if ($disabledModules = sfSympalConfig::get('disabled_modules', null, array()))
      {
        $modules = array_diff($modules, $disabledModules);
      }

      sfConfig::set('sf_enabled_modules', $modules);
    }
  }

  /**
   * Check if Sympal is installed and redirect to installer if not.
   * Do some other install checks as well.
   *
   * @return void
   */
  private function _checkSympalInstall()
  {
    $sfContext = sfContext::getInstance();
    $request = $sfContext->getRequest();

    // Prepare the symfony application is it has not been prepared yet
    if (!$sfContext->getUser() instanceof sfSympalUser)
    {
      chdir(sfConfig::get('sf_root_dir'));
      $task = new sfSympalEnableForAppTask($this->_dispatcher, new sfFormatter());
      $task->run(array($this->_projectConfiguration->getApplication()), array());

      $sfContext->getController()->redirect('@homepage');
    }

    // Redirect to install module if...
    //  not in test environment
    //  sympal has not been installed
    //  module is not already sympal_install
    if (sfConfig::get('sf_environment') != 'test' && !sfSympalConfig::get('installed') && $request->getParameter('module') != 'sympal_install')
    {
      $sfContext->getController()->redirect('@sympal_install');
    }

    // Redirect to homepage if no site record exists so we can prompt the user to create
    // a site record for this application
    // This check is only ran in dev mode
    if (sfConfig::get('sf_environment') == 'dev' && !$this->_sympalContext->getSite() && $sfContext->getRequest()->getPathInfo() != '/')
    {
      $sfContext->getController()->redirect('@homepage');
    }
  }

  /**
   * Initialize some sfConfig values for Sympal
   *
   * @return void
   */
  private function _initializeSymfonyConfig()
  {
    sfConfig::set('sf_cache', sfSympalConfig::get('page_cache', 'enabled', false));
    sfConfig::set('sf_default_culture', sfSympalConfig::get('default_culture', null, 'en'));
    sfConfig::set('sf_admin_module_web_dir', sfSympalConfig::get('admin_module_web_dir', null, '/sfSympalAdminPlugin'));

    sfConfig::set('app_sf_guard_plugin_success_signin_url', sfSympalConfig::get('success_signin_url'));

    if (sfConfig::get('sf_login_module') == 'default')
    {
      sfConfig::set('sf_login_module', 'sympal_auth');
      sfConfig::set('sf_login_action', 'signin');
    }

    if (sfConfig::get('sf_secure_module') == 'default')
    {
      sfConfig::set('sf_secure_module', 'sympal_auth');
      sfConfig::set('sf_secure_action', 'secure');
    }

    if (sfConfig::get('sf_error_404_module') == 'default')
    {
      sfConfig::set('sf_error_404_module', 'sympal_default');
      sfConfig::set('sf_error_404_action', 'error404');
    }

    if (sfConfig::get('sf_module_disabled_module') == 'default')
    {
      sfConfig::set('sf_module_disabled_module', 'sympal_default');
      sfConfig::set('sf_module_disabled_action', 'disabled');
    }

    sfConfig::set('sf_jquery_path', sfSympalConfig::get('jquery_reloaded', 'path'));
    sfConfig::set('sf_jquery_plugin_paths', sfSympalConfig::get('jquery_reloaded', 'plugin_paths'));
  }

  /**
   * Get the active sfSympalConfiguration instance
   *
   * @return sfSympalConfiguration $sympalConfiguration
   */
  public static function getActive()
  {
    return sfApplicationConfiguration::getActive()->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration();
  }
}