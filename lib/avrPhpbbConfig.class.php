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
class avrPhpbbConfig
{
  public static function getCookieDomain()
  {
    $domain = self::getConfigValueFor('cookie_domain');

    if ($domain && '' != $domain) {
      return '.' . $domain;
    }

    return '';
  }

  public static function getCookieName()
  {
    return self::getConfigValueFor('cookie_name');
  }

  /**
   * Get the relative path to phpBB
   *
   * @return string
   */
  public static function getScriptPath()
  {
    return self::getConfigValueFor('script_path');
  }

  public static function getDefaultDateformat()
  {
    return self::getConfigValueFor('default_dateformat');
  }

  public static function getServerProtocol()
  {
    return self::getConfigValueFor('server_protocol');
  }

  public static function getServerName()
  {
    return self::getConfigValueFor('server_name');
  }

  public static function getForumUrl()
  {
    $protocol   = self::getServerProtocol();
    $server     = self::getServerName();
    $scriptPath = self::getScriptPath();

    return $protocol . $server . $scriptPath;
  }

  /**
   * @todo Should we throw an exception if the config value does not exist?
   */
  public static function getConfigValueFor($input)
  {
    $cache = sfConfig::get('app_avrPhpbb_cache', false);

    if (!$cache) {
      $configValues = self::getConfigValues();

      if (isset($configValues[$input])) {
        return $configValues[$input];
      }
    }

    if ($cache) {
      $cacheKeyNonDynamic = 'config_values';
      $cacheKeyDynamic    = 'config_values_dynamic';

      if (sfProcessCache::has($cacheKeyNonDynamic)) {
        $configValues = sfProcessCache::get($cacheKeyNonDynamic);
      } else {
        $configValues = self::getConfigValues(0);
        sfProcessCache::set($cacheKeyNonDynamic, $configValues, myTools::getConfig('app_cache_config_non_dynamic', 86400));
      }

      if (isset($configValues[$input])) {
        return $configValues[$input];
      }

      // Reset config values before we check dynamic config values
      unset($configValues);

      if (sfProcessCache::has($cacheKeyDynamic)) {
        $configValues = sfProcessCache::get($cacheKeyDynamic);
      } else {
        $configValues = self::getConfigValues(1);
        sfProcessCache::set($cacheKeyDynamic, $configValues, myTools::getConfig('app_cache_config_dynamic', 60));
      }
    
      if (isset($configValues[$input])) {
        return $configValues[$input];
      }
    }
    
    // If we get here, there is no config value for this input.
    return null;
  }

  private static function getConfigValues($dynamic = null)
  {
    $prefix = sfConfig::get('app_avrPhpbb_prefix', 'Phpbb');
    $c = new Criteria();

    if (is_null($dynamic)) {
      myPropelTools::criteriaAdd($c, $prefix . 'Config', 'is_dynamic', $dynamic ? 1 : 0);
    }
    
    $configValues = array();
    foreach (myPropelTools::invokePeerMethod($prefix . 'Config', 'doSelect', $c) AS $config) {
      $configValues[$config->getConfigName()] = $config->getConfigValue();
    }

    return $configValues;
  }

  public static function setConfigValueFor($input, $value)
  {
    $prefix = sfConfig::get('app_avrPhpbb_prefix', 'Phpbb');
    $c = new Criteria();

    myPropelTools::criteriaAdd($c, $prefix . 'Config', 'config_name', $input);

    $newestUserConfig = myPropelTools::invokePeerMethod($prefix . 'Config', 'doSelectOne', $c);

    $newestUserConfig->setConfigValue($value);
    $newestUserConfig->save();

    sfProcessCache::delete('config_values');
    sfProcessCache::delete('config_values_dynamic');
  }
}