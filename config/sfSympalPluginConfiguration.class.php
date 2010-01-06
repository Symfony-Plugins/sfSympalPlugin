<?php

class sfSympalPluginConfiguration extends sfPluginConfiguration
{
  const VERSION = '1.0.0-DEV';

  public static
    $dependencies = array(
      'sfDoctrineGuardPlugin',
      'sfFormExtraPlugin',
      'sfTaskExtraPlugin',
      'sfFeed2Plugin',
      'sfWebBrowserPlugin',
      'sfJqueryReloadedPlugin',
      'sfThumbnailPlugin',
      'sfImageTransformPlugin',
      'sfSympalMenuPlugin',
      'sfSympalPluginManagerPlugin',
      'sfSympalPagesPlugin',
      'sfSympalContentListPlugin',
      'sfSympalDataGridPlugin',
      'sfSympalUserPlugin',
      'sfSympalInstallPlugin',
      'sfSympalUpgradePlugin',
      'sfSympalRenderingPlugin',
      'sfSympalAdminPlugin',
      'sfSympalFrontendEditorPlugin',
      'sfSympalAssetsPlugin'
    );

  public
    $sympalConfiguration;

  public function initialize()
  {
    $this->sympalConfiguration = new sfSympalConfiguration($this->dispatcher, $this->configuration);
  }

  public static function enableSympalPlugins(ProjectConfiguration $configuration)
  {
    require_once(dirname(__FILE__).'/../lib/core/sfSympalPluginEnabler.class.php');

    $enabler = new sfSympalPluginEnabler($configuration);
    $enabler->enableSympalPlugins();
  }

  public function getSympalConfiguration()
  {
    return $this->sympalConfiguration;
  }
}