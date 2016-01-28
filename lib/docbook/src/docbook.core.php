<?php
namespace Docbook;

/**
 * Abstract parsing class for DocBook documents
 *
 * @uses \Xily\Xml
 * @uses \Xily\Base
 * @author Peter-Christoph Haider <peter.haider@zeyon.net>
 * @package docbook
 * @version 2.00 (2010-01-08)
 * @copyright Zeyon GmbH & Co. KG
 * @license GNU Public License
 */
abstract class Parser {
	/** @var array Specifies the conversion functions for a collection of tags ('function_name' => array('tag1', array('tag2', 'param1', ...), ...) */
	public $arrConverters = array(
		// Document-specific tags
		'article'           => 'doc',
		'tutorial'          => 'doc',
		'book'              => 'doc',
		'doc'               => 'content',
		'include'           => 'include',
		// Sections
		'chapter'           => 'chapter',
		'sect1'             => 'section',
		'sect2'             => 'section',
		'sect3'             => 'section',
		'sect4'             => 'section',
		// Content tags
		'para'              => 'para',
		'classname'         => array('inline', 'command'),
		'command'           => array('inline', 'command'),
		'code'              => array('inline', 'command'),
		'constant'          => array('inline', 'command'),
		'database'          => array('inline', 'command'),
		'filename'          => array('inline', 'command'),
		'function'          => array('inline', 'command'),
		'methodname'        => array('inline', 'command'),
		'sgmltag'           => array('inline', 'command'),
		'systemitem'        => array('inline', 'command'),
		'userinput'         => array('inline', 'command'),
		'emphasis'          => array('inline', 'emph'),
		'guibutton'         => array('inline', 'emph'),
		'guiicon'           => array('inline', 'emph'),
		'guilabel'          => array('inline', 'emph'),
		'guimenu'           => array('inline', 'emph'),
		'guimenuitem'       => array('inline', 'emph'),
		'guisubmenu'        => array('inline', 'emph'),

		'programlisting'    => 'listing',
		'link'              => 'link',
		'itemizedlist'      => 'itemizedlist',
		'orderedlist'       => 'orderedlist',
		'note'              => array('box', 'note'),
		'warning'           => array('box', 'warning'),
		// Media
		'inlinemediaobject' => 'inlinemediaobject',
		'screenshot'        => 'screenshot',
		'mediaobject'       => 'mediaobject',
		// Meta information
		'title'             => false
	);

	/** @var array Extend or modify $arrConverters with additional settings (for derivative classes) */
	public $arrExtend = array();
	/** @var array Contains the converter keys */
	public $arrConverterKeys = array();
	/** @var array Contains the table of contents */
	public $arrSections = array();
	/** @var string Path to the images (as defined in config.ini) */
	public $strImgPath = '';
	/** @var string URL to the images (as defined in config.ini) */
	public $strImgURL = '';
	/** @var string Path to the include files */
	public $strIncludePath = '';
	/** @var bool|string Parsing function for unknown tags */
	public $strUnknownHandler = false;
	/** @var \Xily\Xml The original DocBook document */
	public $xmlDoc;

	/* ============================== Major function ============================== */

	/**
	 * Contructs the docbook object
	 *
	 * @param string|\Xily\Xml $mxtDoc The \Xily\Xml object or an XML document or file
	 * @param bool $bolLoadFile Load the XML data from a file
	 */
	public function __construct($mxtDoc, $bolLoadFile=false) {
		$this->extend();
		$this->setImgPath(\Xily\Config::get('doc.imgpath'));

		if ( $mxtDoc instanceof \Xily\Xml )
			$this->xmlDoc = $mxtDoc;
		elseif ( !$this->xmlDoc = \Xily\Xml::create($mxtDoc, $bolLoadFile) )
			return false;

		$this->build();
		$this->arrConverterKeys = array_keys($this->arrConverters);

		if ( $bolLoadFile )
			$this->setIncludePath(realpath(dirname($mxtDoc)));

		return true;
	}

	/**
	 * The building function may vary in subclasses
	 */
	public function build() {}

	/**
	 * Sets the image path
	 *
	 * @param string $strPath
	 * @return void
	 */
	public function setImgPath($strPath) {
		$this->strImgPath = \Xily\Base::fileFormatDir($strPath);
	}

	/**
	 * Sets the image path
	 *
	 * @param string $strPath
	 * @return void
	 */
	public function setImgURL($strPath) {
		$this->strImgURL = \Xily\Base::fileFormatDir($strPath, '/');
	}

	/**
	 * Set the include path for additional XML documents
	 *
	 * @param string $strPath
	 * @return void
	 */
	public function setIncludePath($strPath) {
		$this->strIncludePath = \Xily\Base::fileFormatDir($strPath);
	}

	/**
	 * The extend function can be called by derivative classes in order to extend or modify the conversion array
	 *
	 * @return void
	 */
	public function extend() {
		$this->arrConverters = array_merge($this->arrConverters, $this->arrExtend);
	}

	/**
	 * Parses the main document node and returns the result
	 *
	 * @return string
	 */
	public function parse() {
		return $this->parseTag($this->xmlDoc);
	}

	/**
	 * Parses a single tag
	 *
	 * @param \Xily\Xml $xmlNode
	 * @return string
	 */
	public function parseTag($xmlNode) {
		if ( in_array($xmlNode->tag(), $this->arrConverterKeys) ) {
			if ( $cmd = $this->arrConverters[$xmlNode->tag()] ) {
				$params = array($xmlNode);
				if ( is_array($cmd) ) {
					$nparams = $cmd;
					$cmd = array_shift($nparams);
					$params = array_merge($params, $nparams);
				}
				$cmd = 'parse_'.$cmd;
				if ( method_exists($this, $cmd) ) {
					return call_user_func_array(array($this, $cmd), $params);
				} else {
					$this->log('Function "'.$cmd.'" not implemented for tag "'.$xmlNode->tag().'"');
					return;
				}
			}
		} else {
			$this->log('No converter set for tag "'.$xmlNode->tag().'"');
			if ( $this->strUnknownHandler ) {
				return call_user_func_array(array($this, $this->strUnknownHandler));
			}
		}
	}

	/**
	 * Adds a section in the table of contents
	 *
	 * @param \Xily\Xml $xmlNode
	 */
	public function addSection($xmlNode) {
		if ( $xmlNode->tag() == 'sect1' ) {
			$this->arrSections[] = array($xmlNode, array());
			return sizeof($this->arrSections);
		} else {
			$key1 = sizeof($this->arrSections);
			if ( $xmlNode->tag() == 'sect2' ) {
				$this->arrSections[$key1][1][] = array($xmlNode, array());
				return $key1.'.'.sizeof($this->arrSections[$key1][1]);
			} else {
				$key2 = sizeof($this->arrSections[$key1][1]);
				if ( $xmlNode->tag() == 'sect3' ) {
					$this->arrSections[$key1][1][$key2][1] = array($xmlNode, array());
					return $key1.'.'.$key2.'.'.sizeof($this->arrSections[$key1][1][$key2][1]);
				} else {
					$key3 = sizeof($this->arrSections[$key1][1][$key2][1]) - 1;
					if ( $xmlNode->tag() == 'sect4' ) {
						$this->arrSections[$key1][1][$key2][1][$key3][1] = array($xmlNode, array());
						return $key1.'.'.$key2.'.'.$key3.'.'.sizeof($this->arrSections[$key1][1][$key2][1][$key3][1]);
					}
				}
			}
		}

		return false;
	}

	/**
	 * Logs a message; This function can be replaced by a more suitable logging method
	 *
	 * @param string $strMessage
	 */
	public function log($strMessage) {
		// echo $strMessage.'<br />'."\n";
	}

	/* ============================== Parsing functions ============================== */

	/**
	 * Parses the mixed content of a XML node (CDATA and XML)
	 *
	 * @param \Xily\Xml $xmlNode The DocBook XML node
	 * @return string Translated HTML code
	 */
	public function parse_content($xmlNode) {
		$strResult = '';
		$arrContent = $xmlNode->content();
		foreach ($arrContent as $child) {
			if ( is_a($child, '\Xily\Xml') )
				$strResult .= $this->parseTag($child);
			elseif ( is_string($child) )
				$strResult .= $child;
		}
		return $strResult;
	}

	/**
	 * Includes another XML document
	 *
	 * @param \Xily\Xml $xmlNode
	 */
	public function parse_include($xmlNode) {
		if ( !$xmlNode->hasAttribute('file') || !file_exists($this->strIncludePath.$xmlNode->attribute('file')) )
			return '';

		return ($this->parseTag(\Xily\Xml::create($this->strIncludePath.$xmlNode->attribute('file'), 1)));
	}
}

?>
