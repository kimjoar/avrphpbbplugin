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
    $groups = array();
    foreach ($user->getUserGroupsRelatedByUserId() AS $group) {
      $groupName = GroupPeer::getModeratorGroupById($group->getGroupId());
  
      if (!is_null($groupName)) {
        $groups[] = $groupName;
      }
    }
  
    return $groups;
  }
}