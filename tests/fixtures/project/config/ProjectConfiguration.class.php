<?php

$_SERVER['SYMFONY'] = '/Users/jwage/Sites/symfonysvn/1.2/lib';

if (!isset($_SERVER['SYMFONY']))
{
  throw new RuntimeException('Could not find symfony core libraries.');
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    require_once(dirname(__FILE__).'/../../../../config/sfSympalPluginConfiguration.class.php');
    sfSympalPluginConfiguration::enableSympalPlugins($this);

    $this->enableAllPluginsExcept(array('sfPropelPlugin', 'sfCompat10Plugin'));
  }

  /**
   * Methods used by unit.php and functional.php bootstrap files
   */

  public function initializeSympal()
  {
    chdir(sfConfig::get('sf_root_dir'));

    $install = new sfSympalInstall($this, $this->dispatcher, new sfFormatter());
    $install->install();
  }

  public function loadFixtures($fixtures)
  {
    $fixtures = is_bool($fixtures) ? 'fixtures.yml' : $fixtures;
    $path = sfConfig::get('sf_data_dir') . '/fixtures/' . $fixtures;
    if ( ! file_exists($path)) {
      throw new sfException('Invalid data fixtures file');
    }
    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfDoctrineLoadDataTask($this->dispatcher, new sfFormatter());
    $task->run(array(), array('--env=test', '--dir=' . $path));
  }
}
