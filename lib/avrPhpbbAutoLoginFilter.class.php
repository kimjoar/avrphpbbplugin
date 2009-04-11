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
    $this->prefix = sfConfig::get('app_avrPhpbb_prefix', 'Phpbb');

    if ($this->getContext()->getUser()->isAuthenticated() && $this->getContext()->getRequest()->getCookie(avrPhpbbConfig::getCookieName() . '_u') != $this->getContext()->getUser()->getUserId()) {
      $this->getContext()->getUser()->signOut();
    }

    // We only want to invoke the remembering filter if the user is not already authenticated
    if ($this->isFirstCall() && !$this->getContext()->getUser()->isAuthenticated()) {
      $cookieName = avrPhpbbConfig::getCookieName();

      if (avrPhpbbSessions::checkCookie($cookieName)) {
        $userId = $this->getContext()->getRequest()->getCookie($cookieName . '_u');
        $user = myPropelTools::invokePeerMethod($this->prefix . 'Users', 'retrieveByPk', $userId);
        
        // Since the user is already signed in to phpBB, we only sign in to Symfony
        $this->getContext()->getUser()->signInSymfony($user);
      }
    }
    
    $filterChain->execute();
  }
}