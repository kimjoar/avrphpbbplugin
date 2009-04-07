<?php

/*
 * This file is part of the avrPhpbbPlugin package.
 * (c) 2009 Kim Joar Bekkelund <kjbekkelund@atmel.com>
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
class avrPhpbbAuthValidator extends sfValidator
{
  private $blockedKey;
  private $safetyMeasures;
  private $maxBlockTime;
  
  public function initialize($context, $parameters = null)
  {
    // initialize parent
    parent::initialize($context);

    // safety vars
    $this->blockedKey = 'signin_' . $context->getRequest()->getHttpHeader('addr', 'remote');
    $this->safetyMeasures = sfConfig::get('app_brute_force_enabled', true);
    $this->maxBlockTime = sfConfig::get('app_brute_force_timelimit', 60 * 60 * 24);

    // set defaults
    $this->getParameterHolder()->set('username_error', 'Username or password is not valid.');
    $this->getParameterHolder()->set('password_field', 'password');
    $this->getParameterHolder()->set('remember_field', 'remember');
    $this->getParameterHolder()->set('blocked', 'You are blocked, and therefore not able to log in the next ' . $this->maxBlockTime . ' seconds.');
    $this->getParameterHolder()->set('confirmation', 'You cannot log in until you have confirmed your user, you should have received an email with a confirmation link.');

    $this->getParameterHolder()->add($parameters);

    return true;
  }

  public function execute(&$value, &$error)
  {
    if ($this->isBlocked()) {
      $this->performSafetyMeasures();
      $error = $this->getParameterHolder()->get('blocked');
      return false;
    }

    $username = $value;

    $password_field = $this->getParameterHolder()->get('password_field');
    $password = $this->getContext()->getRequest()->getParameter($password_field);

    $remember_field = $this->getParameterHolder()->get('remember_field');
    $remember = $this->getContext()->getRequest()->getParameter($remember_field);

    $user = UserPeer::getUserByUsername($username);

    // user exists?
    // password is ok?
    if ($user && $user->checkPassword($password)) {
      if (!$user->isConfirmed()) {
        $error = $this->getParameterHolder()->get('confirmation');
        return false;
      }

      $this->getContext()->getUser()->signIn($user, $remember);
      $this->resetSafetyMeasures();
      return true;
    }

    $this->performSafetyMeasures();
    $error = $this->getParameterHolder()->get('username_error');
    return false;
  }
  
  private function isBlocked()
  {
    if (!$this->safetyMeasures) {
      return false;
    }

    $count = sfProcessCache::get($this->blockedKey);
    if ($count > sfConfig::get('app_brute_force_limit', 10)) {
      return true;
    }

    return false;
  }
  
  /**
   * To increase security we perform some safety measures.
   */
  private function performSafetyMeasures()
  {
    if (!$this->safetyMeasures) {
      return;
    }

    if (sfProcessCache::cacher()) {
      $count = sfProcessCache::has($this->blockedKey) ? sfProcessCache::get($this->blockedKey) + 1 : 1;
      sfProcessCache::set($this->blockedKey, $count, min(pow(2, $count) * 60, $this->maxBlockTime));
    }
  }

  private function resetSafetyMeasures()
  {
    if (!$this->safetyMeasures) {
      return;
    }

    if (sfProcessCache::cacher()) {
      sfProcessCache::set($this->blockedKey, 0, 0);
    }
  }
}
