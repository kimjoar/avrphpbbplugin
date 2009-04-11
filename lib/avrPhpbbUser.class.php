<?php

/*
 * This file is part of the avrPhpbbPlugin package.
 * (c) 2009 Kim Joar Bekkelund <kjbekkelund@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    avrPhpbbPlugin
 * @subpackage plugin
 * @author     Kim Joar Bekkelund <kjbekkelund@atmel.com>
 */
class avrPhpbbUser
{
  public static function getModeratorGroups($user)
  {
    $prefix = sfConfig::get('app_avrPhpbb_prefix', 'Phpbb');
    
    $c = new Criteria();
    myPropelTools::criteriaAdd($c, $prefix . 'UserGroup', 'user_id', $user->getUserId());
    myPropelTools::criteriaAddJoin($c, $prefix . 'UserGroup', 'group_id', $prefix . 'Groups', 'group_id');

    $groups = array();    
    foreach(myPropelTools::invokePeerMethod($prefix . 'Groups', 'doSelect', $c) AS $group) {
      $groups[] = $group->getGroupName();
    }
    
    return $groups;
  }
}