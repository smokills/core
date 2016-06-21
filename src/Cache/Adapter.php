<?php

namespace Core\Cache;

/**
 * Cache\Adapter
 *
 * Cache drivers common interface.
 *
 * @package core
 * @author stefano.azzolini@caffeinalab.com
 * @copyright Caffeina srl - 2015 - http://caffeina.it
 */

interface Adapter  {
  public function get($key);
  public function set($key,$value,$expire=0);
  public function delete($key);
  public function exists($key);
  public function flush();

  public function inc($key,$value=1);
  public function dec($key,$value=1);

  public static function valid();
}