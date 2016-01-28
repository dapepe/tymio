<?php

/**
 * Provides PHP 5.3 and 6 compatibility.
 * Implemented dummy functions:
 * - {@link get_magic_quotes_gpc()}
 * - {@link json_last_error()}
 *
 * @author Huy Hoang Nguyen <hnguyen@cms-it.de>
 * @copyright Copyright (C) 2009 - 2011, CMS IT-Consulting GmbH. All rights reserved.
 * @package PROCABS
 */

/**
 * Wraps functions for magic quote settings to avoid E_DEPRECATED warnings.
 * Currently implemented functions:
 * - {@link get_magic_quotes_gpc()}
 *
 * Depends: {@link Util}
 */
class MagicQuotes {

	static private $magicQuotesGPC = null;

	/**
	 * Replacement function for the original {@link get_magic_quotes_gpc()}.
	 * Use this function to avoid E_DEPRECATED warnings.
	 *
	 * @return bool Returns the current setting.
	 */
	static public function getGpc() {
		if ( self::$magicQuotesGPC === null ) {
			if ( !function_exists('get_magic_quotes_gpc') ) {
				self::$magicQuotesGPC = false;
			} elseif ( version_compare(PHP_VERSION, '5.3.0') < 0 ) {
				self::$magicQuotesGPC = get_magic_quotes_gpc();
			} else {
				self::$magicQuotesGPC = Util::iniGetBool('magic_quotes_gpc');
				if ( self::$magicQuotesGPC === null )
					self::$magicQuotesGPC = @get_magic_quotes_gpc();
			}
		}

		return self::$magicQuotesGPC;
	}

}

if ( !function_exists('json_last_error') ) {

	if ( !defined('JSON_ERROR_NONE') )
		define('JSON_ERROR_NONE', 0);

	if ( !defined('JSON_ERROR_DEPTH') )
		define('JSON_ERROR_DEPTH', 1);

	if ( !defined('JSON_ERROR_CTRL_CHAR') )
		define('JSON_ERROR_CTRL_CHAR', 3);

	if ( !defined('JSON_ERROR_SYNTAX') )
		define('JSON_ERROR_SYNTAX', 4);

	function json_last_error() {
		return JSON_ERROR_NONE;
	}

}

if ( !function_exists('sys_get_temp_dir') ) {

	/**
	 * Emulation for PHP's native {@link sys_get_temp_dir()} function (PHP 5.2.1+).
	 *
	 * Here we assume that an empty environment variable (value === "") is
	 * the same as an unset variable (value === FALSE).
	 *
	 * @return string
	 */
	function sys_get_temp_dir() {
		if ( Util::isWindows() ) {
			// From http://msdn.microsoft.com/en-us/library/aa364992%28VS.85%29.aspx:
			// The GetTempPath function checks for the existence of environment
			// variables in the following order and uses the first path found:
			//    1. The path specified by the TMP environment variable.
			//    2. The path specified by the TEMP environment variable.
			//    3. The path specified by the USERPROFILE environment variable.
			//    4. The Windows directory.
			// Note that the function does not verify that the path exists,
			// nor does it test to see if the current process has any kind
			// of access rights to the path.
			foreach (array('TMP', 'TEMP', 'USERPROFILE', 'WINDIR') as $envName) {
				$directory = getenv($envName);
				if ( $directory != '' )
					return $directory;
			}
			return 'C:'.DIRECTORY_SEPARATOR.'WINDOWS';

		} else {
			// From the PHP source code:
			// ext/standard/file.c
			//   PHP_FUNCTION(sys_get_temp_dir)
			// main/php_open_temporary_file.c
			//   PHPAPI const char* php_get_temporary_directory(void)
			$directory = getenv('TMPDIR');
			return ( $directory == '' ? '/tmp' : $directory );

		}
	}

}

?>
