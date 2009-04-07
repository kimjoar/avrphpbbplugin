<?php

/*
 * This file is part of the avrPhpbbPlugin package.
 * (c) 2009 Kim Joar Bekkelund <kjbekkelund@atmel.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    avrPhpbbPlugin
 * @subpackage plugin
 * @author     Kim Joar Bekkelund <kjbekkelund@atmel.com>
 *
 * Notes:
 * * Password should be checked by user validation, and is therefore not 
 *   checked here.
 * * Add credentials based on phpBB's group system. To add a credential to a
 *   user, the user has to be added to the correct phpBB group.
 */
class avrPhpbbSecurityUser extends sfBasicSecurityUser
{
  protected $user = null;

  public function initialize($context, $parameters = array())
  {
    parent::initialize($context, $parameters);

    if (!$this->isAuthenticated()) {
      // remove user if timeout
      $this->getAttributeHolder()->removeNamespace('avrPhpbbSecurityUser');
      $this->user = null;
    }
  }
  
  public function getPhpbbUser()
  {
    if (!$this->user && $id = $this->getAttribute('user_id', null, 'avrPhpbbSecurityUser'))
    {
      $this->user = UserPeer::retrieveByPK($id);

      if (!$this->user)
      {
        // the user does not exist anymore in the database
        $this->signOut();

        // @todo Create avrPhpbbException
        throw new sfException('The user does not exist anymore in the database.');
      }
    }

    return $this->user;
  }
  
  public function hasCredential($credential, $useAnd = true)
  {
    if (!$this->getPhpbbUser()) {
      return false;
    }

    return parent::hasCredential($credential, $useAnd);
  }

  public function isAnonymous()
  {
    return !$this->isAuthenticated();
  }

  public function signIn($user, $remember = false, $con = null)
  {
    $this->signInPhpbb($user);

    // signin
    $this->setAttribute('user_id', $user->getUserId(), 'avrPhpbbSecurityUser');
    $this->setAuthenticated(true);

    // credentials
    $this->clearCredentials();
    $this->addCredential('user');
    $this->addCredentials($user->getModeratorGroups());

    // save last login
    $user->setUserLastvisit(time());
    $user->save($con);

    if ($remember) {
      $this->remember($user);
    }
  }
  
  public function signOut()
  {
    $this->signOutPhpbb();

    // signout
    $this->getAttributeHolder()->removeNamespace('avrPhpbbSecurityUser');

    $this->user = null;
    $this->clearCredentials();
    $this->setAuthenticated(false);

    // remove remember cookie
    $lifetime = sfConfig::get('app_users_remember_me_lifetime', 60 * 60 * 24 * 14);
    $cookieName = sfConfig::get('app_users_remember_cookie_name', 'avrPhpbbRememberKey');
  
    sfContext::getInstance()->getResponse()->setCookie($cookieName, '', time() - $lifetime);
  }

  protected function signInPhpbb($user)
  {
    $sessionId  = substr($this->generateRandomKey() . $this->generateRandomKey(), 0, 32);
    $sessionKey = '';

    $this->insertSession(array('userId' => $user->getUserId(), 'sessionKey' => $sessionKey, 'sessionId' => $sessionId));
  }

  protected function signOutPhpbb()
  {
    $this->destroySession();
  }
  
  protected function remember($user)
  {
    $rememberKey = $user->getUserRememberKey();
    $cookieName = sfConfig::get('app_users_remember_cookie_name', 'avrPhpbbRememberKey');
    $key = base64_encode(serialize(array($rememberKey, $user->getUserId())));
    $lifetime = sfConfig::get('app_users_remember_me_lifetime', 60 * 60 * 24 * 14);

    sfContext::getInstance()->getResponse()->setCookie($cookieName, $key, time() + $lifetime, '/');
  }
  
  protected function generateRandomKey()
  {
    $val = rand() . microtime();
    $val = md5($val);

    return substr($val, 4, 16);
  }

  protected function insertSession($params)
  {
    if ('' != $params['sessionKey']) {
      SessionKeyPeer::create($params);
    }

    SessionPeer::create($params);

    $this->setCookies($params);
  }

  protected function destroySession()
  {
    $cookieValues = $this->getCookies();

    SessionPeer::delete($cookieValues);
    SessionKeyPeer::delete($cookieValues);

    $this->unsetCookies();
  }

  protected function getCookies()
  {
    $cookieName = ConfigPeer::getCookieName();
    $request = sfContext::getInstance()->getRequest();

    return array(
      'sessionId'  => $request->getCookie($cookieName . '_sid'),
      'sessionKey' => $request->getCookie($cookieName . '_k'),
      'userId'     => $request->getCookie($cookieName . '_u')
    );
  }

  protected function setCookies($params)
  {
    $cookieName = ConfigPeer::getCookieName();
    $domain = ConfigPeer::getCookieDomain();
    $lifetime = time() + sfConfig::get('app_phpbb_cookie_lifetime', 60 * 60 * 24 * 14);

    $response = sfContext::getInstance()->getResponse();
    $response->setCookie($cookieName . '_k',   $params['sessionKey'], $lifetime, '/', $domain);
    $response->setCookie($cookieName . '_u',   $params['userId'],     $lifetime, '/', $domain);
    $response->setCookie($cookieName . '_sid', $params['sessionId'],  $lifetime, '/', $domain);
  }
  
  protected function unsetCookies($cookieName)
  {
    $cookieName = ConfigPeer::getCookieName();
    $domain = ConfigPeer::getCookieDomain();

    $response = sfContext::getInstance()->getResponse();
    $response->setCookie($cookieName . '_k',   '', time() - 3600, '/', $domain);
    $response->setCookie($cookieName . '_u',   0,  time() - 3600, '/', $domain);
    $response->setCookie($cookieName . '_sid', '', time() - 3600, '/', $domain);
  }

  // proxy methods to the user instance

  public function __toString()
  {
    return $this->getPhpbbUser()->__toString();
  }
  
  public function __call($function, $args)
  {
    if (method_exists($this->getPhpbbUser(), $function)) {
      return call_user_func(array($this->getPhpbbUser(), $function));
    } else {
      throw new Exception("__call fails for " . $function . ' in avrPhpbbSecurityUser');
    }
  }
}