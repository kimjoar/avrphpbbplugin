<?php
/*
 * This file is part of the avrPhpbbPLugin package
 * (c) 2009 Kim Joar Bekkelund <kjbekkelund@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
  * @package    avrPhpbbPlugin
  * @subpackage Configuration
  * @author     Kim Joar Bekkelund <kjbekkelund@atmel.com>
  */

// setup default routes
if (sfConfig::get('app_phpbb_routes', true) && in_array('avrPhpbbPlugin', sfConfig::get('sf_enabled_modules', array())))
{
  $r = sfRouting::getInstance();

  $r->prependRoute('avr_phpbb_signin', '/signin', array('module' => 'avrPhpbb', 'action' => 'signin'));
  $r->prependRoute('avr_phpbb_signout', '/signout', array('module' => 'avrPhpbb', 'action' => 'signout'));
}