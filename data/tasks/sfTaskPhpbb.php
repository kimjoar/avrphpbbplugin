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
 * @subpackage Tasks
 * @author     Kim Joar Bekkelund <kjbekkelund@atmel.com>
 */

pake_desc('transform the database schema for phpBB');
pake_task('phpbb-transform', 'project_exists');

function _phpbb_get_schemas()
{
  $finder = pakeFinder::type('file')->ignore_version_control()->name('*schema.yml')->prune('doctrine');
  $dirs = array('config');
  if ($pluginDirs = glob(sfConfig::get('sf_root_dir').'/plugins/*/config'))
  {
    $dirs = array_merge($dirs, $pluginDirs);
  }
  $schemas = $finder->in($dirs);
  if ($check_schema && !count($schemas))
  {
    throw new Exception('You must create a schema.yml file.');
  }
  
  return $schemas;
}

/**
* Rebuilds the entire search index.
*/
function run_phpbb_transform($task, $args)
{
//  _phpbb_standard_load($args);

  if (count($args) != 1) {
    throw new sfException('Prefix is the only accepted input.');
  }

  $prefix = $args[0];

  $schemas = _phpbb_get_schemas();
  
  foreach ($schemas AS $schema) {
    $yaml = sfYaml::load($schema);
    
    $tmp = array_keys($yaml);
    $connection_name = array_shift($tmp);

    if (!$connection_name) {
      return;
    }

    $database = $yaml[$connection_name];
    
    foreach($database AS $table => $columns) {
      if (!strpos($table, $prefix) == 0) {
        continue;
      }

      foreach ($columns AS $column => $attributes) {
/*
        $specific = array(
          'phpbb_acl_groups' => array('auth_option_id' => 'phpbb_acl_options', 'auth_role_id' => 'phpbb_acl_roles'),
          'phpbb_acl_options' => array('auth_option_id' => NULL, ),
          'phpbb_acl_roles' => array('role_id' => NULL,),
          'phpbb_extension_groups' => array('group_id' => NULL, ),
          'phpbb_extensions' => array('group_id' => 'phpbb_extension_groups'),
        );
*/
              
        $pos = strpos($column, '_id');
        if ($pos > 0 && $pos == strlen($column) - 3) {
          $raw = substr($column, 0, $pos);

          // possibilites:
          // 1. prefix + raw
          // 2. prefix + raw + 2
          // 3. parent, left or right
          if ($database[$prefix . $raw]) {
            $foreign_table = $prefix . $raw;
          } elseif ($database[$prefix . $raw . 's']) {
            $foreign_table = $prefix . $raw . 's';
          }

          if ($foreign_table == $table) {
            continue;
            unset($foreign_table);
          }

          if ($raw == 'parent' || $raw == 'left' || $raw == 'right') {
            $foreign_table = $table;
          }
          
          if (!$foreign_table) {
            echo '# ' . $column . " (" . $table . ")\n";
          } else {
            echo $column . " (" . $table . ") -> " . $foreign_table . "\n";
          }
          
          unset($foreign_table);
        }
      }
    }
  }
  die;
}

  function _phpbb_get_foreign_table($foreign_table, $prefix)
  {
    $tables = array(
      'auth_option' => '',
      'auth_role' => '',
      'role' => '',
      'attach' => '',
      'post_msg' => '',
      'poster' => '',
      'ban' => '',
      'cat' => '',
      'parent' => '',
      'left' => '',
      'right' => '',
      'forum_last_post' => '',
      'forum_last_poster' => '',
      'reportee' => '',
      'parent' => '',
      'left' => '',
      'right' => '',
      'vote_user' => '',
      'poster' => '',
      'msg' => '',
      'author' => '',
      'folder' => '',
      'rule' => '',
      'rule_user' => '',
      'rule_group' => '',
      'rule_folder' => '',
      'msg' => '',
      'author' => '',
      'folder' => '',
      'field' => '',
      'field' => '',
      'option' => '',
      'field' => '',
      'reason' => '',
      'reason' => '',
      'session_user' => '',
      'session_forum' => '',
      'key' => '',
      'site' => '',
      'smiley' => '',
      'template' => '',
      'theme' => '',
      'imageset' => '',
      'imageset' => '',
      'image' => '',
      'imageset' => '',
      'template' => '',
      'template_inherits' => '',
      'template' => '',
      'theme' => '',
      'topic_first_post' => '',
      'topic_last_post' => '',
      'topic_last_poster' => '',
      'topic_moved' => ''
    );
  }

/*
  $start_time = microtime(true);

  $instances = sfLucene::getAllInstances(true);

  foreach ($instances as $instance)
  {
    echo pakeColor::colorize(sprintf('Processing "%s/%s" now...', $instance->getName(), $instance->getCulture()), array('fg' => 'red', 'bold' => true)) . "\n";

    lucene_rebuild_search($instance);

    echo "\n";
  }

  $total_time = microtime(true) - $start_time;

  echo pakeColor::colorize('All done! ', array('fg' => 'red', 'bold' => true));

  echo 'Rebuilt for ';
  echo pakeColor::colorize(count($instances), array('fg' => 'cyan'));

  if (count($instances) == 1)
  {
    echo ' index in ';
  }
  else
  {
    echo ' indexes in ';
  }

  echo pakeColor::colorize(sprintf('%f', $total_time), array('fg' => 'cyan'));
  echo ' seconds.';

  echo "\n";
*/

/*
function lucene_rebuild_search($search)
{
  $start_time = microtime(true);

  pake_echo_action('lucene', 'Created new index');
  pake_echo_action('lucene', 'Rebuilding...');

  $search->rebuildIndex();

  pake_echo_action('lucene', 'Optimizing...');

  $search->optimize();

  pake_echo_action('lucene', 'Committing...');

  $search->commit();

  $execution_time = microtime(true) - $start_time;

  echo pakeColor::colorize('Done!', 'INFO') . " ";

  echo 'Indexed ';
  echo pakeColor::colorize($search->numDocs(), array('fg' => 'cyan'));
  echo ' documents in ';

  echo pakeColor::colorize(sprintf('%f', $execution_time), array('fg' => 'cyan'));

  echo ' seconds.';

  echo "\n";
}
*/
pake_desc('initialize a avrPhpbb module that you can overload');
pake_task('phpbb-init-module', 'project_exists');

function run_phpbb_init_module($task, $args)
{
  _phpbb_standard_load($args);

  $skeleton_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'.DIRECTORY_SEPARATOR.'skeleton';

  $module_dir = sfConfig::get('sf_app_module_dir');

  $finder = pakeFinder::type('any')->ignore_version_control();
  pake_mirror($finder, $skeleton_dir.DIRECTORY_SEPARATOR.'module', $module_dir);
}

function _phpbb_standard_load($args)
{
  if (!count($args))
  {
    throw new sfException('You must provide an app.');
  }

  _phpbb_load_application_environment($args);
}

function _phpbb_check_app($app)
{
  if (!is_dir(sfConfig::get('sf_app_dir') . DIRECTORY_SEPARATOR . $app))
  {
    throw new sfException('The app "' . $app . '" does not exist.');
  }
}

function _phpbb_load_application_environment($args)
{
  static $loaded;

  if (!$loaded)
  {
    _phpbb_check_app($args[0]);

    define('SF_ROOT_DIR', sfConfig::get('sf_root_dir'));
    define('SF_APP', $args[0]);
    define('SF_ENVIRONMENT', !empty($args[1]) ? $args[1] : 'search');
    define('SF_DEBUG', true);

    require_once SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';

    sfContext::getInstance();

    sfConfig::set('pake', true);

    error_reporting(E_ALL);

    $loaded = true;
  }
}