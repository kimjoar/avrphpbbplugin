<?php
/*
 * This file is part of the avrPhpbbPLugin package
 * (c) 2009 Kim Joar Bekkelund <kjbekkelund@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base class for avrPhpbb actions.
 *
 * @package    avrPhpbbPlugin
 * @subpackage Module
 * @author     Kim Joar Bekkelund <kjbekkelund@atmel.com>
 */
abstract class BaseavrPhpbbActions extends sfActions
{
  public function executeSignin()
  {
    if ($this->getModuleName() != ($module = sfConfig::get('sf_login_module')))
    {
      return $this->redirect($module . '/' . sfConfig::get('sf_login_action'));
    }

    $user = $this->getUser();

    if ($user->isAuthenticated())
    {
      $this->redirect('@homepage');
    }

    if ($this->getRequest()->getMethod() == sfRequest::POST)
    {
      echo 'post';die;
      // Redirects in the following priority:
      // 1. URL set in app.yml
      // 2. referer
      // 3. @homepage from routes
      
      $referer = $user->getAttribute('referer', '@homepage');
      $user->getAttributeHolder()->remove('referer');
      $signinUrl = sfConfig::get('app_phpnbb_success_signin_url', $referer);

      return $this->redirect('' != $signinUrl ? $signinUrl : '@homepage');
    }

    // if we have been forwarded, then the referer is the current URL
    // if not, this is the referer of the current request
    $user->setAttribute('referer', $this->getContext()->getActionStack()->getSize() > 1 ? $this->getRequest()->getUri() : $this->getRequest()->getReferer());

    $this->getResponse()->setStatusCode(401);
  }

  public function executeSignout()
  {
    $this->getUser()->signOut();

    $signoutUrl = sfConfig::get('app_sf_guard_plugin_success_signout_url', $this->getRequest()->getReferer());

    $this->redirect('' != $signoutUrl ? $signoutUrl : '@homepage');
  }

  // Errors

  public function handleErrorSignin()
  {
    $user = $this->getUser();
    
    if (!$user->hasAttribute('referer'))
    {
      $user->setAttribute('referer', $this->getRequest()->getReferer());
    }

    $module = sfConfig::get('sf_login_module');
    if ($this->getModuleName() != $module)
    {
      $this->forward(sfConfig::get('sf_login_module'), sfConfig::get('sf_login_action'));
    }

    return sfView::SUCCESS;
  }
}
