<?php

/**
 * Tools for Propel model classes.
 *
 * Copied from the Symfony snippet page, but adapted too our needs.
 * @see http://www.symfony-project.org/snippets/snippet/155
 */
class myPropelTools
{
  /**
   * Return the propel connection name.
   *
   * Usage:
   * $data = SomeClass:doSelect($criteria, myPropelTools::getConnection())
   *
   * @return string
   */
  public static function getConnection()
  {
    $connection = 'guest';

    if (sfContext::getInstance()->getUser()->isAuthenticated()) {
      $connection = 'propel';
    }

    myLogger::logEvent(__METHOD__ . ': connection=' . $connection, myLogger::LOGEVENT_DEBUG);
    return Propel::getConnection($connection);
  }

  public static function isPropelModelClass($class_name)
  {
    try {
      $class         = new ReflectionClass($class_name);
      $peerClass     = new ReflectionClass($class_name . 'Peer');
      $baseClass     = new ReflectionClass('Base' . $class_name);
      $basePeerClass = new ReflectionClass('Base' . $class_name . 'Peer');
    } catch(Exception $e) {
      return false;
    }

    return true;
  }

  public static function invokePeerMethod($class_name, $method)
  {
    if(!self::isPropelModelClass($class_name)) {
      throw new Exception('Class ' . $class_name  . ' is not valid Propel Model class');
    }

    $args = array_slice(func_get_args(), 2);
    return call_user_func_array(array($class_name . 'Peer', $method), $args);
  }

  public static function getFieldName($class_name, $field_name, $output_type = BasePeer::TYPE_PHPNAME)
  {
    return self::invokePeerMethod($class_name, 'translateFieldName', $field_name, BasePeer::TYPE_FIELDNAME, $output_type);
  }

  public static function getColumnName($class_name, $field_name)
  {
    list($table, $column) = explode('.', self::getFieldName($class_name, $field_name, BasePeer::TYPE_COLNAME), 2);

    return $column;
  }

  public static function setValue(&$object, $field_name, $value)
  {
    return self::invokeMethodName($object, 'set', $field_name, $value);
  }

  public static function getValue(&$object, $field_name)
  {
    return self::invokeMethodName($object, 'get', $field_name);
  }

  public static function getCriteria($class_name)
  {
    if(!self::isPropelModelClass($class_name)) {
      throw new Exception('Class ' . $class_name  . ' is not valid Propel Model class');
    }

    return new Criteria(constant($class_name.'Peer::DATABASE_NAME'));
  }

  public static function criteriaAddSelectColumn(&$criteria, $class_name, $field_name)
  {
    if(!self::isPropelModelClass($class_name)) {
      throw new Exception('Class ' . $class_name  . ' is not valid Propel Model class');
    }

    return $criteria->addSelectColumn(constant($class_name . 'Peer::' . self::getColumnName($class_name, $field_name)));
  }

  public static function criteriaAdd(&$criteria, $class_name, $field_name, $value = null, $comparison = null)
  {
    if(!self::isPropelModelClass($class_name)) {
      throw new Exception('Class ' . $class_name  . ' is not valid Propel Model class');
    }

    return $criteria->add(constant($class_name . 'Peer::' . self::getColumnName($class_name, $field_name)), $value, $comparison);
  }
  
  public static function criteriaAddJoin(&$criteria, $class_name1, $field_name1, $class_name2, $field_name2)
  {
    if(!self::isPropelModelClass($class_name1)) {
      throw new Exception('Class ' . $class_name1  . ' is not valid Propel Model class');
    }
    if(!self::isPropelModelClass($class_name2)) {
      throw new Exception('Class ' . $class_name2  . ' is not valid Propel Model class');
    }

    return $criteria->addJoin(constant($class_name1 . 'Peer::' . self::getColumnName($class_name1, $field_name1)), constant($class_name2 . 'Peer::' . self::getColumnName($class_name2, $field_name2)));
  }

  public static function getRowSet($class_name, $select_fields = null, $conditon_fields = null, $method = 'doSelectRS')
  {
    $c = self::getCriteria($class_name);

    if($select_fields) {
      $fields = (is_array($select_fields)) ? $select_fields : array($select_fields);

      foreach($fields as $field_name) {
        self::criteriaAddSelectColumn($c, $class_name, $field_name);
      }
    }

    if($conditon_fields) {
      if(is_array($conditon_fields) && count($conditon_fields)) {
         $conditons = (is_array($conditon_fields[0]))? $conditon_fields : array($conditon_fields);
      } else {
        throw new Exception("Condition parametr must be a not empty array (1 or 2 dimensional)");
      }

      foreach($conditons as $key => $conditon)
      {
        $field_name = null;
        $value = null;
        $comparison = null;

        switch(count($conditon)) {
          case 1:
            $field_name = $condition;
            break;
          case 2:
            list($field_name, $value) = $conditon;
            break;
          case 3:
            list($field_name, $value, $comparison) = $conditon;
            break;
          default:
            throw new Exception('Too many elements in condition #' . $key . 'specified');
        }

        self::criteriaAdd($c, $class_name, $field_name, $value, $comparison);
      }
    }

    return self::invokePeerMethod($class_name, $method, $c);
  }

  private static function getMethodName($class_name, $mode, $field_name)
  {
    return $mode . self::getFieldName($class_name, $field_name);
  }

  private static function invokeMethodName(&$object, $mode, $field_name, $value = '')
  {
    $methodName = self::getMethodName(get_class($object), $mode, $field_name);

    return ($mode == 'set') ? $object->$methodName($value) : $object->$methodName();
  }
}
