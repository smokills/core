<?php

namespace Core;

/**
 * Request
 *
 * Handles the HTTP request for the current execution.
 *
 * @package core
 * @author stefano.azzolini@caffeinalab.com
 * @copyright Caffeina srl - 2015 - http://caffeina.it
 */

class Request {
  use Module;

  protected static $body,
                   $accepts;

  /**
   * Handle Content Negotiation requests
   *
   * @param  string $key The name of the negotiation subject
   * @param  string $choices A query string for the negotiation choices (See RFC 7231)
   *
   * @return Object The preferred content if $choices is empty else return best match
   */
  public static function accept($key='type',$choices=''){
    if (null === static::$accepts) static::$accepts = [
      'type'     => new Negotiation(isset($_SERVER['HTTP_ACCEPT'])          ? $_SERVER['HTTP_ACCEPT']          : ''),
      'language' => new Negotiation(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : ''),
      'encoding' => new Negotiation(isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : ''),
      'charset'  => new Negotiation(isset($_SERVER['HTTP_ACCEPT_CHARSET'])  ? $_SERVER['HTTP_ACCEPT_CHARSET']  : ''),
    ];
    return empty(static::$accepts[$key])
      ? false
      : ( empty($choices)
           ? static::$accepts[$key]->preferred()
           : static::$accepts[$key]->best($choices)
      );
  }

  /**
   * Retrive a value from generic input (from the $_REQUEST array)
   * Returns all elements if you pass `null` as $key
   *
   * @param  string $key The name of the input value
   *
   * @return Object The returned value or $default.
   */
  public static function input($key=null,$default=null){
    return $key ? (isset($_REQUEST[$key]) ? new Object($_REQUEST[$key]) : (is_callable($default)?call_user_func($default):$default))  : new Object($_REQUEST[$key]);
  }

  /**
   * Retrive a value from environment (from the $_ENV array)
   * Returns all elements if you pass `null` as $key
   *
   * @param  string $key The name of the input value
   *
   * @return Object The returned value or $default.
   */
  public static function env($key=null,$default=null){
    return $key ? (filter_input(INPUT_ENV,$key) ?: (is_callable($default)?call_user_func($default):$default))  : $_ENV;
  }

  /**
   * Retrive a value from server (from the $_SERVER array)
   * Returns all elements if you pass `null` as $key
   *
   * @param  string $key The name of the input value
   *
   * @return Object The returned value or $default.
   */
  public static function server($key=null,$default=null){
    return $key ? (filter_input(INPUT_SERVER,$key) ?: (is_callable($default)?call_user_func($default):$default))  : $_SERVER;
  }

  /**
   * Retrive a value from generic input (from the $_POST array)
   * Returns all elements if you pass `null` as $key
   *
   * @param  string $key The name of the input value
   *
   * @return Object The returned value or $default.
   */
  public static function post($key=null,$default=null){
    return $key ? (filter_input(INPUT_POST,$key) ?: (is_callable($default)?call_user_func($default):$default))  : $_POST;
  }

  /**
   * Retrive a value from generic input (from the $_GET array)
   * Returns all elements if you pass `null` as $key
   *
   * @param  string $key The name of the input value
   *
   * @return Object The returned value or $default.
   */
  public static function get($key=null,$default=null){
    return $key ? (filter_input(INPUT_GET,$key) ?: (is_callable($default)?call_user_func($default):$default))  : $_GET;
  }

  /**
   * Retrive uploaded file (from the $_FILES array)
   * Returns all uploaded files if you pass `null` as $key
   *
   * @param  string $key The name of the input value
   *
   * @return Object The returned value or $default.
   */
  public static function files($key=null,$default=null){
    return $key ? (isset($_FILES[$key]) ? $_FILES[$key] : (is_callable($default)?call_user_func($default):$default))  : $_FILES;
  }

  /**
   * Retrive cookie (from the $_COOKIE array)
   * Returns all cookies if you pass `null` as $key
   *
   * @param  string $key The name of the input value
   *
   * @return Object The returned value or $default.
   */
  public static function cookie($key=null,$default=null){
    return $key ? (filter_input(INPUT_COOKIE,$key) ?: (is_callable($default)?call_user_func($default):$default))  : $_COOKIE;
  }

  /**
   * Returns the current host, complete with protocol (pass `false` to omit).
   *
   * @return string
   */
  public static function host($protocol=true){
    $host = filter_input(INPUT_SERVER,'HOSTNAME') ?: (
          filter_input(INPUT_SERVER,'SERVER_NAME') ?:
          filter_input(INPUT_SERVER,'HTTP_HOST')
    );
    return ($protocol ? 'http' . (filter_input(INPUT_SERVER,'HTTPS')?'s':'') . '://' : '') . Filter::with('core.request.host',$host);
  }

  /**
   * Returns the current request URL, complete with host and protocol.
   *
   * @return string
   */
  public static function URL(){
    return static::host(true) . static::URI(false);
  }

  /**
   * Retrive header
   * Returns all headers if you pass `null` as $key
   *
   * @param  string $key The name of the input value
   *
   * @return Object The returned value or null.
   */
  public static function header($key=null,$default=null){
    $key = 'HTTP_'.strtr(strtoupper($key),'-','_');
    return $key ? (filter_input(INPUT_SERVER,$key) ?: (is_callable($default)?call_user_func($default):$default)) : $_SERVER;
  }

  /**
   * Returns the current request URI.
   *
   * @param  boolean $relative If true, trim the URI relative to the application index.php script.
   *
   * @return string
   */
  public static function URI($relative=true){
    // On some web server configurations PHP_SELF is not populated.
    $self = filter_input(INPUT_SERVER,'SCRIPT_NAME') ?: filter_input(INPUT_SERVER,'PHP_SELF');
    // Search REQUEST_URI in $_SERVER
    $serv_uri = filter_input(INPUT_SERVER,'PATH_INFO') ?: (
          filter_input(INPUT_SERVER,'ORIG_PATH_INFO') ?:
          filter_input(INPUT_SERVER,'REQUEST_URI')
    );
    $uri = strtok($serv_uri,'?');
    $uri = ($uri == $self) ? '/' : $uri;

    // Add a filter here, for URL rewriting
    $uri = Filter::with('core.request.URI',$uri);

    $uri = rtrim($uri,'/');

    if ($relative){
      $base = rtrim(dirname($self),'/');
      $uri = str_replace($base,'',$uri);
    }

    return $uri ?: '/';
  }

  /**
   * Returns the current base URI (The front-controller directory)
   *
   * @return string
   */
  public static function baseURI(){
    // On some web server configurations PHP_SELF is not populated.
    $uri = dirname(filter_input(INPUT_SERVER,'SCRIPT_NAME') ?: filter_input(INPUT_SERVER,'PHP_SELF'));
    return $uri ?: '/';
  }

  /**
   * Returns the HTTP Method
   *
   * @return string
   */
  public static function method(){
   return Filter::with('core.request.method',strtolower(filter_input(INPUT_SERVER,'REQUEST_METHOD')?:'get'));
  }

  /**
   * Returns the remote IP
   *
   * @return string
   */
  public static function IP(){
   return Filter::with('core.request.IP',strtolower(filter_input(INPUT_SERVER,'REMOTE_ADDR')?:''));
  }

  /**
   * Returns the remote UserAgent
   *
   * @return string
   */
  public static function UA(){
   return Filter::with('core.request.UA',strtolower(filter_input(INPUT_SERVER,'HTTP_USER_AGENT')?:''));
  }


  /**
   * Returns request body data, convert to object if content type is JSON
   * Gives you all request data if you pass `null` as $key
   *
   * @param  string $key The name of the key requested
   *
   * @return mixed The request body data
   */
  public static function data($key=null,$default=null){
    if (null===static::$body){
      $json = (false !== stripos(filter_input(INPUT_SERVER,'HTTP_CONTENT_TYPE'),'json'))
           || (false !== stripos(filter_input(INPUT_SERVER,'CONTENT_TYPE'),'json'));
      if ($json) {
        static::$body = json_decode(file_get_contents("php://input"));
      } else {
       if (empty($_POST)) {
          static::$body = file_get_contents("php://input");
        } else {
          static::$body = (object)$_POST;
        }
      }
    }
    return $key ? (isset(static::$body->$key) ? static::$body->$key : (is_callable($default)?call_user_func($default):$default))  : static::$body;
  }

}
