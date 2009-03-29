<?php
$app = 'sympal';
$database = true;
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$configuration->loadHelpers(array('Url'));

$browser = new sfTestFunctional(new sfBrowser());
$browser->get('/');

$menuItems = Doctrine::getTable('MenuItem')->findAll();
foreach ($menuItems as $menuItem)
{
  if ($menuItem->level <= 0 || $menuItem->requires_auth || $menuItem->requires_no_auth || !($content = $menuItem->getMainContent()))
  {
    continue;
  }

  $browser->
    click($menuItem->getLabel())->
    isStatusCode('200')->
    with('request')->begin()->
      isParameter('module', 'sympal_content_renderer')->
      isParameter('action', 'index')->
    end()->
    with('response')->begin()->
      contains((string) $menuItem->getBreadcrumbs($content))->
      contains($content->getSlots()->getFirst()->render())->
      contains($content->getSlots()->getLast()->render())->
    end();
}

$browser->
  get('/signin')->
  post('/signin', array('signin' => array('username' => 'admin', 'password' => 'admin')))->
  isRedirected()->
  followRedirect()->
  with('user')->begin()->
    isAuthenticated()->
  end()
;

$browser->
  get('/security/logout')->
  isRedirected()->
  followRedirect()
;

$current = sfSympalConfig::get('sfSympalRegisterPlugin', 'enable_recaptcha');
sfSympalConfig::set('sfSympalRegisterPlugin', 'enable_recaptcha', false);

$browser->
  get('/register')->
  post('/register/save', array('sf_guard_user' => array('username' => 'test', 'password' => 'test', 'password_again' => 'test')))->
  isRedirected()->
  followRedirect()
;

sfSympalConfig::set('sfSympalRegisterPlugin', 'enable_recaptcha', $current);

$browser->
  click('Signout')->
  isRedirected()->
  followRedirect()->
  click('Signin')->
  post('/signin', array('signin' => array('username' => 'test', 'password' => 'test')))->
  with('user')->begin()->
    isAuthenticated()->
  end()
;