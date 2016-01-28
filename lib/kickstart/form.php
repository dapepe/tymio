<?php

/**
 * Functions for creating and handling (X)HTML forms.
 *
 * @author Huy Hoang Nguyen <hnguyen@cms-it.de>
 * @copyright Copyright (C) 2009 - 2011, CMS IT-Consulting GmbH. All rights reserved.
 * @package PROCABS
 */

/**
 * Functions for creating and handling (X)HTML forms.
 *
 * Dependencies:
 * - {@link HTTP}
 * - {@link HTML}
 * - {@link RecentList}
 * - {@link Session}
 */
class Form {

	/**
	 * Specifies that {@link textBox()} should not use any HTTP GET/POST variables to fill the text box.
	 * @see textBox()
	 */
	const METHOD_NONE = null;

	/**
	 * HTTP POST method.
	 *
	 * @see begin()
	 * @see verify()
	 */
	const METHOD_POST = 'post';

	/**
	 * HTTP GET method.
	 *
	 * @see begin()
	 * @see verify()
	 */
	const METHOD_GET = 'get';

	/**
	 * The character set to use with {@link entities()}.
	 * Default is "UTF-8".
	 *
	 * @var string
	 */
	static protected $characterSet = 'UTF-8';

	/**
	 * The max. number of tokens to simultaneously keep in the session per user.
	 *
	 * @var int
	 * @see setTokenLimit()
	 */
	static protected $tokenLimit = 100;

	/**
	 * The session variable name prefix to use.
	 *
	 * @var string
	 * @see begin()
	 * @see verify()
	 */
	static protected $sessionTokenName = 'system.token.';

	/**
	 * The name of the hidden form input variable to use in {@link begin()}.
	 *
	 * @var string
	 * @see begin()
	 * @see verify()
	 */
	static protected $tokenName = '__token';

	static protected $hiddenInputs = array();

	static public function setTokenLimit($limit) {
		self::$tokenLimit = $limit;
	}

	/**
	 * Returns the session token name.
	 *
	 * @return string
	 * @see setSessionTokenName()
	 */
	static public function getSessionTokenName() {
		return self::$sessionTokenName;
	}

	/**
	 * Sets the name of the session variable holding the tokens.
	 *
	 * @param string $name
	 * @return void
	 * @see getSessionTokenName()
	 * @see begin()
	 * @see verify()
	 * @uses $sessionTokenName
	 */
	static public function setSessionTokenName($name) {
		self::$sessionTokenName = $name;
	}

	/**
	 * Returns the name of the HTTP GET/POST variable to use for the CSRF token.
	 *
	 * @return string
	 * @see getToken()
	 */
	static public function getTokenName() {
		return self::$tokenName;
	}

	/**
	 * Returns a token for a specified purpose.
	 * The token will be created and persisted in the session if it does not
	 * already exist, otherwise the existing token value will be returned.
	 *
	 * @param string $purpose
	 * @return string
	 * @see getTokenName()
	 */
	static public function getToken($purpose) {
		$recentList = Session::get(self::$sessionTokenName);
		if ( !($recentList instanceof RecentList) ) {
			$recentList = new RecentList(self::$tokenLimit);
			Session::set(self::$sessionTokenName, $recentList);
		}

		if ( $recentList->exists($purpose) ) {
			// Re-use existing token value
			$tokenID = $recentList->get($purpose);
			$recentList->touch($purpose);
		} else {
			// Create new token value
			$tokenID = uniqid(mt_rand());
			$recentList->set($purpose, $tokenID);
		}

		return $tokenID.':'.$purpose;
	}

	/**
	 * Returns the value of the specified hidden input previously set via {@link addHiddenInput()} or {@link addHiddenInputs()}.
	 *
	 * @param string $name
	 * @param string $default Optional. The value to return if the specified
	 *     input is not defined. Default is NULL.
	 * @return string
	 * @see getHiddenInputs()
	 * @see addHiddenInput()
	 * @see addHiddenInputs()
	 */
	static public function getHiddenInput($name, $default = null) {
		return (
			array_key_exists($name, self::$hiddenInputs)
			? self::$hiddenInputs[$name]
			: $default
		);
	}

	static public function getHiddenInputs() {
		return self::$hiddenInputs;
	}

	static public function clearHiddenInputs() {
		self::$hiddenInputs = array();
	}

	/**
	 * Adds a hidden input that will be incorporated into forms when using {@link begin()} or {@link beginString()}.
	 *
	 * @param string $name
	 * @param string $value
	 * @return void
	 * @see addHiddenInputs()
	 */
	static public function addHiddenInput($name, $value) {
		self::$hiddenInputs[$name] = $value;
	}

	static public function addHiddenInputs(array $inputs) {
		self::$hiddenInputs = array_merge(self::$hiddenInputs, $inputs);
	}

	/**
	 * Returns a <form> tag with a hidden input element.
	 * The token is stored in the session for verification.
	 * The hidden input element has the name defined in {@link $tokenName}.
	 *
	 * @param string $purpose The purpose to bind the token to. The purpose
	 *     should be unique across the entire site and be different for each
	 *     page to ensure that you cannot use a regular edit token for changing
	 *     a user's password, for example.
	 * @param string $action The destination URL to send the form data to.
	 * @param string $method Optional. Default is {@link METHOD_POST}.
	 * @param array $attributes Optional. Associative array of attributes to
	 *     add to the form. All names (keys) and values will be quoted.
	 *     Default is NULL.
	 * @param array $hiddenInputs Optional. An associative array specifying
	 *     "<input type="hidden" />" elements with the names indexing the values.
	 *     Default is NULL.
	 * @return string
	 * @see end()
	 * @uses $sessionTokenName
	 */
	static public function beginString($purpose, $action, $method = self::METHOD_POST, array $attributes = null, array $hiddenInputs = null) {
		if ( $attributes === null )
			$attributes = array();
		$attributeText = '';
		foreach ($attributes as $name => $value) {
			$attributeText .= ' '.HTML::entities($name).'="'.HTML::entities($value).'"';
		}

		if ( $hiddenInputs === null )
			$hiddenInputs = self::$hiddenInputs;
		else
			$hiddenInputs = array_merge(self::$hiddenInputs, $hiddenInputs);

		if ( $purpose !== null )
			$hiddenInputs[self::$tokenName] = self::getToken($purpose);

		$hiddenInputText = '';
		foreach ($hiddenInputs as $name => $value)
			$hiddenInputText .= '<input type="hidden" name="'.HTML::entities($name).'" value="'.HTML::entities($value).'" />';

		// "<input />" tags must be enclosed in one of those blocks (according
		// to the standard): "p", "h1", "h2", "h3", "h4", "h5", "h6", "div",
		// "pre", "address", "fieldset", "ins", "del"
		return
			'<form method="'.HTML::entities($method).'" action="'.HTML::entities($action).'"'.$attributeText.'>'.
			'<div style="display:none;">'.$hiddenInputText.'</div>';
	}

	/**
	 * Displays and/or returns a <form> tag with a hidden input element.
	 * The token is stored in the session for verification.
	 * The hidden input element has the name defined in {@link $tokenName}.
	 *
	 * @param string $purpose The purpose to bind the token to. The purpose
	 *     should be unique across the entire site and be different for each
	 *     page to ensure that you cannot use a regular edit token for changing
	 *     a user's password, for example.
	 * @param string $action The destination URL to send the form data to.
	 * @param string $method Optional. Default is {@link METHOD_POST}.
	 * @param array $attributes Optional. Associative array of attributes to
	 *     add to the form. All names (keys) and values will be quoted.
	 *     Default is NULL.
	 * @param array $hiddenInputs Optional. Default is NULL.
	 * @return void
	 * @see end()
	 * @uses $sessionTokenName
	 */
	static public function begin($purpose, $action, $method = self::METHOD_POST, array $attributes = null, array $hiddenInputs = null) {
		echo self::beginString($purpose, $action, $method, $attributes, $hiddenInputs);
	}

	/**
	 * Returns a </form> tag.
	 *
	 * @return string
	 * @see begin()
	 */
	static public function endString() {
		return '</form>';
	}

	/**
	 * Displays and/or returns a </form> tag.
	 *
	 * @return void string
	 * @see begin()
	 */
	static public function end() {
		echo self::endString();
	}

	/**
	 * Verifies a form token and deletes it thereafter.
	 *
	 * @param string $purpose The purpose to check against.
	 * @param string $method Optional. Default is {@link METHOD_POST}.
	 * @param bool $persist Optional. If TRUE, the CSRF protection token will
	 *     not be invalidated by this call. Default is FALSE.
	 * @return bool Returns TRUE if the token is valid, otherwise FALSE.
	 * @see begin()
	 * @see verifyPersist()
	 * @uses $sessionTokenName
	 */
	static public function verify($purpose, $method = self::METHOD_POST, $persist = false) {
		$recentList = Session::get(self::$sessionTokenName);
		if ( !($recentList instanceof RecentList) )
			return false;

		$tokenValue = (
			$method === self::METHOD_POST
			? HTTP::readPOST(self::$tokenName)
			: HTTP::readGET(self::$tokenName)
		);

		if ( !preg_match('`^([^:]+):'.preg_quote($purpose, '`').'`', $tokenValue, $matches) )
			return false;

		$tokenID = $matches[1];

		// Token with the purpose stated in the form must exist in the session
		if ( $recentList->get($purpose) !== $tokenID )
			return false;

		if ( !$persist ) {
			// Remove token to prevent re-use
			$recentList->delete($purpose);
		}

		return true;
	}

	/**
	 * Verifies a CSRF protection token without invalidating it.
	 *
	 * @param string $purpose The purpose to check against.
	 * @param string $method Optional. Default is {@link METHOD_POST}.
	 * @return bool Returns TRUE if the token is valid, otherwise FALSE.
	 * @see verify()
	 */
	static public function verifyPersist($purpose, $method = self::METHOD_POST) {
		return self::verify($purpose, $method, true);
	}

	/**
	 * Returns an "<input type="text" />" and pre-fills it with HTTP POST/GET data, or the value if the former is missing.
	 *
	 * @param string $name The input field name.
	 * @param mixed $value Optional. The value to use if no matching HTTP
	 *     POST/GET variable exists. Default is an empty string.
	 * @param array $attributes Optional. Additional attributes to include.
	 *     You can use this e.g. to set a tag ID or to override the "type"
	 *     attribute. Default is NULL.
	 * @param string $method Optional. Can be one of these values:
	 *     - {@link METHOD_NONE}: Use the specified default value.
	 *     - {@link METHOD_GET}: Use the corresponding HTTP GET variable if
	 *       available, otherwise fall back to the default value.
	 *     - {@link METHOD_POST}: Use the corresponding HTTP POST variable if
	 *       available, otherwise fall back to the default value.
	 *     Default is {@link METHOD_POST}.
	 * @return string
	 */
	static public function textBox($name, $value = '', array $attributes = null, $method = self::METHOD_POST) {
		if ( $attributes === null )
			$attributes = array();

		if ( !isset($attributes['type']) )
			$attributes['type'] = 'text';

		$attributes['name']  = $name;
		switch ( $method ) {
			case self::METHOD_GET:
				$attributes['value'] = HTTP::readGET($name, $value);
				break;
			case self::METHOD_POST:
				$attributes['value'] = HTTP::readPOST($name, $value);
				break;
			case self::METHOD_NONE:
			default:
				$attributes['value'] = $value;
				break;
		}

		return '<input '.HTML::expandAttributes($attributes).' />';
	}

	/**
	 * Returns a string of options.
	 * This function supports option groups ("<optgroup>"). An option group is
	 * defined as an array of options.
	 *
	 * @param array $options An associative array of options. The keys contain
	 *     the captions while the array elements contain the values.
	 * @param string $selectedValue
	 * @return string
	 * @see select()
	 */
	static private function expandOptions(array $options, $selectedValue) {
		$optionString = '';

		foreach ($options as $caption => $value) {
			if ( !is_array($value) ) {
				// Only a simple value specified
				$optionString .= '<option value="'.HTML::entities($value).'"'.( $value == $selectedValue ? ' selected="selected"' : '' ).'>'.HTML::entities($caption).'</option>';
			} elseif ( array_key_exists(0, $value) ) {
				// Array with element [0] => value and additional XHTML tag attributes
				// This check must come after determining that "$value" is
				// an array - the former would hold for strings, too.
				$val = array_shift($value);
				$optionString .= '<option value="'.HTML::entities($val).'"'.( $val == $selectedValue ? ' selected="selected"' : '' ).' '.HTML::expandAttributes($value).'>'.HTML::entities($caption).'</option>';
			} else {
				// Array without element [0] => option group
				$optionString .= '<optgroup label="'.HTML::entities($caption).'">'.self::expandOptions($value, $selectedValue).'</optgroup>';
			}
		}

		return $optionString;
	}

	/**
	 * Returns a "<select></select>" box and selects the option matching the HTTP POST/GET data, or the value if the former is missing.
	 *
	 * @param string $name The <select> tag name.
	 * @param array $options An associative array of options. The keys contain
	 *     the captions while the array elements contain the values. You can
	 *     define an option group ("<optgroup>") by specifying an array of
	 *     options as the value. For options with additional XHTML attributes,
	 *     specify an array with the option value as the very first element and
	 *     additional attributes thereafter.
	 *     Example:
	 *     <code>
	 *         select('pizza', array(
	 *             'Regular' => array(
	 *                 'Margherita'    => array('margherita', 'title' => '50% Off Special'),
	 *                 'Funghi'        => 'funghi',
	 *                 'Prosciutto'    => 'prosciutto',
	 *                 'Salame'        => 'salame',
	 *             ),
	 *             'Specials' => array(
	 *                 'Mexican Style' => 'mexican',
	 *                 'Spicy Curry'   => 'curry',
	 *             ),
	 *         ));
	 *     </code>
	 * @param mixed $value Optional. The value to select if no matching HTTP
	 *     POST/GET variable exists. Default is NULL.
	 * @param array $attributes Optional. Additional attributes to include.
	 *     You can use this e.g. to set a tag ID or to override the "type"
	 *     attribute. Default is NULL.
	 * @param string $method Optional. Default is {@link METHOD_POST}.
	 * @return string
	 * @uses expandOptions()
	 */
	static public function select($name, array $options, $value = null, array $attributes = null, $method = self::METHOD_POST) {
		$selectedValue = (
			$method == self::METHOD_POST
			? HTTP::readPOST($name, $value)
			: HTTP::readGET($name, $value)
		);

		return
			'<select name="'.HTML::entities($name).'" '.HTML::expandAttributes($attributes).'>'.
			self::expandOptions($options, $selectedValue).
			'</select>';
	}

}

?>
