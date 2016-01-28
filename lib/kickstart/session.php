<?php

/**
 * Wrapper for session management.
 *
 * @author Huy Hoang Nguyen <hnguyen@cms-it.de>
 * @copyright Copyright (C) 2009 - 2011, CMS IT-Consulting GmbH. All rights reserved.
 * @package PROCABS
 */

/**
 * Wrapper for session management.
 *
 * NOTE:
 * There is no "exists()" function. Use "array_key_exists($key, $_SESSION)" instead.
 * A wrapper function would not add any functionality and would not provide
 * a complement to another function either.
 *
 * Dependencies: NONE
 */
class Session {

	static private $opened = false;

	/**
	 * Begins or resumes a session.
	 *
	 * @param string $name Optional.
	 * @return bool
	 */
	static public function start($name = null) {
		if ( $name !== null )
			session_name($name);

		// Use "@" operator to suppress warnings about HTTP headers being
		// already sent when the session is closed and resumed after producing
		// some output.
		self::$opened = @session_start();

		return self::$opened;
	}

	/**
	 * Stores and closes a session.
	 *
	 * @return void
	 */
	static public function end() {
		session_write_close();
		self::$opened = false;
	}

	/**
	 * Indicates whether a session is currently open.
	 *
	 * @return bool
	 */
	static public function opened() {
		return self::$opened;
	}

	/**
	 * Destroys the current session.
	 *
	 * @return bool
	 */
	static public function destroy() {
		// Invalidate cookie if one exists
		if ( isset($_COOKIE[session_name()]) and !headers_sent() )
			setcookie(session_name(), '', time() - 42000, '/');

		if ( session_destroy() ) {
			self::$opened = false;
			return true;
		}

		return false;
	}

	/**
	 * Regenerates the session ID, keeping the current session data.
	 * This is a wrapper for {@link session_regenerate_id()}.
	 *
	 * @return bool
	 */
	static public function regenerate() {
		return session_regenerate_id(true);
	}

	/**
	 * Returns the specified session variable.
	 * If the variable does not exist, the default value is returned instead.
	 * The function will temporarily reopen the session for the duration
	 * of the call if it is closed.
	 *
	 * @param string $name The name of the variable to get.
	 * @param mixed $default Optional. Default is NULL.
	 * @return void
	 */
	static public function get($name, $default = null) {
		if ( self::$opened ) {
			return ( array_key_exists($name, $_SESSION) ? $_SESSION[$name] : $default );
		} else {
			Session::start();
			$result = ( array_key_exists($name, $_SESSION) ? $_SESSION[$name] : $default );
			Session::end();
			return $result;
		}
	}

	/**
	 * Sets a session variable.
	 * The function will temporarily reopen the session for the duration
	 * of the call if it is closed.
	 *
	 * @param string $name The name of the variable to set.
	 * @param mixed $value The value to set the variable to.
	 * @return void
	 */
	static public function set($name, $value) {
		if ( self::$opened ) {
			$_SESSION[$name] = $value;
		} else {
			Session::start();
			$_SESSION[$name] = $value;
			Session::end();
		}
	}

	/**
	 * Adds an item to an array stored in the session data.
	 *
	 * @param string $name The name of the array variable in the session.
	 * @param mixed $value The value to add to the array.
	 * @param mixed $key Optional. Specifies the array key. If NULL, a numeric
	 *     array key is used. Default is NULL.
	 * @return void
	 */
	static public function push($name, $value, $key = null) {
		if ( !array_key_exists($name, $_SESSION) )
			$_SESSION[$name] = array();

		if ( $key === null )
			$_SESSION[$name][] = $value;
		else
			$_SESSION[$name][$key] = $value;
	}

	/**
	 * Increases a variable.
	 *
	 * @param mixed $name
	 * @param int $step Optional. The number to increase by. Default is 1.
	 * @return void
	 */
	static public function increase($name, $step = 1) {
		if ( !array_key_exists($name, $_SESSION) )
			return ( $_SESSION[$name] = $step );
		else
			return ( $_SESSION[$name] += $step );
	}

	/**
	 * Deletes a session variable.
	 *
	 * @param string $name
	 * @return void
	 * @see deleteAll()
	 */
	static public function delete($name) {
		unset($_SESSION[$name]);
	}

	/**
	 * Deletes all session variables.
	 *
	 * @return void
	 * @see delete()
	 */
	static public function deleteAll() {
		session_unset();
	}

}

?>
