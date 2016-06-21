<?php

namespace Core;

/**
 * Session
 *
 * Manage PHP sessions.
 *
 * @package core
 * @author stefano.azzolini@caffeinalab.com
 * @copyright Caffeina srl - 2015 - http://caffeina.it
 */

class Session {
    use Module;

	/**
	 * Start session handler
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	static public function start($name=null){
		if (isset($_SESSION)) return;
		static::name($name);
    // Obfuscate IDs
    ini_set('session.hash_function', 'whirlpool');
		session_cache_limiter('must-revalidate');
		@session_start();
	}

	/**
	 * Get/Set Session name
	 *
	 * @access public
	 * @static
	 * @param string $key The session name
	 * @return string The session value
	 */
	static public function name($name=null){
		return $name ? session_name($name) : session_name();
	}

	/**
	 * Get a session variable reference
	 *
	 * @access public
	 * @static
	 * @param mixed $key The variable name
	 * @return mixed The variable value
	 */
	static public function get($key,$default=null){
                if (($active = static::active()) && isset($_SESSION[$key])) {
			return $_SESSION[$key];
                } else if ($active) {
                	return $_SESSION[$key] = (is_callable($default)?call_user_func($default):$default);
                } else {
                 	return (is_callable($default)?call_user_func($default):$default);
                }
	}

	/**
	 * Set a session variable
	 *
	 * @access public
	 * @static
	 * @param mixed $key The variable name
	 * @param mixed $value The variable value
	 * @return void
	 */
	static public function set($key,$value=null){
		static::start();
		if($value==null && is_array($key)){
			foreach($key as $k=>$v) $_SESSION[$k]=$v;
		} else {
			$_SESSION[$key] = $value;
		}
	}

	/**
	 * Delete a session variable
	 *
	 * @access public
	 * @static
	 * @param mixed $key The variable name
	 * @return void
	 */
	static public function delete($key){
		static::start();
		unset($_SESSION[$key]);
	}


	/**
	 * Delete all session variables
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	static public function clear(){
		static::start();
		session_unset();
		session_destroy();
	}

	/**
	 * Check if session is active
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	static public function active(){
		return session_status() == PHP_SESSION_ACTIVE;
	}

	/**
	 * Check if a session variable exists
	 *
	 * @access public
	 * @static
	 * @param mixed $key The variable name
	 * @return bool
	 */
	static public function exists($key){
		static::start();
		return isset($_SESSION[$key]);
	}

	/**
	 * Return a read-only accessor to session variables for in-view use.
	 * @return SessionReadOnly
	 */
	static public function readOnly(){
		return new SessionReadOnly;
	}

}  /* End of class */



/**
 * Read-only Session accessor class
 */

class SessionReadOnly {

	/**
	 * Get a session variable reference
	 *
	 * @access public
	 * @param mixed $key The variable name
	 * @return mixed The variable value
	 */
	public function get($key){
		return Session::get($key);
	}
	public function __get($key){
		return Session::get($key);
	}

	public function name(){
		return Session::name();
	}

	/**
	 * Check if a session variable exists
	 *
	 * @access public
	 * @param mixed $key The variable name
	 * @return bool
	 */
	public function exists($key){
		return Session::exists($key);
	}
	public function __isset($key){
		return Session::exists($key);
	}

}  /* End of class */
