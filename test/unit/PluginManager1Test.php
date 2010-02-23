<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(2, new lime_output_color());

chdir(sfConfig::get('sf_root_dir'));

function generatePlugin($name, $contentType, $t)
{
  global $configuration;

  $generate = new sfSympalPluginGenerateTask($configuration->getEventDispatcher(), new sfFormatter());
  $generate->run(array($name), array('--re-generate', '--no-confirmation', '--content-type='.$contentType));

  $t->is(file_exists(sfConfig::get('sf_plugins_dir').'/'.sfSympalPluginToolkit::getLongPluginName($name)), true, 'Test that the plugin was generated');
}

function downloadPlugin($name, $t)
{
  $manager = sfSympalPluginManager::getActionInstance($name, 'uninstall');
  $manager->uninstall(true);

  $manager = sfSympalPluginManager::getActionInstance($name, 'download');
  $manager->download();

  $t->is(file_exists(sfConfig::get('sf_plugins_dir').'/'.$name), true, 'Test that the plugin exists and was downloaded');
}

downloadPlugin('sfSympalObjectReplacerPlugin', $t);
generatePlugin('Event', 'Event', $t);