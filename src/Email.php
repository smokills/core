<?php

namespace Core;

use Core\Email\Envelope;

/**
 * Email
 *
 * Send messages via Email services.
 *
 * @package core
 * @author stefano.azzolini@caffeina.com
 * @copyright Caffeina srl - 2016 - http://caffeina.com
 */

class Email {
  use Module;

  protected static $driver,
                   $options,
                   $driver_name;

  public static function using($driver, $options = null){
    $class = 'Email\\'.ucfirst(strtolower($driver));
    if ( ! class_exists($class) ) throw new Exception("[core.email] : $driver driver not found.");
    static::$driver_name = $driver;
    static::$options     = $options;
    static::$driver      = new $class;
    static::$driver->onInit($options);
  }

  public static function create($mail=[]){
    if (is_a($mail, 'Email\\Envelope')){
      return $mail;
    } else {
      return new Envelope(array_merge([
        'to'          => false,
        'from'        => false,
        'cc'          => false,
        'bcc'         => false,
        'replyTo'     => false,
        'subject'     => false,
        'message'     => false,
        'attachments' => [],
      ], $mail));
    }
  }

  public static function send($mail){
    return static::$driver->onSend(static::create($mail));
  }

}

Email::using('native');
