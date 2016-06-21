<?php

namespace Core;

/**
 * Filter
 *
 * Permits users to override data via callback hooks.
 *
 * @package core
 * @author stefano.azzolini@caffeinalab.com
 * @copyright Caffeina srl - 2015 - http://caffeina.it
 */

class Filter {
    use Module;

    protected static $_modders = [];

    public static function add($name,callable $modder = null){
        if( is_array($name) ) {
            foreach ($name as $key => $value) {
                static::$_modders[$key][] = $value;
            }
        } else {
            static::$_modders[$name][] = $modder;
        }
    }

    public static function single($name,callable $modder){
        static::$_modders[$name] = [$modder];
    }

    public static function remove($name,callable $modder = null){
        if($modder === null) {
            unset(static::$_modders[$name]);
        } else {
            if ($idx = array_search($modder,static::$_modders[$name],true))
                unset(static::$_modders[$name][$idx]);
        }
    }

    public static function with($names, $default){
      foreach ((array)$names as $name) {
        if (!empty(static::$_modders[$name])) {
            $value = $default;
            $args = array_slice( func_get_args(), 2 );
            foreach (static::$_modders[$name] as $modder) {
                $value = call_user_func( $modder,$value,$args );
            }
            return $value;
        }
      }
      return $default;
    }

}
