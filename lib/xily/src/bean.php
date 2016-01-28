<?php
namespace Xily;

/**
 * The Bean class extends the Xily\Xml class and allows advanced parsing features for XML data.
 * Based on the Bean class you can create your own
 * micro parsers (Beans) which are stored in the /beans directory of your
 * xily installation.
 * One more speciality about Beans:
 * Although you may define your own Beans in PHP and create your
 * own logic and functions arround them, Beans hold very powerful
 * data referencing methods, calls XDR - Xily Data References.
 * An XDR helps you to use data stored in object OUTSIDE the tag you
 * are using.
 *
 * @author Peter-Christoph Haider (Project Leader) et al. <info@xily.info>
 * @version 2.0 (2013-03-28)
 * @package Xily
 * @copyright Copyright (c) 2009-2013, Peter-Christoph Haider
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @license http://www.xily.info/ Commercial License
 */
class Bean extends Xml {
	/** @var string|bool|array|object The container of the object's results. A result can be of varible types. */
	private $mxtResult;
	/** @var bool This switch commands the run() function to respect or ignore the source attribute of the tag. */
	private $bolLoadFromURL = true;
	/** @var bool This switch commands the result() function to return the XML-string of the node or to ignore it in the result collection. */
	public $bolReturnXML = true;
	/** @var array Container for all utility data sets */
	private $arrDataset = array();
	/** @var array Container for all object links */
	private $arrLink = array();
	/** @var array Container for all heritage objects to be refused */
	private $arrNoHeritage = array('attribute' => array(), 'value' => array(), 'link' => array(), 'dataset' => array(), 'root' => array(), 'parent' => array());
	/** @var array List of additional bean directories  */
	public static $BEAN_DIRS = array();
	/** @var string The base path for external includes */
	public static $basepath = '';

	/**
	 * Loads the XML data from a string or file
	 *
	 * @param string $strXML Name of the XML file or XML string
	 * @param bool $bolLoadFile Load from file or parse locally
	 * @return Bean
	 */
	public static function create($strXML='', $bolLoadFile=false) {
		return $strXML == '' ? new Bean() : self::returnNode($strXML, new Bean(), $bolLoadFile);
	}

	/**
	 * Scan the bean directories and include the Bean
	 * @param array $addDirs
	 * @param string $strParserFile
	 * @param string $strParserClass
	 */
	private function includeNode($addDirs, $strParserFile, $strParserClass) {
		foreach ($addDirs as $strDir) {
			if (file_exists($this->fileFormatDir($strDir).$strParserFile)) {
				include_once($this->fileFormatDir($strDir).$strParserFile);
				if (class_exists('\Xily\\'.$strParserClass)) {
					return '\Xily\\'.$strParserClass;
				}
			}
		}
		return false;
	}

	/**
	 * Creates a Bean node
	 * The function will check, if there is a specific Bean class in the beans/ directory and include it, if availiable
	 * @param string $strTag
	 * @param array $arrAttributes
	 * @param Bean $xmlParent
	 * @return Bean
	 */
	public function createNode($strTag='', $arrAttributes=array(), $xmlParent=null) {
		$arrParserKey = explode(":", $strTag);

		$addDirs = self::$BEAN_DIRS ? self::$BEAN_DIRS : array(dirname(__FILE__).DIRECTORY_SEPARATOR.'beans');

		if (isset($arrParserKey[1]))
			if ($strParserClass = $this->includeNode($addDirs, $arrParserKey[0].DIRECTORY_SEPARATOR.$arrParserKey[1].'.php', ucfirst($arrParserKey[0]).ucfirst($arrParserKey[1])))
				return new $strParserClass($strTag, null, $arrAttributes, $xmlParent);

		if ($strParserClass = $this->includeNode($addDirs, $arrParserKey[0].'.php', 'Bean'.ucfirst($arrParserKey[0])))
			return new $strParserClass($strTag, null, $arrAttributes, $xmlParent);

		return new Bean($strTag, null, $arrAttributes, $xmlParent);
	}

	// =============  Execution functions  =============

	/**
	 * Nothing to do here besides parsing the sub structure of the node deviations of Xily\Xml
	 * e.g. Beans, may have different build functions.
	 *
	 * @return void
	 */
	public function build() {
		$this->initDatasets();
		$this->heritage();
	}

	/**
	 * Returns a dump of the nodes content
	 *
	 * @param mixed $mxtData Temporary dataset [n:data]
	 * @return string The content of the node including CDATA and subordinate bean results
	 * @category iXML/Wrap
	 */
	public function dump($mxtData=null) {
		$arrContent = $this->content();
		$strResult = '';
		if ($this->hasContent()) {
			foreach ($arrContent as $content) {
				if (is_string($content))
					$strResult .= $this->xdrInsert($content, $mxtData, 1);
				if ($content instanceof Bean)
					$strResult .= $content->run($mxtData);
			}
		}
		return $strResult;
	}

	/**
	 * Executes all functions included in the Bean
	 * First, all datasets will be loaded and Xily Data References (XDR) included
	 * Then the bean's result() function will be called
	 *
	 * @param mixed $mxtData Temporary dataset [n:data]
	 * @param int $intLevel Level within the execution hierarchie
	 * @return string|mixed Result of the run (usually a string, e.g. HTML code)
	 * @category iXML/Wrap
	 */
	public function run($mxtData=null, $intLevel=0) {
		// If the object has a _source meta attribute, load the children from a local file.
		if ($this->hasAttribute('_source')) {
			$xlySource = $this->xdr($this->attribute('_source'), $mxtData, 0, 0);
			if ($xlySource instanceof Bean) {
				$this->arrAttributes = array_merge($this->arrAttributes, $xlySource->attributes());
				$this->arrChildren = $xlySource->children();
				$this->arrCDATA = $xlySource->cdata();
				$this->arrContent = $xlySource->arrContent;
				// Inherit the root and parent object...
				// $this->inherit('root', 0, 0, 1);
				// $this->inherit('parent');
				$this->heritage();
			}
			if (is_string($xlySource))
				$this->setValue($xlySource);
			unset($this->arrAttributes['_source']);
		}
		// Return the results of the tag.
		return $this->result($mxtData, $intLevel);
	}

	/**
	 * Executes the run() function on all child nodes
	 *
	 * @param mixed $mxtData Temporary dataset [n:data]
	 * @param int $intLevel Level within the execution hierarchie
	 * @param bool $bolArray If active, all results will be written to an array
	 * @return string|array|bool Result of the run
	 * @category iXML/Wrap
	 */
	public function runChildren($mxtData=null, $intLevel=0, $bolArray=0) {
		$arrResult = array();

		// Get the children's result - and check if the result is an array or a string
		foreach ($this->children() as $xmlChild)
			$arrResult[] = $xmlChild->run($mxtData, $intLevel);

		return $arrResult;
	}

	/**
	 * Works like the tag() method, but also applies the xdrInsert() function first.
	 * If $bolReturnXML = true, it will return the XML string of the object and will then
	 * execute the runChildren() function to get it's children's results.
	 *
	 * @param mixed $mxtData Temporary dataset
	 * @param int $intLevel Level within the execution hierarchie
	 * @return string|mixed Result of the run (usually a string, e.g. HTML code)
	 */
	public function result($mxtData, $intLevel=0) {
		if ($this->bolReturnXML) {
			$strTab = str_repeat("\t", $intLevel);
			$strResult = $strTab."<".$this->strTag;
			// Build the tag
			if ($this->hasAttributes()) {
				foreach ($this->arrAttributes as $key => $value)
					$strResult .= ' '.$key.'="'.$this->xdrInsert($value, $mxtData).'"';
			}

			if ($this->hasValue() || $this->hasCdata()) {
				$strResult .= '>';
				$arrContent = $this->content();
				foreach ($arrContent as $content) {
					if (is_string($content))
						$strResult .= $this->xdrInsert($content, $mxtData);
					elseif ($content instanceof Bean)
						$strResult .= $content->run($mxtData);
					else
						$strResult .= $content;
				}
				$strResult .= '</'.$this->strTag.'>';
			} elseif ($this->hasChildren()) {
				$strResult .= ">\n".implode("\n", $this->runChildren($mxtData, $intLevel+1))."\n".$strTab.'</'.$this->strTag.'>';
			} else {
				$strResult .= in_array($this->tag(), self::$OPEN_TAGS) ? '></'.$this->tag().'>' : '/>';
			}
			return $strResult;
		} else {
			return implode("\n", $this->runChildren($mxtData, $intLevel));
		}
	}

	// =============  XDR functions  =============

	/**
	 * This function evaluates and inserts multiple XDRs into a string.
	 *
	 * @param string $strXDR The string containing the XDRs or an XDR itself
	 * @param string|array|Xily\Xml $mxtData Temporary dataset
	 * @param bool $bolStrict If strict mode is of, the function will try to convert unfitting object to strings (e.g. XML files)
	 * @param bool $bolReturnEmpty If true (default) the function will replace and invalid XDR with an empty string
	 * @return string|array|Xily\Xml
	 */
	public function xdrInsert($strXDR, $mxtData="", $bolStrict=false, $bolReturnEmpty=true) {
		preg_match_all('/%\{(.*)\}/U', $strXDR, $arrXDRs);
		if (sizeof($arrXDRs[1]) > 0)
			foreach ($arrXDRs[1] as $strSubXDR) {
				$strResult = $this->xdr($strSubXDR, $mxtData, 1, $bolStrict);
				if (is_string($strResult) || is_numeric($strResult))
					$strXDR = preg_replace('/%\{'.preg_quote($strSubXDR, '/').'}/', $strResult, $strXDR);
				elseif ($bolReturnEmpty)
					$strXDR = preg_replace('/%\{'.preg_quote($strSubXDR, '/').'}/', '', $strXDR);
			}

			return $strXDR;
	}

	/**
	 * Evalutates a Xily Data Reference (XDR)
	 * The function covers the following XDR shapes in this order:
	 *
	 * {_request(var):type} (Request variables: _post, _get, _request)
	 * {_request(var):type:default}
	 * {object::dataset}
	 * {external:type:datapath}
	 * {external:type}
	 * {object:dataset:datapath}
	 * {object:datapath}
	 * {.objectpath}
	 * {datapath}
	 *
	 * @param string $strXDR The XDR string
	 * @param string|array|Xily\Xml $mxtData Temporary dataset
	 * @param bool $bolStringOnly If true, the function will only return results of type string
	 * @param bool $bolStrict If strict mode is off, the function will try to convert unfitting object to strings (e.g. XML files)
	 * @return string|array|Xily\Xml|mixed
	 */
	public function xdr($strXDR, $mxtData="", $bolStringOnly=true, $bolStrict=false) {
		// First, remove all brackets from the Object
		$strXDR = $this->strStripString($strXDR, '%{', '}', 1);
		// Then recursively insert
		// TESTING: $this->probe("xdr", "%%% New XDR: ".$strXDR." %%%", 0, 1);
		// TESTING: $this->probe("xdr", "Only Strings: ".($bolStringOnly?'on':'off'));
		// TESTING: $this->probe("xdr", "XDR: ".$strXDR, 1);

		// Inserting subordinate XDRs
		$strXDR = $this->xdrInsert($strXDR, $mxtData, 1);
		// TESTING: $this->probe("xdr", ">> XDR completely inserted: ".$strXDR, 0, 1);

		// Identify the XDR class for each XDR found in the string
		// Split the string by the separator "::"
		// If this exists, the XDR refers to a whole dataset: {object::dataset}
		if (preg_match_all('/((?:[^:]+\(.*\))|(?:[^:]+))::((?:[^:]+\(.*\))|(?:[^:]+))/', $strXDR, $arrXDR, PREG_SET_ORDER)) {
			// -----> CASE: {object::dataset}
			// TESTING: $this->probe("xdr", "CASE: {object::dataset}", 2);
			$arrXDR = array_pop($arrXDR);
			if ($mxtObject = $this->xdr_object($arrXDR[1])) {
				$mxtDataset = $mxtObject->dataset($arrXDR[2]);
				if (is_null($mxtDataset))
					throw new Exception('The required dataset "'.$arrXDR[2].'" cannot be supplied by the object.');
				else {
					if ($bolStringOnly && !$bolStrict) {
						if (is_array($mxtDataset)) {
							return var_export($mxtDataset, 1);
						} elseif ($mxtDataset instanceof Xml) {
							return $mxtDataset->toString();
						} elseif (is_string($mxtDataset))
							return $mxtDataset;
					} else
						return $mxtDataset;
				}
			}
		} elseif (preg_match_all('/(?:[^:]+\(.*\)[^:]+)|(?:[^:]+)/', $strXDR, $arrXDRraw, PREG_SET_ORDER)) {
			$arrXDR = array();
			foreach ($arrXDRraw as $arrSubXDR)
				$arrXDR[] = array_pop($arrSubXDR);

			// CASE: {externals:type}, {externals:type:path}, {_request(var):type}, {_request(var):type:default}
			if (preg_match_all('/^(_post|_get|_request|.open|.post|.get)\(((?:\"(.*)\")|(?:\'(.*)\')|(.*))\)/', $arrXDR[0], $arrBase, PREG_SET_ORDER)) {
				$arrBase = array_pop($arrBase);
				array_shift($arrBase);
				$strMethod = array_shift($arrBase);
				$strVariable = array_pop($arrBase);
				$strType = isset($arrXDR[1]) ? $arrXDR[1] : null;
				$strDefault = isset($arrXDR[2]) ? $arrXDR[2] : null;
				// TESTING: $this->probe('xdr', 'External data identified. (Method: '.$strMethod.'; Variable: '.$strVariable.')', 3);

				$mxtValue = null;
				switch($strMethod) {
					case '_post':
						return $this->initValue($_POST[$strVariable], $strType, $strDefault);
					case '_get':
						return $this->initValue($_GET[$strVariable], $strType, $strDefault);
					case '_request':
						return $this->initValue($_REQUEST[$strVariable], $strType, $strDefault);
					default:
						// for .open, .post, .get
						$mxtExternalData = $this->xdr_external($strVariable, $strType, substr($strMethod, 1));
						return isset($arrXDR[2]) ? $this->xdr_data($strDefault, $mxtExternalData, $bolStringOnly) : $mxtExternalData;
				}
			}

			switch (sizeof($arrXDR)) {
				case 1:
					// -----> CASE {datapath} or {.objectpath}
					// If the datapath starts with a '.', e.g. {.myobject.child.item(@index == 0).@name}
					// the XDR referrs to object data, otherwise to local/temporary data
					if (substr($strXDR, 0, 1) == '.') {
						// -----> CASE: {.objectpath}
						// TESTING: $this->probe("xdr", "CASE: {.objectpath}", 3);
						return $this->xdr_object($arrXDR[0], $bolStringOnly, $bolStrict);
					} else {
						// -----> CASE: {datapath}
						// TESTING: $this->probe("xdr", "CASE: {datapath}", 3);
						// The datapath does refer to the temporary dataset or the default dataset of the object
						if ($mxtData) {
							// TESTING: $this->probe("xdr", "Inserting data from temporary dataset", 3);
							return $this->xdr_data($strXDR, $mxtData, $bolStringOnly);
						} else {
							// TESTING: $this->probe("xdr", "Inserting data from local default dataset", 3);
							return $this->xdr_data($strXDR, $this->dataset(), $bolStringOnly);
						}
					}
				case 2:
					// -----> CASE {object:datapath}
					// TESTING: $this->probe("xdr", "CASE: {object:datapath}", 3);
					if ($mxtObject = $this->xdr_object($arrXDR[0])) {
						// TESTING: $this->probe("xdr", "Object ready", 4);
						// TESTING: $this->probe("xdr", "STEP2: Retrieving dataset: default", 5);
						if ($mxtDataset = $mxtObject->dataset()) {
							// TESTING: $this->probe("xdr", "Dataset loaded: default", 5);
							return $this->xdr_data($arrXDR[1], $mxtDataset, $bolStringOnly);
						} else {
							// TESTING: $this->probe('xdr', 'The object has no dataset. There\'s noting I could do');
							return false;
						}
					}
				case 3:
					// -----> CASE {object:dataset:datapath}
					// TESTING: $this->probe("xdr", "CASE: {object:dataset:datapath}", 3);
					// TESTING: $this->probe("xdr", "Object: ".$arrXDR[0], 4);
					if ($mxtObject = $this->xdr_object($arrXDR[0])) {
						// TESTING: $this->probe("xdr", "Object ready", 4);
						// TESTING: $this->probe("xdr", "STEP2: Retrieving dataset: ".$arrXDR[1], 5);
						// // TESTING: $this->probe("xdr", $mxtObject->showDatasets());
						if ($mxtDataset = $mxtObject->dataset($arrXDR[1])) {
							// TESTING: $this->probe("xdr", "Dataset loaded: ".$arrXDR[1], 6);
							return $this->xdr_data($arrXDR[2], $mxtDataset, $bolStringOnly);
						} else {
							// TESTING: $this->probe("xdr", "Could not load dataset: ".$arrXDR[1], 6);
							return false;
						}
					} else {
						// TESTING: $this->probe('xdr', 'The required object "'.$arrXDR[0].'" could not be evaluated.', 6);
						return false;
					}
			}
		}

		throw new Exception('Invalid XDR statement: '.$strXDR);
	}

	/**
	 * Retrieves a local object
	 * This function evaluates an XML path, in order to receive an XML object or to trace the complete path ($bolStringOnly).
	 *
	 * @param string $strObject The object path or ID of the node
	 * @param bool $bolStringOnly If true, the function will only return results of type string
	 * @param bool $bolStrict If strict mode is of, the function will try to convert unfitting object to strings (e.g. XML files)
	 * @return string|array|Xily\Xml|mixed
	 */
	private function xdr_object($strObject, $bolStringOnly=false, $bolStrict=false) {
		// TESTING: $this->probe('xdr_object', "STEP1: Retrieving Object: ".$strObject, 4);
		// TESTING: $this->probe('xdr_object', "Only Strings: ".($bolStringOnly?'on':'off'), 4);

		// Get the root object of the current tag. We might need it to reference other objects
		if (!$xmlRoot = $this->root())
			throw new Exception('Could not process XDR: No root object set for node.');

		// Analyze the object path and check for special cases (.root, .parent, .this)
		$arrObject = explode('.', $strObject);
		if (sizeof($arrObject) > 1) {
			// Check, if the objectpath starts with a '.'
			if ($arrObject[0] == '') {
				// TESTING: $this->probe('xdr_object', "Special object root or object path detected", 4);
				array_shift($arrObject);
				if (strtolower($arrObject[0]) == 'root') {
					// TESTING: $this->probe('xdr_object', "Load Object: .root", 5);
					$mxtObject = $xmlRoot;
				} elseif (strtolower($arrObject[0]) == 'parent') {
					// TESTING: $this->probe('xdr_object', "Load Object: .parent", 5);

					if (!$mxtObject = $this->parent())
						throw new Exception('Could not process XDR: The node has no parent. (poor thing...)');

					// TESTING: $this->probe('xdr_object', "Object .parent successfully loaded", 5);
					$arrObject = array_slice($arrObject, 1);
				} elseif (strtolower($arrObject[0]) == 'this') {
					// TESTING: $this->probe('xdr_object', "Load Object: .this", 5);
					$mxtObject = $this;
				}
			}
		}
		// Now make sure that there is a reference object available by now
		if (!isset($mxtObject)) {
			// TESTING: $this->probe('xdr_object', "Loading Object: ".$arrObject[0], 6);
			if (!$mxtObject = $xmlRoot->getNodeById($arrObject[0])) {
				// TESTING: $this->probe('xdr_object', "Error loading the object \"$arrObject[0]\"", 6);
				return false;
			}
		}
		// TESTING: $this->probe('xdr_object', "Object successfully loaded; Object type '".$mxtObject->tag()."'", 6);
		// Shorten the objectpath by the first element
		array_shift($arrObject);
		// Evaluating the object's path
		$strObjectpath = implode('.', $arrObject);

		if ($bolStringOnly) {
			if ($strObjectpath == '') {
				// TESTING: $this->probe('xdr_object', "No Objectpath detected. Returning the object's value.", 7);
				return $mxtObject->dump();
			} else {
				// TESTING: $this->probe('xdr_object', "Tracing the objectpath now: ".$strObjectpath, 7);
				return $mxtObject->trace($strObjectpath);
			}
		} else {
			if ($strObjectpath == '')
				return $mxtObject;
			else {
				// TESTING: $this->probe('xdr_object', "Evaluate the rest of the object path: ".$strObjectpath, 7);
				$xlsObject = $mxtObject->getNodesByPath($strObjectpath);
				switch (sizeof($xlsObject)) {
					case 0:
						// TESTING: $this->probe('xdr_object', 'Could not process XDR: The node "'.$arrObject[0].'" could not process the path.');
						return false;
					case 1:
						// TESTING: $this->probe('xdr_object', "Object retrieved - returning a single object", 7);
						return $xlsObject[0];
					default:
						// TESTING: $this->probe('xdr_object', "Object list retrieved - returning the complete list", 7);
						return $xlsObject;
				}
			}
		}
	}

	/**
	 * This function evaluates a datapath relativ to the given dataset.
	 *
	 * @param string $strDataPath The XML data path
	 * @param Xily\Xml $mxtDataset The dataset on which the datapath should be applied
	 * @param bool $bolStringOnly If true, the function will only return results of type string
	 * @param bool $bolStrict If strict mode is of, the function will try to convert unfitting object to strings (e.g. XML files)
	 * @return string|array|Xily\Xml|mixed
	 */
	private function xdr_data($strDataPath, $mxtDataset, $bolStringOnly=true, $bolStrict=false) {
		// TESTING: $this->probe('xdr_data', "STEP3: Evaluating datapath", 7);
		// TESTING: $this->probe('xdr_data', "Only Strings: ".($bolStringOnly?'on':'off'), 7);
		// TESTING: $this->probe('xdr_data', "Path: ".$strDataPath, 7);
		if (is_array($mxtDataset)) {
			// TESTING: $this->probe('xdr_data', "Dataset is an array", 8);
			$xlyArray = new Dictionary($mxtDataset);

			return $xlyArray->get($strDataPath, false);
		} elseif ($mxtDataset instanceof Xml || $mxtDataset instanceof Bean) {
			// TESTING: $this->probe('xdr_data', "Dataset is an XML/Bean document", 8);
			if ($bolStringOnly) {
				// TESTING: $this->probe('xdr_data', "Tracing the datapath now: ".$strDataPath, 9);
				return $mxtDataset->trace($strDataPath);
			} else {
				// TESTING: $this->probe('xdr_data', "Retrieving the object now: ".$strDataPath, 9);
				$mxtData = $mxtDataset->getNodesByPath($strDataPath);
				if (isset($mxtData[1]))
					return $mxtData;
				elseif (isset($mxtData[0]))
					return $mxtData[0];
				else
					return false;
			}
		} else {
			// TESTING: $this->probe('xdr_data', "Invalid dataset: Dataset must be an array or XML/Bean object.", 8);
			return false;
		}
	}

	/**
	 * Fetches an external dataset.
	 *
	 * @param string $strURL The URL or directory of the file
	 * @param string strType Declares the type of data (plain*, xml, bean, json)
	 * @param string $strMethod Loading method for the external resource: open*, get, post
	 * @return object|string
	 */
	private function xdr_external($strURL, $strType='plain', $strMethod='open') {
		// TESTING: $this->probe('xdr_external', 'Trying to load the external resource now: '.$strURL.'; Method: '.$strMethod.'; Type: '.$strType, 4);

		$strData = null;
		$strMethod = strtolower($strMethod);
		switch ($strMethod) {
			case 'get':
			case 'post':
				if (class_exists('\REST\Client')) {
					$req = new \REST\Client($strURL);
					$strData = $strMethod == 'get' ? $req->get() : $req->post();
				}
				break;
			default:
				$strData = is_readable(self::$basepath.$strURL) ? file_get_contents(self::$basepath.$strURL) : false;
				break;
		}

		if (!$strData)
			return;

		if ($strType == 'xml') {
			return Xml::create($strData);
		} elseif ($strType == 'bean') {
			return Bean::create($strData);
		} elseif ($strType == 'json') {
			return json_decode($strData, 1);
		} else
			return $strData;
	}

	// =============  Data handling functions  =============

	/**
	 * Initialize the bean's datasets (used in build() functions)
	 * A dataset is identified by a '_' as an attribute's first character
	 * e.g. <tag _dataset="{XDR}" attribute="STIRNG" />
	 *
	 * @return void
	 */
	public function initDatasets() {
		foreach ($this->arrAttributes as $key => $value) {
			if (substr($key, 0, 1) == '_' && $key != '_source') {
				// TESTING: $this->probe("initDatasets", "Inserting data in dataset '".substr($key, 1)."'; XDR: '".$value."'");
				$this->arrDataset[substr($key, 1)] = $this->xdr($value, '', 0, 1);
				unset($this->arrAttributes[$key]);
			}
		}
	}

	/**
	 * Sets the value for the $mxtDataset variable.
	 * Caution: existing data will be overwritten!
	 *
	 * @param string|array|Xily\Xml $mxtDataset [data]
	 * @param string $strDataset Name of the dataset. [name]
	 * @return void
	 * @category iXML
	 */
	public function setDataset($mxtDataset, $strDataset="default") {
		$this->arrDataset[$strDataset] = $mxtDataset;
	}

	/**
	 * Lists all available datasets of a function
	 *
	 * @param bool $bolShowAll If TRUE the function will also search all child nodes [0:recursive]
	 * @return string
	 * @category iXML
	 */
	public function showDatasets($bolShowAll=false) {
		$strResult = "";
		if (sizeof($this->arrDataset) > 0) {
			$strObject  = "";
			$strObject .= $this->strTag." [".$this->intIndex."]";
			if ($this->strID)
				$strObject .= " - (".$this->strID.")";
			foreach ($this->arrDataset as $key => $value) {
				$strResult .= $strObject."->".$key." (".gettype($value).")\n";
			}
		}
		if ($bolShowAll && $this->hasChildren())
			foreach ($this->children() as $xmlChild)
				$strResult .= $xmlChild->showDatasets(1);
		return $strResult;
	}

	/**
	 * Returns a specified dataset
	 *
	 * @param string $strDataset Name of the dataset. [name]
	 * @return string|array|Xily\Xml
	 * @category iXML
	 */
	public function dataset($strDataset="default") {
		if (array_key_exists($strDataset, $this->arrDataset))
			return $this->arrDataset[$strDataset];
		else
			return null;
	}

	/**
	 * Clears a defined dataset.
	 * If no dataset name is supplied, all datasets will be removed by clearing the entire dataset variable.
	 *
	 * @param string $strDataset Name of the dataset [n:name]
	 * @return void
	 * @category iXML
	 */
	public function clearDataset($strDataset=null) {
		if (is_null($strDataset))
			unset($this->arrDataset[$strDataset]);
		else
			$this->arrDataset = array();
	}

	/**
	 * Removes a single dataset from the $mxtDataset variable.
	 *
	 * @param string $strDataset Name of the dataset. [name]
	 * @return void
	 * @category iXML
	 */
	public function removeDataset($strDataset="default") {
		if (isset($this->arrDataset[$strDataset]))
			unset($this->arrDataset[$strDataset]);
	}

	/**
	 * Checks, if the node has a specified dataset
	 *
	 * @param string $strDataset Name of the dataset. [name]
	 * @return bool
	 * @category iXML/Wrap
	 */
	public function hasDataset($strDataset) {
		return isset($this->arrDataset[$strDataset]);
	}

	// =============  Linking and collection functions  =============

	/**
	 * Sets a link to another Bean
	 *
	 * @param string $strLink
	 * @param Bean $xlyObject
	 * @return void
	 */
	public function setLink($strLink, $xlyObject) {
		$this->arrLink[$strLink] = $xlyObject;
	}

	/**
	 * Clears all links from the node
	 *
	 * @return void
	 */
	public function clearLinks() {
		$this->arrLink = array();
	}

	/**
	 * Removes a specified link
	 *
	 * @param string $strLink
	 * @return void
	 */
	public function removeLink($strLink) {
		unset($this->arrLink[$strLink]);
	}

	/**
	 * Returns a pointer to the linked object
	 *
	 * @param string $strLink Name of the link
	 * @return Bean
	 */
	public function link($strLink) {
		return $this->hasLink($strLink) ? $this->arrLink[$strLink] : null;
	}

	/**
	 * Check, if a specified link exists
	 *
	 * @param string $strLink Name of the link
	 * @return bool
	 */
	public function hasLink($strLink) {
		return isset($this->arrLink[$strLink]) && $this->arrLink[$strLink] instanceof Dictionary;
	}

	/**
	 * The collect() function passes a value to the local of foreign collector
	 *
	 * @param string $strCollector The name of the collector
	 * @param mixed $mxtContent
	 * @return void
	 */
	public function collect($strCollector, $mxtContent) {
		if ($this->hasLink($strCollector))
			$this->link($strCollector)->push($mxtContent);
		elseif ($this->root()->hasLink($strCollector))
			$this->root()->link($strCollector)->push($mxtContent);
		elseif ($this->hasParent() && $this->parent()->hasLink($strCollector))
			$this->parent()->link($strCollector)->push($mxtContent);
		else
			throw new Exception('No collector (Dictionary) found for '.$strCollector, 0);
	}

	// =============  Inheriting functions  =============

	/**
	 * The heritage function collects all inherit() and noInherit() statements for each bean.
	 * Therefore, each bean has its own heritage() function.
	 * The heritag() function is called in the run() function.
	 *
	 * @return void@return void
	 */
	public function heritage() {
		// $this->preHeritage();

		// Put your inherit() statement here
		// e.g. $this->inherit('link', $this, 'godfather');
		// or   $this->inherit('attribute', 'myattribute');
		// or   $this->inherit('value');
	}

	/**
	 * The preHeritage() function should be called at the beginning of a class's
	 * heritage() function, as it calls the children's refuseHeritage() functions,
	 * which is required to invoce the noHeritage() statements before the
	 * inhertited data is passed
	 *
	 * @return void
	 */
	public function preHeritage() {
		foreach ($this->children() as $xmlChild)
			$xmlChild->refuseHeritage();
	}

	/**
	 * The refuseHeritage() function collects all noInherit() statements for each bean.
	 * Therefore, each bean has its own noheritage() function.
	 * The noheritage() function is called by the parents preheritage function.
	 *
	 * @return void
	 */
	public function refuseHeritage() {
		// Put your inherit() statement here
		// e.g. $this->inherit('link', $this, 'godfather');
		// or   $this->inherit('attribute', 'myattribute');
		// or   $this->inherit('value');
	}

	/**
	 * Inherits a certain set of properties to all child nodes.
	 *
	 * @param mixed $mxtValue
	 * @param string $strAs
	 * @param string $strName
	 * @return void
	 */
	public function inherit($strAs, $strName="", $mxtValue="", $bolPersistent=false) {
		$arrModes = array('attribute', 'value', 'link', 'dataset');
		if (in_array($strAs, $arrModes)) {
			if (!$mxtValue) {
				if ($strAs == 'attribute') $mxtValue = $this->attribute($strName);
				if ($strAs == 'link') $mxtValue = $this->link($strName);
				if ($strAs == 'dataset') $mxtValue = $this->dataset($strName);
				if ($strAs == 'value' && !$mxtValue) $mxtValue = $this->value();
			}
			foreach ($this->children() as $xmlChild)
				$xmlChild->passHeritage($strAs, $mxtValue, $strName, $bolPersistent);
		} else
			throw new Exception('Invalid operation mode: '.$strAs.'. Use "attribute", "value", "link" or "dataset" instead.');
	}

	/**
	 * This function adds a restriction for the passHeritage() function in order to refuse a certain heritage.
	 * This is especially useful when working with persistent heritage functions,
	 * as some child nodes will not be supposed to inherit some parameters.
	 *
	 * @param string $strAs
	 * @param string $strName
	 * @param bool $bolSkip If true, the element is only skipped during an persistent heritage operation.
	 * @return void
	 */
	public function noInherit($strAs, $strName='_ALL', $bolSkip=false) {
		$this->arrNoHeritage[$strAs][$strName] = $bolSkip;
	}

	/**
	 * This function accepts the inheritance brought through the inherit() function.
	 *
	 * @param string $strAs
	 * @param mixed $mxtValue
	 * @param string $strName
	 * @param bool $bolPersistent
	 * @return void
	 */
	public function passHeritage($strAs, $mxtValue, $strName, $bolPersistent) {
		$arrModes = array('attribute', 'value', 'link', 'dataset');
		if (in_array($strAs, $arrModes)) {
			if (!array_key_exists($strName, $this->arrNoHeritage[$strAs]) && !array_key_exists('_ALL', $this->arrNoHeritage[$strAs])) {
				if ($strAs == 'attribute')
					$this->setAttribute($strName, $mxtValue);
				if ($strAs == 'value')
					$this->setValue($mxtValue);
				if ($strAs == 'link')
					$this->setLink($strName, $mxtValue);
				if ($strAs == 'dataset')
					$this->setDataset($mxtValue, $strName);
			} else {
				if ($this->arrNoHeritage[$strAs][$strName] == false)
					$bolPersistent = false;
			}
			if ($bolPersistent)
				$this->inherit($strAs, $mxtValue, $strName, $bolPersistent);
		} else
			throw new Exception('Invalid operation mode: '.$strAs.'. Use "attribute", "value", "link" or "dataset" instead.');
	}
}

?>
