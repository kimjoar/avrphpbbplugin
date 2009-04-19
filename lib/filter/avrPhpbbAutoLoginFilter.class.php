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
class avrPhpbbAutoLoginFilter extends sfBasicSecurityFilter
{
  public function execute($filterChain)
  {
    if (!$this->isFirstCall()) {
      return;
    }

    $this->prefix = sfConfig::get('app_phpbb_prefix', 'Phpbb');

    // When we are logged out of phpBB, the system sets a cookie with id = 1. 
    // Because we want to log out of Symfony when this happens, we check for
    // cookies with user id different from the current logged in symfony user.
    if ($this->getContext()->getUser()->isAuthenticated() && $this->getContext()->getRequest()->getCookie(avrPhpbbConfig::getCookieName() . '_u') != $this->getContext()->getUser()->getUserId()) {
      $this->getContext()->getUser()->signOut();
    }

    // We only want to invoke the sign in if the user is not already authenticated.
    if (!$this->getContext()->getUser()->isAuthenticated()) {
      $cookieName = avrPhpbbConfig::getCookieName();

      // If the cookies are ok, the user gets signed in.
      if (avrPhpbbSessions::checkCookie($cookieName)) {
        $userId = $this->getContext()->getRequest()->getCookie($cookieName . '_u');
        $user = avrPropelTools::invokePeerMethod($this->prefix . 'Users', 'retrieveByPk', $userId);
        
        // Since the user is already signed in to phpBB, we only sign in to Symfony
        $this->getContext()->getUser()->signInSymfony($user);
      }
    }
    
    $filterChain->execute();
  }
}