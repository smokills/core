<?php

namespace Core\View;

/**
 * View\Adapter
 *
 * Core\View\Adapter Interface.
 *
 * @package core
 * @author stefano.azzolini@caffeinalab.com
 * @copyright Caffeina srl - 2015 - http://caffeina.it
 */

interface Adapter {
    public function __construct($path=null, $options=[]);
    public function render($template,$data=[]);
    public static function exists($path);
    public static function addGlobal($key,$val);
    public static function addGlobals(array $defs);
}
