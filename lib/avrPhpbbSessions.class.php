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
 */
class avrPhpbbSessions
{ 
  public static function createSession($params)
  {
    $prefix = sfConfig::get('app_avrPhpbb_prefix', 'Phpbb');

    $class = new ReflectionClass($prefix . 'Sessions');
    $session = $class->newInstance();

    $session->setSessionId($params['sessionId']);
    $session->setSessionUserId($params['userId']);
    $session->setSessionStart(time());
    $session->setSessionLastVisit(time());
    $session->setSessionTime(time());
    $session->setSessionBrowser($_SERVER['HTTP_USER_AGENT']);
    $session->setSessionIp(sfContext::getInstance()->getRequest()->getHttpHeader('addr', 'remote'));
    $session->setSessionAutologin(1);
    $session->setSessionAdmin(0);
    $session->setSessionViewonline(1);
    $session->save();    
  }

  public static function deleteSession($params)
  {
    $prefix = sfConfig::get('app_avrPhpbb_prefix', 'Phpbb');

    $c = new Criteria();
    myPropelTools::criteriaAdd($c, $prefix . 'Sessions', 'SESSION_USER_ID', $params['userId']);

    if ($params['sessionId']) {
      myPropelTools::criteriaAdd($c, $prefix . 'Sessions', 'SESSION_ID', md5($params['sessionId']));
    } else {
      myPropelTools::criteriaAdd($c, $prefix . 'Sessions', 'SESSION_IP', sfContext::getInstance()->getRequest()->getHttpHeader('addr', 'remote'));
    }

    myPropelTools::invokePeerMethod($prefix . 'Sessions', 'doDelete', $c);
  }

  public static function createSessionKey($params)
  {
    $prefix = sfConfig::get('app_avrPhpbb_prefix', 'Phpbb');

    $class = new ReflectionClass($prefix . 'SessionsKeys');
    $sessionKey = $class->newInstance();

    $sessionKey->setKeyId(md5($params['SessionsKeys']));
    $sessionKey->setUserId($params['userId']);
    $sessionKey->setLastIp(sfContext::getInstance()->getRequest()->getHttpHeader('addr', 'remote'));
    $sessionKey->setLastLogin(time());
    $sessionKey->save();
  }

  public static function deleteSessionKey($params)
  {
    $prefix = sfConfig::get('app_avrPhpbb_prefix', 'Phpbb');

    $c = new Criteria();
    myPropelTools::criteriaAdd($c, $prefix . 'SessionsKeys', 'USER_ID', $params['userId']);

    if ($params['SessionsKeys']) {
      myPropelTools::criteriaAdd($c, $prefix . 'SessionsKeys', 'LAST_IP', sfContext::getInstance()->getRequest()->getHttpHeader('addr', 'remote'));
    } else {
      myPropelTools::criteriaAdd($c, $prefix . 'SessionsKeys', 'KEY_ID', md5($params['SessionsKeys']));
      $c->add(SessionKeyPeer::KEY_ID, md5($params['SessionsKeys']));
    }
    
    myPropelTools::invokePeerMethod($prefix . 'SessionsKeys', 'doDelete', $c);
  }
  
  public static function checkCookie($cookieName)
  {
    $prefix = sfConfig::get('app_avrPhpbb_prefix', 'Phpbb');
    $request = sfContext::getInstance()->getRequest();

    $sessionKey = $request->getCookie($cookieName . '_k');
    $sessionId  = $request->getCookie($cookieName . '_sid');
    $userId     = $request->getCookie($cookieName . '_u');
    
    $c = new Criteria();
    
    myPropelTools::criteriaAdd($c, $prefix . 'Sessions', 'session_id', $sessionId);
    myPropelTools::criteriaAdd($c, $prefix . 'Sessions', 'session_user_id', $userId);
    $sessions = myPropelTools::invokePeerMethod($prefix . 'Sessions', 'doCount', $c);

    return ($sessions == 1);
  }
}