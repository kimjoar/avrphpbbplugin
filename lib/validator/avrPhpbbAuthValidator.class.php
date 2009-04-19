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
  public function initialize($context, $parameters = null)
  {
    parent::initialize($context);

    // Set defaults
    $this->getParameterHolder()->set('username_error', 'Username or password is not valid.');
    $this->getParameterHolder()->set('password_field', 'password');
    $this->getParameterHolder()->set('remember_field', 'remember');
    $this->getParameterHolder()->set('activation', 'You cannot log in until you have activated your user, you should have received an email with a activation link.');
    $this->getParameterHolder()->add($parameters);

    return true;
  }

  public function execute(&$value, &$error)
  {
    $username = $value;

    $password_field = $this->getParameterHolder()->get('password_field');
    $password = $this->getContext()->getRequest()->getParameter($password_field);

    $remember_field = $this->getParameterHolder()->get('remember_field');
    $remember = $this->getContext()->getRequest()->getParameter($remember_field);

    $user = avrPhpbbUser::getUserByUsername($username);

    // user exists?
    // password is ok?
    if ($user && avrPhpbbUser::checkPassword($user, $password)) {
      if (!avrPhpbbUser::isActivated($user)) {
        $error = $this->getParameterHolder()->get('activation');
        return false;
      }

      $this->getContext()->getUser()->signIn($user, $remember);
      return true;
    }

    $error = $this->getParameterHolder()->get('username_error');

    return false;
  }
}