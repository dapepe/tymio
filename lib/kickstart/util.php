<?php

/**
 * Generic helper / utility functions that do not fit elsewhere.
 *
 * @author Huy Hoang Nguyen <hnguyen@cms-it.de>
 * @copyright Copyright (C) 2009 - 2011, CMS IT-Consulting GmbH. All rights reserved.
 * @package PROCABS
 */

/**
 * Generic utility functions.
 *
 * Dependencies: NONE
 *
 * @todo !!! Move code into classes "Platform", "Math", "File", "Directory" etc.
 */
class Util {

	/**
	 * PHP's default max. size of HTTP POST data in bytes.
	 *
	 * @see getMaxUploadSize()
	 */
	const PHP_INI_POST_MAX_SIZE_DEFAULT = 8388608;

	/**
	 * PHP's default max. size of an uploaded file in bytes.
	 *
	 * @see getMaxUploadSize()
	 */
	const PHP_INI_UPLOAD_MAX_FILESIZE_DEFAULT = 2097152;

	/**
	 * Maps byte unit suffixes to unit factors, e.g. "K" => 1024.
	 *
	 * @var array
	 * @see toBytes()
	 */
	static protected $phpByteUnitsBinary = array(
		'K' => 1024,
		'M' => 1048576,       // 1024 * 1024
		'G' => 1073741824,    // 1024 * 1024 * 1024
		'T' => 1099511627776, // 1024 * 1024 * 1024
	);

	/**
	 * SI byte unit suffixes.
	 *
	 * @var array
	 * @see formatBytes()
	 */
	static protected $siByteUnits = array('Bytes', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB');

	/**
	 * Lower-cased values of {@link PHP_OS} indicating a Windows-based system.
	 *
	 * @var array
	 */
	static protected $windowsOsNames = array(
		'win32'   => true,
		'winnt'   => true,
		'windows' => true,
	);

	/**
	 * Indicates whether the script is running on Windows.
	 * NULL means that the value is not yet initialized.
	 *
	 * @var bool
	 */
	static protected $isWindows = null;

	static protected $specialDirectories = array('.' => true, '..' => true);

	/**
	 * php.ini values that indicate a value of FALSE.
	 *
	 * @var array
	 * @see iniGetBool()
	 */
	static protected $phpIniFalseValues = array(
		'0'   => true,
		'off' => true,
		'no'  => true,
	);

	/**
	 * php.ini values that indicate a value of TRUE.
	 *
	 * @var array
	 * @see iniGetBool()
	 */
	static protected $phpIniTrueValues = array(
		'1'   => true,
		'on'  => true,
		'yes' => true,
	);

	/**
	 * Checks whether a value can be interpreted as an integer number.
	 *
	 * @param mixed $value
	 * @return bool Returns TRUE if the value can be interpreted as an integer
	 *     (signed or unsigned), otherwise FALSE.
	 * @see isUnsigned()
	 */
	static public function isInt($value) {
		return (
			is_scalar($value) and
			((string)(int)$value === (string)$value)
		);
	}

	/**
	 * Checks whether a value can be interpreted as an unsigned integer number.
	 *
	 * @param mixed $value
	 * @return bool Returns TRUE if the value can be interpreted as an unsigned
	 *     integer, otherwise FALSE.
	 * @see isInt()
	 * @see isUnsignedFloat()
	 */
	static public function isUnsigned($value) {
		return (
			is_scalar($value) and
			((string)(int)$value === (string)$value) and
			($value >= 0)
		);
	}

	/**
	 * Checks whether a value can be interpreted as an unsigned floating-point number.
	 *
	 * @param mixed $value
	 * @return bool Returns TRUE if the value can be interpreted as an unsigned
	 *     integer, otherwise FALSE.
	 * @see isUnsigned()
	 */
	static public function isUnsignedFloat($value) {
		return ( is_numeric($value) and ($value >= 0) );
	}

	/**
	 * Enforces a value to be within a specified integer range.
	 * <code>
	 *     echo Util::limitToIntRange(4, 1, 20)."\n";        // Prints 4
	 *     echo Util::limitToIntRange(4, 10, 20)."\n";       // Prints 10
	 *     echo Util::limitToIntRange('4', 1, 20)."\n";      // Prints 4
	 *     echo Util::limitToIntRange('bla', 1, 20)."\n";    // Prints "" (NULL)
	 *     echo Util::limitToIntRange('bla', 1, 20, 8)."\n"; // Prints 8
	 * </code>
	 *
	 * If {@link $min} > {@link $max}, {@link $default} will be returned.
	 *
	 * @param mixed $value The value to check.
	 * @param int $min Minimum allowed value. Optional. If omitted or NULL,
	 *     no lower limit will be applied. Default is NULL.
	 * @param int $max Maximum allowed value. Optional. If omitted or NULL,
	 *     no upper limit will be applied. Default is NULL.
	 * @param int $default Optional. If the supplied value cannot be interpreted
	 *     as an integer, this value will be returned. Default is NULL.
	 * @return int The adjusted value.
	 * @uses isInt()
	 */
	static public function limitToIntRange($value, $min = null, $max = null, $default = null) {
		if ( !self::isInt($value) )
			return $default;
		elseif ( $min > $max )
			return $default;
		elseif ( ($min !== null) and ($value < $min) )
			return $min;
		elseif ( ($max !== null) and ($value > $max) )
			return $max;
		else
			return $value;
	}

	/**
	 * Converts a permutation sequence to its enumeration index.
	 *
	 * The basic idea behind this function is that a permutation of the numbers
	 * 1 to n (or 0 to n - 1, respectively) form a sort of denominational
	 * number system with varying base (decreasing from left to right).
	 * The left-most sequence item may be chosen out of n distinct items
	 * while the second-to-left is selected among n - 1, and so on.
	 * The item's numerical value is its index in the lexically-ordered
	 * character set of the permutation (e.g. for a permutation "2 1 3", the
	 * characters are "1", "2" and "3", having indexes 0, 1 and 2, respectively).
	 *
	 * Sequence Index
	 * 1 2 3    0       0 0
	 * 1 3 2    1       0 1
	 * 2 1 3    2       1 0
	 * 2 3 1    3       1 1
	 * 3 1 2    4       2 0
	 * 3 2 1    5       2 1
	 *
	 * @param array $p The permutation sequence.
	 * @return int
	 */
	static public function permutationToInt(array $p) {
		$numbers = $p;
		sort($numbers);
		$numbers = array_flip(array_merge($numbers));

		if ( count($numbers) !== count($p) )
			throw new Exception('Permutation sequence contains duplicates: ['.implode(', ', $p).']');

		// Remove last element in permutation sequence
		array_pop($p);

		$result = 0;

		foreach ($p as $digit) {
			// "$numbers[$digit]" always exists by definition because "$numbers"
			// consists of all elements occuring in "$p", and "$p" has been
			// verified to be free of any duplicates.
			$result = $numbers[$digit] + $result * count($numbers);

			// Remove digit from available numbers
			unset($numbers[$digit]);

			// Reindex. This causes a runtime of O(n^2) but could be optimized
			// a bit by updating only the array part "to the right" of
			// "$numbers[$digit]".
			$numbers = array_flip(array_merge(array_keys($numbers)));
		}

		return $result;
	}

	/**
	 * Converts a number with a byte unit suffix (e.g. "8M") to bytes.
	 * This function assumes binary units, i.e. 1 KB = 1024 bytes etc.
	 * Supports the suffixes "K"/"KB", "M"/"MB", "G"/"GB" and "T"/"TB".
	 *
	 * @param string $numberWithUnit
	 * @return int The number without the unit or FALSE on failure.
	 * @see formatBytes()
	 * @uses $phpByteUnitsBinary
	 */
	static public function toBytes($numberWithUnit) {
		if ( preg_match('`^(\d+)\s*(?:([KMGT])i?B?)?$`i', trim($numberWithUnit), $matches) ) {

			if ( $matches[2] == '' )
				return $matches[1];

			$unitSuffix = strtoupper($matches[2]);
			if ( isset(self::$phpByteUnitsBinary[$unitSuffix]) )
				return $matches[1] * self::$phpByteUnitsBinary[$unitSuffix];
		}

		return false;
	}

	/**
	 * Converts a byte figure to a figure with units, e.g. 4827 becomes 4.71 KiB.
	 *
	 * @param number $bytes
	 * @param int $decimals Optional. The number of decimals to round to.
	 *     If NULL, no rounding is done. Default is 2.
	 * @return string
	 * @see toBytes()
	 * @uses $siByteUnits
	 */
	static public function formatBytes($bytes, $decimals = 2) {
		$index = (int)floor(log($bytes) / log(1024));

		$suffix = '';
		if ( isset(self::$siByteUnits[$index]) ) {
			$bytes = (double)$bytes / pow(1024, $index);
			$suffix = ' '.self::$siByteUnits[$index];
		}

		if ( self::isInt($decimals) )
			$bytes = number_format($bytes, ( $index === 0 ? 0 : $decimals ));
		else
			$bytes = number_format($bytes);

		return $bytes.$suffix;
	}

	/**
	 * Gets the max. size of a file upload allowed by PHP settings in bytes.
	 *
	 * The max. size is limited by the PHP settings "post_max_size" and
	 * "upload_max_filesize", whichever is smaller.
	 *
	 * NOTE:
	 * The returned value may be larger than the max. size allowed by your
	 * web server or firewall.
	 *
	 * @return int The max. allowed size, or FALSE if it is unknown.
	 * @uses toBytes()
	 * @uses PHP_INI_POST_MAX_SIZE_DEFAULT
	 * @uses PHP_INI_UPLOAD_MAX_FILESIZE_DEFAULT
	 */
	static public function getMaxUploadSize() {
		$postMaxSize = ini_get('post_max_size');
		if ( $postMaxSize == '' ) // non-strict comparison
			$postMaxSize = self::PHP_INI_POST_MAX_SIZE_DEFAULT;

		$postMaxSize = self::toBytes($postMaxSize);

		$uploadMaxFilesize = ini_get('upload_max_filesize');
		if ( $uploadMaxFilesize == '' )
			$uploadMaxFilesize = self::PHP_INI_UPLOAD_MAX_FILESIZE_DEFAULT;

		$uploadMaxFilesize = self::toBytes($uploadMaxFilesize);

		if ( $postMaxSize === false )
			return $uploadMaxFilesize;
		elseif ( $uploadMaxFilesize === false )
			return false;
		else
			return min($postMaxSize, $uploadMaxFilesize);
	}

	/**
	 * Checks if PHP is running on Windows.
	 *
	 * @return bool
	 */
	static public function isWindows() {
		if ( self::$isWindows === null )
			self::$isWindows = isset(self::$windowsOsNames[strtolower(PHP_OS)]);

		return self::$isWindows;
	}

	static public function directoryEmpty($path) {
		$dh = @opendir($path);
		if ( !is_resource($dh) )
			return false;

		while ( ($entry = @readdir($dh)) !== false ) {
			if ( !isset(self::$specialDirectories[$entry]) ) {
				closedir($dh);
				return false;
			}
		}

		closedir($dh);
		return true;
	}

	/**
	 * Checks whether the given path is the root directory.
	 *
	 * @return bool
	 */
	static public function isRootDirectory($path) {
		return (
			empty($path) or
			in_array($path, array(DIRECTORY_SEPARATOR, '/')) or
			(realpath($path) === realpath(DIRECTORY_SEPARATOR)) or
			(self::isWindows() and preg_match('`^[a-z]:[/\\\\]?$`i', $path))
		);
	}

	static private function doDeleteDirectory($directory, $contentsOnly = false) {
		$dh = @opendir($directory);
		if ( !is_resource($dh) )
			return false;

		while ( ($entry = readdir($dh)) !== false ) {
			$path = $directory.DIRECTORY_SEPARATOR.$entry;
			if ( is_file($path) and @unlink($path) ) {
			} elseif ( is_dir($path) ) {
				if ( !isset(self::$specialDirectories[$entry]) and
					 !self::doDeleteDirectory($path) ) {
					closedir($dh);
					return false;
				}
			} else {
				// Not a regular file, directory or file not deletable
				closedir($dh);
				return false;
			}
		}

		closedir($dh);

		return ( $contentsOnly ? true : @rmdir($directory) );
	}

	/**
	 * Recursively deletes a directory.
	 * The function does not follow symlinks and will only delete folders and
	 * regular files.
	 *
	 * @param string $directory
	 * @param bool $contentsOnly Optional. If TRUE, only directory's contents
	 *     will be deleted but not the directory itself. Default is FALSE.
	 * @return bool
	 * @see clear()
	 */
	static public function deleteDirectory($directory, $contentsOnly = false) {
		if ( self::isRootDirectory($directory) )
			return false;
		else
			return self::doDeleteDirectory($directory, $contentsOnly);
	}

	static protected function doCopyDirectory($from, $to) {
		$dh = @opendir($from);
		if ( !is_resource($dh) )
			return false;

		while ( ($entry = readdir($dh)) !== false ) {
			$fromPath = $from.DIRECTORY_SEPARATOR.$entry;
			$toPath   = $to.DIRECTORY_SEPARATOR.$entry;

			if ( is_file($fromPath) ) {
				if ( !@copy($fromPath, $toPath) ) {
					closedir($dh);
					return false;
				}
			} elseif ( is_dir($fromPath) ) {
				if ( !isset(self::$specialDirectories[$entry]) ) {
					@mkdir($toPath);
					if ( !is_dir($toPath) or !self::doCopyDirectory($fromPath, $toPath) ) {
						closedir($dh);
						return false;
					}
				}
			} else {
				// Not a regular file / directory
				closedir($dh);
				return false;
			}
		}

		closedir($dh);

		return true;
	}

	static public function copyDirectory($from, $to) {
		// This is required for recursive mkdir() to work
		$from = str_replace('/', DIRECTORY_SEPARATOR, $from);
		$to   = str_replace('/', DIRECTORY_SEPARATOR, $to);

		@mkdir($to, 0777, true);
		if ( !is_dir($to) )
			return false;

		return self::doCopyDirectory($from, $to);
	}

	static public function fileDelete($path) {
		return ( is_file($path) ? @unlink($path) : false );
	}

	static public function fileRead($path, $length) {
		$f = fopen($path, 'rb');
		if ( !is_resource($f) )
			return false;

		$result = '';
		$bytesLeft = $length;
		while ( !feof($f) and ($bytesLeft > 0) ) {
			$fragment = fread($f, $bytesLeft);
			$result .= $fragment;
			$bytesLeft -= strlen($fragment);
		}
		fclose($f);

		return ( $bytesLeft === 0 ? $result : false );
	}

	static public function filesExist($files) {
		foreach ((array)$files as $file) {
			if ( !file_exists($file) )
				return false;
		}
		return true;
	}

	/**
	 * Returns a "php.ini" setting as a boolean value.
	 *
	 * @param string $name
	 * @return bool The value as a boolean or NULL on errors.
	 * @uses ini_get()
	 */
	static public function iniGetBool($name) {
		$rawValue = ini_get($name);
		if ( is_bool($rawValue) )
			return $rawValue;

		$value = strtolower((string)$rawValue);
		if ( isset(self::$phpIniTrueValues[$value]) )
			return true;
		elseif ( isset(self::$phpIniFalseValues[$value]) )
			return false;
		else
			return null;
	}

}

?>
