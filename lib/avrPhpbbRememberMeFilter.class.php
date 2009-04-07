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
class avrPhpbbRememberMeFilter extends sfBasicSecurityFilter
{
  public function execute($filterChain)
  {
    $this->prefix = sfConfig::get('app_avrPhpbb_prefix', 'phpbb');

    // We only want to invoke the remembering filter if the user is not already authenticated
    if ($this->isFirstCall() && !$this->getContext()->getUser()->isAuthenticated()) {
      if ($cookie = $this->getContext()->getRequest()->getCookie(sfConfig::get('app_users_remember_cookie_name', 'avrPhpbbRememberKey'))) {
        $key = unserialize(base64_decode($cookie));

        $c = new Criteria();
        myPropelTools::criteriaAdd($c, $this->prefix . 'ProfileFieldData', 'PF_REMEMBER_KEY', $key[0]);
        myPropelTools::criteriaAdd($c, $this->prefix . 'ProfileFieldData', 'USER_ID', $key[1]);
        myPropelTools::criteriaAddJoin($c, $this->prefix . 'ProfileFieldData', 'USER_ID', $this->prefix . 'User', 'USER_ID');
        $user = myPropelTools::invokePeerMethod($this->prefix . 'User', 'doSelectOne', $c);

        // If the user is found with the given key and user id, the user is logged in. Otherwise nothing happens.
        if ($user) {
          $this->getContext()->getUser()->signIn($user);
        }
      }
    }

    $filterChain->execute();
  }
}