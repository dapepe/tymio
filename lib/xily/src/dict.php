<?php
namespace Xily;

// Shortcut...
class Dict extends Dictionary {}

/**
 * The Dictionary class provides some useful functions when working with arrays.
 * In order to avoid errors, I would suggest to pack most assoc. arrays you
 * are using in this class
 *
 * @author Peter-Christoph Haider (Project Leader) et al. <info@xily.info>
 * @version 1.3 (2011-03-02)
 * @package xily
 * @copyright Copyright (c) 2009-2011, Peter-Christoph Haider
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @license http://www.xily.info/ Commercial License
 */
class Dictionary extends Base {
	/** @var array The main array which is containing the data */
	public $data = array();

	/**
	 * Constructor
	 *
	 * @param array $arr
	 * @return void
	 */
	public function __construct($arr=array()) {
		if (is_array($arr))
			$this->data = $arr;
		else
			throw new Exception('Wrong data type. Xily\Dictionary is expecting an array for initialization!');
	}

	/**
	 * Adds an array to the dataset
	 *
	 * @param array $arr
	 * @return void
	 */
	public function merge($arr) {
		$this->data = array_merge($arr, $this->data);
	}

	/**
	 * Only adds additional settings to the data set
	 *
	 * @param arr $arr
	 * @param bool $keep Keep current values
	 * @return void
	 */
	public function add($arr, $keep=false) {
		foreach ($arr as $attr => $value)
			if (!$this->has($attr) || !$keep)
				$this->set($attr, $value);
	}

	/**
	 * Pushes a new value to the array
	 *
	 * @param string $value
	 * @return void
	 */
	public function push($value) {
		$this->data[] = $value;
	}

	/**
	 * Adds an option string to the data set
	 *
	 * @param string $opt Option string ("option1:value1,option2:value2")
	 * @param string $delim Delimiter for the option statement
	 * @param string $sign Textual representations of the assignment
	 * @return void
	 */
	public function addOptions($opt, $delim=',', $sign=':') {
		$options = explode($delim, $opt);
		foreach ($options as $option) {
			$item = explode($sign, $option);
			$this->data[$item[0]] = $item[1];
		}
	}

	/**
	 * Sets a value for a specified attribute or sets a complete array
	 *
	 * @param string|array $mxt Name of the attribute or complete array
	 * @param string $value Value to be set
	 * @return void
	 */
	public function set($mxt, $value=true) {
		if (is_array($mxt))
			$this->data = $mxt;
		elseif (is_string($mxt))
			$this->data[$mxt] = $value;
		else
			throw new Exeption('Unexpected variable type. Expecting array or string.');
	}

	/**
	 * Returns a specified item from the object
	 *
	 * @param string $attr Attribute name
	 * @param string $type Variable type
	 * @param mixed $default Default value
	 * @return mixed
	 */
	public function get($attr, $type=false, $default=null) {
		if (preg_match_all('/\s*([^.(\s]+)\s*(?:\((.*?)\))?/', $attr, $match, PREG_SET_ORDER)) {
			$path = array();
			foreach ($match as $crump) {
				$path[] = array(
					'key'  => $crump[1],
					'path' => isset($crump[2]) ? $crump[2] : null
				);
			}
			return $this->getFromPath($this->data, $path, $type, $default);
		}

		return $default;
	}

	/**
	 * Returns a specified item from the object
	 *
	 * @param array $data The data array
	 * @param string $attr Attribute name
	 * @param string $type Variable type
	 * @param mixed $default Default value
	 * @return mixed
	 */
	private function getFromPath($data, $path, $type, $default) {
		if (sizeof($path) == 1)
			return $this->getValue($data, $path[0]['key'], $type, $default);

		$crump = array_shift($path);
		// @todo: Implement filter queries

		return isset($data[$crump['key']]) ? $this->getFromPath($data[$crump['key']], $path, $type, $default) : $default;
	}

	/**
	 * Returns a specified item from the object
	 *
	 * @param array $data The data array
	 * @param string $attr Attribute name
	 * @param string $type Variable type
	 * @param mixed $default Default value
	 * @return mixed
	 */
	private function getValue($data, $attr, $type, $default) {
		if ($type)
			return $this->initValue(isset($data[$attr]) ? $data[$attr] : '', $type, $default);
		else
			return isset($data[$attr]) ? $data[$attr] : $default;
	}

	/**
	 * Returns a specified item from the object and applies a filter to the returned value
	 *
	 * @param string $attr Attribute name
	 * @param string $type Variable type
	 * @param mixed $default Default value
	 * @param array $accept List of all values the variable may have
	 * @param array $refuse List of all values the variable may not have
	 * @return mixed
	 */
	public function getFiltered($attr, $type='string', $default='', $accept=false, $refuse=false) {
		$value = $this->get($attr, $type='string', $default='');
		if (!in_array($value, $accept))
			return $default;
		if (in_array($value, $refuse))
			return $default;
		return $value;
	}

	/**
	 * Returns the complete data array of the object
	 *
	 * @return array
	 */
	public function getClear() {
		return $this->data;
	}

	/**
	 * Checks, whether the object has a specified attribute
	 *
	 * @param string $attr Attribute name
	 * @return bool
	 */
	public function has($attr) {
		return array_key_exists($attr, $this->data);
	}

	/**
	 * Counts the object's items
	 *
	 * @return int
	 */
	public function count() {
		return sizeof($this->data);
	}

	/**
	 * Erases a specified attribute
	 *
	 * @param string $attr Attribute name (If empty, the complete array will be cleared)
	 * @return void
	 */
	public function erase($attr='') {
		if ($att='')
			$this->data = array();
		if ($this->has($attr))
			unset($this->data[$attr]);
	}

	/**
	 * Adds data from a CSV string
	 *
	 * @param string $csv The CSV string
	 * @param string $delim Delimiter
	 * @return void
	 */
	public function addCSV($csv, $delim='|') {
		$this->add(explode($delim, $csv));
	}

	/**
	 * Parses a CSV string into an array
	 *
	 * <code>
	 * $csv = Xily\Dictionary::parseCsv(file_get_contents('data.csv'), ',', '"', true);
	 * </code>
	 *
	 * @param $csv
	 * @param $delim
	 * @param $wrapper
	 * @param $usetitles
	 * @return array
	 */
	public function parseCsv($csv, $delim = ';', $wrapper = false, $usetitles = false) {
		$lines = explode("\n", $csv);
		$data = array();
		if ($usetitles) {
			$line = trim(array_shift($lines));
			$titles = explode($wrapper ? $wrapper.$delim.$wrapper : $delim, $wrapper ? trim($line, $wrapper) : $line);
		}
		$z = 0;
		// foreach ($lines as $line) {
		while(sizeof($lines) > 1) {
			$line = array_shift($lines);
			// If there's a wrapper, e.g. '"', explode ";" - if not, only use the delimiter
			$row = explode($wrapper ? $wrapper.$delim.$wrapper : $delim, $line);
			// Check, if a delimiter ends with a backslash and remove whitespaces
			for ($i = 0 ; $i < sizeof($row) ; $i++) {
				$key = $usetitles ? $titles[$i] : $i;
				if (substr($row[$i], -1) == '\\') {
					$data[$z][$key] = substr($row[$i], 0, -1).$delim;
					if (isset($row[$i+1])) {
						$data[$z][$key] .= $row[$i+1];
						array_splice($row, $i+1, 1);
					}
				}
				$data[$z][$key] = trim($row[$i]);
				if ($wrapper)
					$data[$z][$key] = trim($data[$z][$key], $wrapper);
			}
			$z++;
		}
		return $data;
	}

	/**
	 * Implodes the array
	 *
	 * @param string $glue
	 * @return string
	 */
	public function implode($glue='') {
		return implode($glue, $this->data);
	}

	/**
	 * Insert the data into a template string
	 *
	 * @param string $strTemplate
	 * @param string $strPatternStart
	 * @param string $strPatternEnd
	 */
	public function insertInto($strTemplate, $strPatternStart='{{', $strPatternEnd='}}') {
		foreach ($this->data as $key => $value) {
			if (!is_string($value) && !is_numeric($value) && !is_bool($value))
				$value = '';
			$strTemplate = str_replace($strPatternStart.$key.$strPatternEnd, $value, $strTemplate);
		}
		return $strTemplate;
	}

	/**
	 * Converts the array into an Xily\Xml object
	 *
	 * @param Xily\Xml $xmlNode
	 * @param array $mxtData
	 * @param bool $bolAssoc
	 * @return Xily\Xml
	 */
	private function fromXml($xmlNode, $mxtData, $bolAssoc) {
		if (is_array($mxtData)) {
			if ($bolAssoc && self::checkAssoc($mxtData)) {
				foreach ($mxtData as $key => $value) {
					$xmlChild = new Xml($key);
					$xmlNode->addChild(self::fromXml($xmlChild, $value, $bolAssoc));
				}
			} else {
				foreach ($mxtData as $key => $value) {
					$xmlChild = new Xml('node', null, array('key' => $key));
					$xmlNode->addChild(self::fromXml($xmlChild, $value, $bolAssoc));
				}
			}
		} else
			$xmlNode->setValue($mxtData);

		return $xmlNode;
	}

	/**
	 * Check, if the array is associative
	 *
	 * @param array $a
	 */
	private function checkAssoc($a) {
		foreach(array_keys($a) as $key)
			if (is_int($key))
				return false;
		return true;
	}

	/**
	 * Converts the array data an Xily\Xml object
	 *
	 * @param string $strName Name of the root element
	 * @param bool $bolAssoc Use the keys of an associative array to create the node titles. Otherwise, use "node"
	 * @return Xily\Xml
	 */
	public function toXml($strName='array', $bolAssoc=true) {
		return $this->fromXml(new Xml($strName), $this->data, $bolAssoc);
	}
}

?>
