<?php

/**
 * HTTP-protocol-specific functions.
 *
 * @author Huy Hoang Nguyen <hnguyen@cms-it.de>
 * @copyright Copyright (C) 2009 - 2011, CMS IT-Consulting GmbH. All rights reserved.
 * @package PROCABS
 */

/**
 * HTTP-protocol-specific functions.
 *
 * NOTE:
 * There is no "exists()" function. Use "isset($_GET[...])" instead (same for
 * "$_POST"). A wrapper function would not add any functionality and would not
 * provide a complement to another function either.
 *
 * Dependencies: NONE
 */
class HTTP {

	static private $forwardCallback = null;

	/**
	 * Indicates whether {@link forwardTo()} has been called in this request.
	 *
	 * @var string
	 * @see forwarded()
	 * @see forwardTo()
	 */
	static private $forwardUrl = null;

	/**
	 * Removes line-feed, carriage-return and NUL characters from the input.
	 * This function should be called on user-supplied data which are about to
	 * be included in HTTP headers to protect against HTTP response splitting
	 * attacks.
	 *
	 * @param string $data
	 * @return string
	 */
	static public function sanitizeHeaderData($data) {
		return str_replace(array("\n", "\r", "\0"), array('', '', ''), $data);
	}

	/**
	 * Sends an HTTP status code (e.g. 404).
	 *
	 * @param int $code
	 * @param string $message Optional. Default is an empty string (no message).
	 * @return void
	 * @see sendStatus404NotFound()
	 */
	static public function sendStatusCode($code, $message = '') {
		header($_SERVER['SERVER_PROTOCOL'].' '.$code.( (string)$message == '' ? '' : ' '.self::sanitizeHeaderData($message) ));
	}

	/**
	 * Sends a HTTP 404 "Not Found" status code.
	 *
	 * @return void
	 * @see sendStatusCode()
	 */
	static public function sendStatus404NotFound() {
		self::sendStatusCode(404, 'Not Found');
	}

	/**
	 * Sends a "Content-Type" HTTP response header.
	 *
	 * @param string $type The MIME type to set, e.g. "text/html".
	 * @return void
	 */
	static public function setContentType($type) {
		header('Content-Type: '.self::sanitizeHeaderData($type));
	}

	/**
	 * Sends a "Content-Length" HTTP response header.
	 *
	 * @param int $length The content length.
	 * @return void
	 */
	static public function setContentLength($length) {
		header('Content-Length: '.(int)$length);
	}

	/**
	 * Sends a "Content-Disposition" header to initiate a file download prompt in the user's browser.
	 * For information on "Content-Disposition", see
	 * http://www.ietf.org/rfc/rfc2183.txt.
	 *
	 * @param string $fileName The file name.
	 * @param int $modificationDate Optional. A UNIX timestamp specifying the
	 *     date the file was last modified. If NULL, the current time will be
	 *     used. Default is NULL.
	 * @return void
	 */
	static public function forceDownload($fileName, $modificationDate = null) {
		if ( $modificationDate === null )
			$modificationDate = time();
		elseif ( !is_scalar($modificationDate) or
				 ((string)(int)$modificationDate !== (string)$modificationDate) )
			throw new Exception('Invalid modification date "'.$modificationDate.'".');
		header('Content-Disposition: attachment; filename='.urlencode($fileName).'; modification-date="'.date('r', (int)$modificationDate).'";');
	}

	/**
	 * Processes the supplied array and removes magic quotes if necessary.
	 *
	 * @param array $data Optional. If omitted or NULL, {@link $_GET} will be
	 *     assumed. Default is NULL (i.e. {@link $_GET}).
	 * @return array
	 * @see readGET()
	 * @see readPOST()
	 * @see MagicQuotes::getGpc()
	 */
	static public function readAll(array $data = null) {
		if ( $data === null )
			$data = $_GET;

		if ( !MagicQuotes::getGpc() )
			return $data;

		foreach ($data as $name => $value) {
			if ( is_string($value) )
				$data[$name] = stripslashes($value);
		}
		return $data;
	}

	/**
	 * Reads an HTTP GET variable.
	 * NOTES:
	 * - The result might be an array.
	 * - If magic_quotes_gpc is enabled, the function will strip all slashes
	 *   from the variable (if it is a string) before returning it.
	 *
	 * @param string $name The name of the GET variable.
	 * @param mixed $default Optional. The default value to return if the
	 *     specified variable is undefined. Default is NULL.
	 * @return mixed
	 * @see readPOST()
	 * @see readAll()
	 * @see $_GET
	 */
	static public function readGET($name, $default = null) {
		$value = ( isset($_GET[$name]) ? $_GET[$name] : $default );
		if ( is_string($value) and MagicQuotes::getGpc() )
			return stripslashes($value);
		else
			return $value;
	}

	/**
	 * Reads an HTTP POST variable.
	 * NOTES:
	 * - The result might be an array.
	 * - If magic_quotes_gpc is enabled, the function will strip all slashes
	 *   from the variable (if it is a string) before returning it.
	 *
	 * @param string $name The name of the POST variable.
	 * @param mixed $default Optional. The default value to return if the
	 *     specified variable is undefined. Default is NULL.
	 * @return mixed
	 * @see readGET()
	 * @see readAll()
	 * @see $_POST
	 */
	static public function readPOST($name, $default = null) {
		$value = ( isset($_POST[$name]) ? $_POST[$name] : $default );
		if ( is_string($value) and MagicQuotes::getGpc() )
			return stripslashes($value);
		else
			return $value;
	}

	/**
	 * Indicates whether {@link forwardTo()} has been called in this request.
	 *
	 * @return bool
	 * @see forwardTo()
	 * @uses $forwardUrl
	 */
	static public function forwarded() {
		return ( self::$forwardUrl !== null );
	}

	/**
	 * Returns the URL set by {@link forwardTo()}.
	 *
	 * @return string The URL set by {@link forwardTo()} or NULL if the
	 *     function was not called in this request.
	 */
	static public function getForwardUrl() {
		return self::$forwardUrl;
	}

	/**
	 * Defines the callback function to call when a redirect is made.
	 * This function is supplied with the original URL to forward to and is
	 * supposed to return the new URL to forward to.
	 *
	 * @param callback $callback
	 * @return string
	 * @see forwardTo()
	 */
	static public function setForwardCallback($callback) {
		self::$forwardCallback = $callback;
	}

	/**
	 * Redirects the browser to another URL and discards any open output buffers.
	 *
	 * @param string $url Optional. If NULL, redirects to the current page,
	 *     possibly clearing any existing HTTP POST data. Default is NULL.
	 * @return void
	 * @see forwarded()
	 * @uses $forwardUrl
	 */
	static public function forwardTo($url = null) {
		if ( self::$forwardUrl !== null )
			throw new Exception('Forwarding can only be called once during a request.');

		$url = (
			(string)$url == ''
			? $_SERVER['REQUEST_URI']
			: self::sanitizeHeaderData($url)
		);

		self::$forwardUrl = $url;

		if ( self::$forwardCallback !== null )
			$url = call_user_func(self::$forwardCallback, $url);

		error_log(__METHOD__.': URL="'.$url.'"');

		// Discard any open output buffers
		while ( ob_get_level() > 0 )
			ob_end_clean();

		// HTTP redirect
		header('Location: '.$url);
	}

	/**
	 * Checks whether HTTPS / SSL is enabled.
	 *
	 * @return bool Returns TRUE if the connection is SSL-encrypted, otherwise FALSE.
	 */
	static public function sslEnabled() {
		return (
			isset($_SERVER['HTTPS']) and
			(strcasecmp($_SERVER['HTTPS'], 'on') == 0)
		);
	}

	/**
	 * Checks if the browser has signalled the specified ETag as being cached.
	 *
	 * @param string $eTag
	 * @return bool
	 * @see sendCacheHeaders()
	 */
	static public function eTagCached($eTag) {
		return (
			isset($_SERVER['HTTP_IF_NONE_MATCH']) and
			($_SERVER['HTTP_IF_NONE_MATCH'] == $eTag)
		);
	}

	/**
	 * Sends HTTP cache headers.
	 *
	 * @param string $eTag An arbitrary string that uniquely identifies the
	 *     currently requested resource on the web server.
	 * @param int $maxCacheAge The max. time the resource can be cached.
	 * @param string $cacheControl Optional. Additional settings to add
	 *     to the "Cache-Control" header. Default is "private".
	 * @return void
	 */
	static public function sendCacheHeaders($eTag, $maxCacheAge, $cacheControl = 'private') {
		header('ETag: '.self::sanitizeHeaderData($eTag));
		header('Expires: '.date('r', time() + $maxCacheAge));
		header('Pragma:');
		header('Cache-Control: '.implode(', ', (array)$cacheControl).', max-age='.(int)$maxCacheAge);
	}

}

?>
