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
  /**
   * @todo Should probably be renamed to getUserGroups or getGroups.
   */
  public static function getModeratorGroups($user)
  {
    $prefix = sfConfig::get('app_phpbb_prefix', 'Phpbb');
    
    $c = new Criteria();
    myPropelTools::criteriaAdd($c, $prefix . 'UserGroup', 'user_id', $user->getUserId());
    myPropelTools::criteriaAddJoin($c, $prefix . 'UserGroup', 'group_id', $prefix . 'Groups', 'group_id');

    $groups = array();    
    foreach(myPropelTools::invokePeerMethod($prefix . 'Groups', 'doSelect', $c) AS $group) {
      $groups[] = $group->getGroupName();
    }
    
    return $groups;
  }
  
  public static function getUserByUsername($username)
  {
    $prefix = sfConfig::get('app_phpbb_prefix', 'Phpbb');
    
    $c = new Criteria();
    myPropelTools::criteriaAdd($c, $prefix . 'Users', 'username', $username);

    return myPropelTools::invokePeerMethod($prefix . 'Users', 'doSelectOne', $c);
  }
  
  /**
   * Whether or not the users account is activated.
   * 
   * @todo This method is probably a little rough according to the specific way 
   *       phpBB3 actually works, and might need a little more work.
   */
  public static function isActivated($user)
  {
    // If activation is not required, the user is per definition activated.
    if (avrPhpbbConfig::getConfigValueFor('require_activation') == 0) {
      return true;
    }

    // If there is no activation key, but activation is required, the user has 
    // activated the account.
    if ($user->getUserActKey() == '') {
      return true;
    }
    
    return false;
  }
}