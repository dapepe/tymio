<?php

/**
 * HTML/XHTML-related utility functions.
 *
 * @author Huy Hoang Nguyen <hnguyen@cms-it.de>
 * @copyright Copyright (C) 2009 - 2011, CMS IT-Consulting GmbH. All rights reserved.
 * @package PROCABS
 */

/**
 * HTML/XHTML-related utility functions.
 *
 * Dependencies:
 * - http.php
 * - recentlist.php
 * - session.php
 */
class HTML {

	/**
	 * The character set to use with {@link entities()}.
	 * Default is "UTF-8".
	 *
	 * @var string
	 */
	static protected $characterSet = 'UTF-8';

	/**
	 * Gets the character set currently used by {@link entities()}.
	 *
	 * @return string
	 * @see entities()
	 * @see setCharacterSet()
	 */
	static public function getCharacterSet() {
		return self::$characterSet;
	}

	/**
	 * Sets the character set to use with {@link entities()}.
	 *
	 * @param string $characterSet
	 * @return void
	 * @see entities()
	 * @see getCharacterSet()
	 */
	static public function setCharacterSet($characterSet) {
		self::$characterSet = $characterSet;
	}

	/**
	 * Convenience wrapper to call {@link htmlentities()} with a predefined character set.
	 *
	 * @param string/array $text The text or an array to escape.
	 * @param int $quoteStyle Optional. Default is {@link ENT_QUOTES}.
	 * @return string The escaped string.
	 * @see getCharacterSet()
	 * @see setCharacterSet()
	 * @see cssUrl()
	 */
	static public function entities($text, $quoteStyle = ENT_QUOTES) {
		if ( !is_array($text) )
			return htmlentities($text, $quoteStyle, self::$characterSet);

		$result = array();
		foreach ($text as $name => $value)
			$result[$name] = htmlentities($value, $quoteStyle, self::$characterSet);
		return $result;
	}

	/**
	 * Escapes a URL for use in CSS "url(...)".
	 *
	 * According to {@link http://www.w3.org/TR/REC-CSS1-961217#url},
	 * "Parentheses, commas, whitespace characters, single quotes (') and
	 * double quotes (") appearing in a URL must be escaped with a backslash:
	 * '\(', '\)', '\,'."
	 *
	 * If the URL is going to be used in an inline CSS "style" attribute,
	 * convert the URL with this function first, then pipe it through
	 * {@link entities()} as shown here:
	 * <code>
	 *     echo '<div style="background:url('.HTML::entities(HTML::cssUrl($url)).');"></div>';
	 * </code>
	 *
	 * @param string $url The URL to escape.
	 * @return string
	 * @see entities()
	 */
	static public function cssUrl($url) {
		return preg_replace('`([(),\s\'"\\\\])`', '\\$1', $url);
	}

	/**
	 * Expands an associative array to an (X)HTML tag attribute string.
	 *
	 * @param array $attributes If NULL, an empty string will be returned.
	 * @return string
	 */
	static public function expandAttributes(array $attributes = null) {
		if ( $attributes === null )
			return '';

		$result = array();
		foreach ($attributes as $name => $value)
			$result[] = self::entities($name).'="'.self::entities($value).'"';

		return implode(' ', $result);
	}

	/**
	 * Extends the "class" attribute in the {@link $attributes} array or sets it.
	 *
	 * @param array $attributes An associative array of (X)HTML tag attributes.
	 * @param array/string $classes CSS classes to add to the "class" attribute.
	 * @return array The extended attributes array.
	 * @see expandAttributes()
	 */
	static public function addClasses(array $attributes = null, $classes) {
		if ( $attributes === null )
			$attributes = array();
		if ( !isset($attributes['class']) )
			$attributes['class'] = '';

		if ( is_array($classes) )
			$attributes['class'] .= ' '.implode(' ', $classes);
		else
			$attributes['class'] .= ' '.$classes;

		return $attributes;
	}

}

?>
