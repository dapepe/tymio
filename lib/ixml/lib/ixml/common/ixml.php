<?php
namespace Zeyon;

const IXMLVERSION = 1000;

const TYPE_BOOL = -1;
const TYPE_TYPE = -2;

const SIGN_SIGN    = 0;
const SIGN_MAP     = 1;
const SIGN_VAR     = 2;
const SIGN_VAR_T   = 3;
const SIGN_ATTR    = 4;
const SIGN_ATTR_T  = 5;
const SIGN_CDATA   = 6;
const SIGN_CDATA_T = 7;
const SIGN_CDATA_C = 8;
const SIGN_CHILD   = 9;
const SIGN_DESC    = 10;
const SIGN_FUNC    = 11;
const SIGN_STORE   = 12;
const SIGN_CONST   = 13;

// -------------------- Exceptions ------------------------

class iXmlExit extends \Exception {}

class iXmlReturn extends iXmlExit {}

class iXmlBreak extends iXmlReturn {
  protected $leap;

  public function __construct($leap) {
  	$this -> leap = $leap;
  }

	public function checkLeap() {
		if ( --$this -> leap > 0)
		  throw $this;

		return $this;
	}
}

class iXmlNext extends iXmlBreak {}

class iXmlException extends \Exception {
  public function __construct($message) {
    parent::__construct('iXML: '.$message);
  }
}

class iXmlParserException extends \Exception {
  public function __construct($parser, $message) {
    parent::__construct('iXML Parser ['.(xml_get_current_line_number($parser) + 1)."]: $message");
  }
}

class iXmlUserException extends \Exception {}

// -------------------- Implementation --------------------

interface iXmlComplex {
  public function __toString();
}

class iXmlArray implements iXmlComplex {
  public $array;

	public function __construct($array = []) {
		$this -> array = $array;
	}

  public function __toString() {
    return 'array';
  }
}

class iXmlObject extends iXmlArray {
  public $class;

	public function __construct($class) {
		$this -> array = $class -> prototype;
		$this -> class = $class;
	}
}

interface iXmlSubroutine extends iXmlComplex {}

abstract class iXmlFunction implements iXmlSubroutine {
  public function __toString() {
    return 'function';
  }
}

class iXmlFunctionSimple extends iXmlFunction {
  public $elems;
  public $vars = [];

  public function __construct($elems) {
    $this -> elems = $elems;
  }
}

class iXmlFunctionClosure extends iXmlFunctionSimple {
  protected $ref = [];

  public function __construct($elems, $vars) {
    $this -> elems = $elems;
    $this -> vars = $vars;

    foreach ($vars as &$value)
      $this -> ref[] =& $value; // Preserve references
  }
}

interface iXmlBind {
  public function call($params);
}

class iXmlFunctionSoap extends iXmlFunction implements iXmlBind {
  protected $client;
  protected $operation;

  public function __construct($client, $operation) {
  	$this -> client = $client;
  	$this -> operation = $operation;
  }

  public function call($params) {
  	$args = [];

    foreach ($params as $name => $value)
      if ($name === '')
        $args[] = $value;
      else
        $args[$name] = $value;

    return iXml::import($this -> client -> __soapCall($this -> operation, iXml::export($args)));
  }

  public static function createClient($wsdl) {
    return new \SoapClient($wsdl, ['compression' => SOAP_COMPRESSION_ACCEPT, 'connection_timeout' => 30, 'features' => SOAP_SINGLE_ELEMENT_ARRAYS]);
  }
}

class iXmlFunctionRest extends iXmlFunction implements iXmlBind {
  protected $url;
  protected $timeout;
  protected $route;
  protected $method;
  protected $raw;

  public function __construct($url, $timeout, $route, $method, $raw) {
  	$this -> url = substr($url, -1) === '/' ? $url : "$url/";
  	$this -> timeout = $timeout;
  	$this -> route = ( $route = trim($route, '/') ) === '' ? [] : explode('/', $route);
  	$this -> method = strtoupper($method);
  	$this -> raw = $raw;
  }

  public function call($params) {
    $args = [];
    $content = '';
    $route = $this -> route;

    foreach ($params as $name => $value)
      if ($name === '')
        $content = "$value";
      else if (( $key = array_search(":$name", $route) ) === false)
        $args[$name] = $value;
      else
        $route[$key] = "$value";

    $url = $this -> url.join('/', $route);

    if ($args) {
      $query = http_build_query(iXml::export($args), '', '&');

      if ($this -> method === 'POST')
        $content = $query;
      else
        $url .= "?$query";
    }

    $return = iXml::sendHttpRequest($url, $this -> method, $this -> timeout, $content);

    if ($this -> raw)
      return new iXmlArray($return);

    $body = $return['body'];

    if (( $status = $return['status'] ) < 200 || $status >= 300)
      throw new iXmlException("REST $status $this->method $url -> $body");

    switch ( $type = $return['type'] ) {
      case 'application/json':
        return iXml::import(decodeJson($body));

      case 'text/csv':
        $csv = decodeCsv($body);

        foreach ($csv as &$row)
          $row = new iXmlArray($row);

        return new iXmlArray($csv);
    }

    if (preg_match('/[\/+]xml$/D', $type)) {
      loadCommon('xml');

      return iXml::import((new Xml) -> parse($body));
    }

    return $body;
  }
}

class iXmlMacro implements iXmlSubroutine  {
  public $elems;

	public function __construct($elems) {
		$this -> elems = $elems;
	}

  public function __toString() {
    return 'macro';
  }
}

class iXmlClass implements iXmlComplex {
	public $extends;
	public $parents;
	public $constructors;
	public $prototype;

	public function __construct($extends, $parents, $constructors, $prototype) {
		$this -> extends = $extends;
		$this -> parents = $parents;
		$this -> constructors = $constructors;
		$this -> prototype = $prototype;
	}

  public function __toString() {
    return 'class';
  }
}

class iXml {
  public $root;

  protected $undefined;

  protected $schema;
  protected $stack;
  protected $index;

  protected $global;
  protected $local;
  protected $structs;
  protected $includeonce;
  protected $timer;

  public function __construct($code = '') {
    $this -> undefined = new \stdClass;

  	$this -> root = $this -> parse($code);
  }

  protected function prepareSchema() {
    $this -> initSchema();

    $defaults = [
      TYPE_BOOL => false,
      TYPE_INT => 0,
      TYPE_FLOAT => 0.0,
      TYPE_STRING => '',
      TYPE_TYPE => ''
    ];

    foreach ($this -> schema as $name => &$sign) {
      if ( $store = isset($sign[SIGN_STORE]) OR isset($sign[SIGN_VAR])) {
        $var = ( $var =& $sign[SIGN_VAR] ) === null ? [] : array_flip((array) $var);
        $store AND $var[$sign[SIGN_STORE]] = true;
      }

      if (isset($sign[SIGN_ATTR]))
        foreach ($sign[SIGN_ATTR] as &$type)
          $type === false OR isset($type[0]) OR $type = [$type, $defaults[$type]];

      !isset($sign[SIGN_CDATA]) OR ( $type =& $sign[SIGN_CDATA] ) === false OR isset($type[0]) OR $type = [$type, $defaults[$type]];

      isset($sign[SIGN_FUNC]) OR isset($sign[SIGN_DESC]) OR $sign[SIGN_FUNC] = '_'.strtr($name, ':', '_');
    }
  }

  public function parse($code) {
    if (( $code = trim($code) ) === '')
      return [];

    $this -> schema || $this -> prepareSchema();

    $this -> stack = [];
    $this -> index = -1;

    $parser = xml_parser_create();

    $e = null;

    try {
      xml_set_object($parser, $this);
      xml_set_element_handler($parser, 'startTagCallback', 'endTagCallback');
      xml_set_character_data_handler($parser, 'cdataCallback');

      xml_parse($parser, preg_replace('/^(<\?xml.+?\?>)(?:\s*<!DOCTYPE[^>]+>)?/s', '$1<!DOCTYPE ixml [<!ENTITY n "&#10;"><!ENTITY r "&#13;"><!ENTITY t "&#9;"><!ENTITY rn "&#13;&#10;">]>', $code, 1), true) OR
      $e = new iXmlParserException($parser, xml_error_string(xml_get_error_code($parser)));
    } catch (\Exception $e) {}

    xml_parser_free($parser);

    if ($e)
      throw $e;

    return $this -> stack;
  }

  protected function startTagCallback($parser, $name, $attr) {
    if ( $index = ++$this -> index ) {
      if (!isset($this -> schema[$name]))
        throw new iXmlParserException($parser, "Unknown element '$name'");

      $elem = [SIGN_SIGN => $sign = $this -> schema[$name] , SIGN_MAP => &$map];
      $map = 0;

      if (isset($sign[SIGN_DESC]) && !isset($this -> stack[$index - 1][SIGN_SIGN][$name]))
        throw new iXmlParserException($parser, "Cannot use element '$name' as descendant");

      isset($sign[SIGN_STORE], $attr[ $store = $sign[SIGN_STORE] ]) AND $elem[SIGN_STORE] = $store;

      if (isset($sign[SIGN_VAR]))
        foreach ($sign[SIGN_VAR] as $attr_name => $nil) {
          if (isset($attr[$attr_name])) {
            $value = trim($attr[$attr_name]);
            unset($attr[$attr_name]);

            if ($value !== '') {
              if (! $value = $this -> prepareVar($value) )
                throw new iXmlParserException($parser, "Invalid variable name for attribute '$attr_name' in element '$name'");

              if (isset($value[2])) {
                $elem[SIGN_VAR_T][$attr_name] = $value;
                $map = 1;
              } else
                $elem[$attr_name] = $value;

              continue;
            }
          }

          $elem[$attr_name] = null;
        }

      if (isset($sign[SIGN_ATTR]))
    		foreach ($sign[SIGN_ATTR] as $attr_name => $type)
    		  if (isset($attr[$attr_name])) {
    		    $value = $this -> prepareTokens($attr[$attr_name]);
    		    unset($attr[$attr_name]);

    		    if (is_array($value)) {
    		      $elem[SIGN_ATTR_T][$attr_name] = [$value, $type];
    		      $map |= 2;
    		    } else
    		      $elem[$attr_name] = $type === false ? $value : $this -> processValue($value, $type);
    		  } else
    		    $elem[$attr_name] = $type === false ? '' : $type[1];

  		if ($attr)
  		  throw new iXmlParserException($parser, "Unknown attribute '".key($attr)."' in element '$name'");

      isset($sign[SIGN_CDATA]) AND $elem[SIGN_CDATA] = '';
    	isset($sign[SIGN_CONST]) AND $elem[SIGN_CONST] = $sign[SIGN_CONST];
    } else
      $elem = [];

    $this -> stack[$index] = $elem;
  }

  protected function endTagCallback($parser, $name) {
  	$stack =& $this -> stack;

    $elem = $stack[ $index =& $this -> index ];

  	if ($index) {
  	  unset($stack[ $index-- ]);

  	  $sign = $elem[SIGN_SIGN];
  	  unset($elem[SIGN_SIGN]);

  	  $map =& $elem[SIGN_MAP];

  	  if (isset($sign[SIGN_CDATA])) {
  	    $type = $sign[SIGN_CDATA];

  	    if (isset($elem[SIGN_CHILD])) {
  	      $elem[SIGN_CDATA_C] = $type;
  	      $map |= 4;
  	    } else if (is_array( $cdata = $this -> prepareTokens($elem[SIGN_CDATA]) )) {
	        unset($elem[SIGN_CDATA]);
	        $elem[SIGN_CDATA_T] = [$cdata, $type];
  	      $map |= 8;
	      } else
	        $elem[SIGN_CDATA] = $type === false ? $cdata : ($cdata === '' ? $type[1] : $this -> processValue($cdata, $type));
  	  }

  	  if ($map === 0)
  	    unset($elem[SIGN_MAP]);

	  	$parent =& $stack[$index];

	  	if (!isset($sign[SIGN_DESC])) {
	  	  unset($parent[SIGN_CDATA]); // Element with children does not need CDATA
	  	  $elem[SIGN_FUNC] = $sign[SIGN_FUNC];
	  	  $parent[SIGN_CHILD][] = $elem;
	  	} else if ($sign[SIGN_DESC] || isset($elem[SIGN_CHILD]))
	  	  $parent[( $desc = $parent[SIGN_SIGN][$name] ) === true ? $name : $desc][] = $elem;
  	} else
  	  $stack = $elem;
  }

  protected function cdataCallback($parser, $cdata) {
    $elem =& $this -> stack[$this -> index];
    isset($elem[SIGN_CDATA]) AND $elem[SIGN_CDATA] .= $cdata;
  }

  public function exec($vars = [], $structs = []) {
    $this -> global = self::import($vars) -> array;
    $this -> local = [];
    $this -> structs = $structs;
    $this -> includeonce = [];
    $this -> timer = microtime(true);

  	if (isset($this -> root[SIGN_CHILD]))
  	  try {
  	    $this -> run($this -> root[SIGN_CHILD]);
      } catch (iXmlExit $e) {}
  }

  protected function run($elems) {
    $result = null;

    foreach ($elems as $elem) {
      isset($elem[SIGN_MAP]) AND $elem = $this -> map($elem);

      $func = $elem[SIGN_FUNC];
      $result = $func[0] === '_' ? $this -> $func($elem) : $func($elem[SIGN_CDATA]);

      isset($elem[SIGN_STORE]) && $this -> setVar($elem[$elem[SIGN_STORE]], $result);
    }

    return $result;
  }

  protected function runSub($elems, $vars) {
    $preserve = $local =& $this -> local;
    $local = $vars;

    $e = null;

    try {
      $this -> run($elems);
    } catch (\Exception $e) {}

    $local = $preserve;

    if ($e && !$e instanceof iXmlReturn)
      throw $e;
  }

  protected function runStruct($name, $elems, $struct = []) {
    $structs =& $this -> structs;

    $preserve =& $structs[$name];
    $structs[$name] =& $struct;

	  $e = null;

	  try {
	    $this -> run($elems);
	  } catch (\Exception $e) {}

	  $structs[$name] =& $preserve;

	  if ($e)
	    throw $e;

    return $struct;
  }

  protected function &getStruct($name) {
    if (isset($this -> structs[$name]))
      return $this -> structs[$name];

    throw new iXmlException("Execution state not in context of '$name'");
  }

  protected function map($elem) {
    if (( $map = $elem[SIGN_MAP] ) & 1)
      foreach ($elem[SIGN_VAR_T] as $name => $var) {
        foreach ($var[2] as $index)
          $key = $this -> processTokens( $key =& $var[1][$index] );

        $elem[$name] = $var;
      }

    if ($map & 2)
      foreach ($elem[SIGN_ATTR_T] as $name => $attr)
        $elem[$name] = $this -> processTokens($attr[0], $attr[1]);

    if ($map & 8) {
      $cdata = $elem[SIGN_CDATA_T];
      $elem[SIGN_CDATA] = $this -> processTokens($cdata[0], $cdata[1]);
    } else if ($map & 4) {
      $cdata = $this -> run($elem[SIGN_CHILD]);

      if (( $type = $elem[SIGN_CDATA_C] ) === false)
        $cdata = "$cdata";
      else
        switch ($type[0]) {
          case TYPE_INT:
            $cdata = is_numeric($cdata) ? (int) +$cdata : $type[1];
            break;

          case TYPE_FLOAT:
            $cdata = is_numeric($cdata) ? (float) +$cdata : $type[1];
            break;

          case TYPE_TYPE:
            $cdata = strtolower("$cdata");
            break;

          case TYPE_BOOL:
            $cdata = (bool) $cdata;
            break;

          default:
            $cdata = "$cdata";
        }

      $elem[SIGN_CDATA] = $cdata;
    }

    return $elem;
  }

  protected function prepareVar($expression /* string */) {
    static $used;

    if (isset($used[$expression]))
      return $used[$expression];

    if (!preg_match('/^(\w+)((?:\.\w+|\[(?:(?>[^\[\]]+)|(?2))*\])+)?$/D', $expression, $matches))
      return;

    if (!isset($matches[2])) // Variable without keys
      return $used[$expression] = [$matches[1]];

    // Variable with keys

    $evaluate = null;

    preg_match_all('/(?<=\.)\w+|\[((?:(?>[^\[\]]+)|(?R))*)\]/S', $matches[2], $keys, PREG_SET_ORDER);

    foreach ($keys as $index => &$key)
      if (!isset($key[1]))
        $key = $key[0];
      else if (( $key = $key[1] ) === '')
        $key = null;
      else if (is_array( $key = $this -> prepareTokens($key) ))
        $evaluate[] = $index;

    return $used[$expression] = [$matches[1], $keys, $evaluate];
  }

  protected function prepareTokens($expression /* string */) {
    if (strpos($expression, '$') === false)
      return $expression;

    static $used;

    if (( $tokens =& $used[$expression] ) !== null)
      return $tokens;

    // Single variable substitution

    if (
      $expression[0] === '$' &&
      preg_match('/^\$(\w+)((?:\.\w+|\[(?:(?>[^$\[\]]+)|(?2))*\])+)?\$?$/D', $expression, $matches)
    ) {
      if (!isset($matches[2])) // Variable without keys
        return $tokens = [null, $matches[1]];

      // Variable with keys

      preg_match_all('/(?<=\.)\w+|\[((?:(?>[^\[\]]+)|(?R))*)\]/S', $matches[2], $keys, PREG_SET_ORDER);

      foreach ($keys as &$key)
        $key = $key[isset($key[1]) ? 1 : 0];

      return $tokens = [null, $matches[1], $keys];
    }

    // Complex substitution

    $evaluate = null;

    preg_match_all('/\$\$|(?:\$(?![$(\w])|[^$]+)+|\$(\w+)((?:\.\w+|\[(?:(?>[^\[\]]+)|(?2))*\])+)?(?:\$|(\((?:(?>[^()]+)|(?3))*\)))?|\$(\((?:(?>[^()]+)|(?4))*\))/S', $expression, $values, PREG_SET_ORDER);

    foreach ($values as $index => &$value)
      if (isset($value[4])) { // Arithmetic expression
        if (is_array( $value = $this -> prepareTokens($value[4]) ))
          $evaluate[$index] = true;
        else
          $value = ''.+$this -> processMath($value);
      } else if (isset($value[1])) { // Variable
        $match = $value;

	      if (empty($match[2])) { // Variable without keys
	        $value = [$match[1]];
	        $eval = 0;
	      } else { // Variable with keys
          $keys_evaluate = null;

	        preg_match_all('/(?<=\.)\w+|\[((?:(?>[^\[\]]+)|(?R))*)\]/S', $match[2], $keys, PREG_SET_ORDER);

	        foreach ($keys as $keys_index => &$key)
	          if (!isset($key[1]))
	            $key = $key[0];
	          else if (is_array( $key = $this -> prepareTokens($key[1]) ))
              $keys_evaluate[$keys_index] = true;

          $value = [$match[1], $keys, $keys_evaluate];
          $eval = 1;
        }

	      if (isset($match[3])) { // Inline function
	        $eval |= 2;
	        is_array( $value[] = $this -> prepareTokens(substr($match[3], 1, -1)) ) AND $eval |= 4;
	      }

	      $evaluate[$index] = $eval;
      } else if (( $value = $value[0] ) === '$$') // Simple value
        $value = '$';

    return $tokens = $evaluate ? [count($values) === 1, $values, $evaluate] : join('', $values);
  }

  protected function processTokens($tokens, $type = false) {
    if ($type === false)
      $numeric = false;
    else
      switch ($type[0]) {
        case TYPE_INT:
        case TYPE_FLOAT:
          $numeric = true;
          break;

        default:
          $numeric = false;
      }

    // Single variable substitution

    if (( $one = $tokens[0] ) === null) {
      $vars = $this -> local;

      if (!isset($vars[ $name = $tokens[1] ])) {
        $vars = $this -> global;

        if (!isset($vars[$name]) || array_key_exists($name, $this -> local))
          goto undefined;
      }

      if (isset($tokens[2])) // Variable with keys
        foreach ($tokens[2] as $key) {
          if (!( $array = $vars[$name] ) instanceof iXmlArray)
            goto undefined;

          $vars = $array -> array;

          if (!isset($vars[ $name = $key ]))
            goto undefined;
        }

      $result = $vars[$name];

      if ($numeric && !is_numeric($result)) {
        undefined:

        if ($numeric)
          return $type[1];

        $result = '';
      } else
        $result = "$result";

      goto result;
    }

    // Complex substitution

    $values = $tokens[1];

    foreach ($tokens[2] as $index => $eval) {
      $value =& $values[$index];

      if ($eval === true) // Arithmetic expression
        $value = ''.+$this -> processMath($this -> processTokens($value, [TYPE_FLOAT, 0]));
      else { // Variable
        $vars = $this -> local;

        if (!isset($vars[ $name = $value[0] ])) {
          $vars = $this -> global;

          if (!isset($vars[$name]) || array_key_exists($name, $this -> local))
            goto void;
        }

        if ($eval & 1) { // Variable with keys
          $keys_evaluate = $value[2];

          foreach ($value[1] as $keys_index => $key) {
            if (!( $that = $vars[$name] ) instanceof iXmlArray)
              goto void;

            $vars = $that -> array;

            if (!isset($vars[ $name = isset($keys_evaluate[$keys_index]) ? $this -> processTokens($key) : $key ]))
              goto void;
          }
        } else
          $that = null;

        $return = $vars[$name];

        if ($eval & 2) { // Inline function
          if (!$return instanceof iXmlFunction)
            goto void;

          $value = $value[$eval & 1 ? 3 : 1];

          $eval & 4 AND $value = $this -> processTokens($value);

          if ($return instanceof iXmlBind)
            $return = $return -> call(['' => $value]);
          else {
            $vars = $return -> vars;
            $that === null OR $vars['this'] = $that;
            $vars['return'] =& $value;

            $this -> runSub($return -> elems, $vars);

            $return = $value;
          }
        }

        if ($numeric && !is_numeric($return)) {
          if ($one)
            return $type[1];

          $value = ' 0 ';
        } else
          $value = "$return";

        continue;

        void:

        if ($numeric) {
          if ($one)
            return $type[1];

          $value = ' 0 ';
        } else
          $value = '';
      }
    }

    $result = $one ? $values[0] : join('', $values);

    result:
    return $type === false ? $result : $this -> processValue($result, $type);
  }

  protected function processValue($value /* string */, $type) {
  	switch ($type[0]) {
    	case TYPE_INT:
    	  return ( $value = $this -> processMath($value) ) === null ? $type[1] : (int) +$value;

    	case TYPE_FLOAT:
    	  return ( $value = $this -> processMath($value) ) === null ? $type[1] : (float) +$value;

  		case TYPE_TYPE:
  			return strtolower($value);

      case TYPE_BOOL:
        return (bool) $value;
  	}

    return $value;
  }

  protected function processMath($value /* string */) {
    if (
      is_numeric($value) ||
      rtrim($value, "0123456789.eExXb+-*/%&|^~<>() \n\r\t") === '' && ( $value = @eval("return +($value);") ) !== false
    )
      return $value;
  }

  protected function setVar($var, $value) {
    $vars =& $this -> local;

    if (!isset($vars[ $name = $var[0] ])) {
      $global =& $this -> global;

      isset($global[$name]) || array_key_exists($name, $global) AND !array_key_exists($name, $vars) AND $vars =& $global;
    }

  	if (isset($var[1])) {
  	  foreach ($var[1] as $key) {
  	    if ($name === null) {
  	      unset($array);
  	      $vars[] = $array = new iXmlArray;
  	    } else if (!( $array =& $vars[$name] ) instanceof iXmlArray)
  	      $array = new iXmlArray;

  	    $vars =& $array -> array;
  	    $name = $key;

  	    unset($array);
  	  }

  	  if ($name === null) {
  	    $vars[] = $value;
  	    return;
  	  }
  	}

  	$vars[$name] = $value;
  }

  protected function setVarVirtual(&$vars, $var, $value) {
    $name = $var[0];

  	if (isset($var[1])) {
  	  foreach ($var[1] as $key) {
  	    if ($name === null) {
  	      unset($array);
  	      $vars[] = $array = new iXmlArray;
  	    } else if (!( $array =& $vars[$name] ) instanceof iXmlArray)
  	      $array = new iXmlArray;

  	    $vars =& $array -> array;
  	    $name = $key;
  	  }

  	  if ($name === null) {
  	    $vars[] = $value;
  	    return;
  	  }
  	}

  	$vars[$name] = $value;
  }

  protected function setVarArray($var, $array) {
    $this -> setVar($var, new iXmlArray($array));
  }

  protected function unsetVar($var) {
  	$vars =& $this -> local;

  	isset($vars[ $name = $var[0] ]) OR array_key_exists($name, $vars) OR $vars =& $this -> global;

    if (isset($var[1]))
      foreach ($var[1] as $key) {
        if (!isset($vars[$name]) || !( $array = $vars[$name] ) instanceof iXmlArray)
          return;

        $vars =& $array -> array;
        $name = $key;
      }

    unset($vars[$name]);
  }

  protected function getVar($var, $undefined = false) {
  	$vars = $this -> local;

  	isset($vars[ $name = $var[0] ]) OR array_key_exists($name, $vars) OR $vars = $this -> global;

	  if (isset($var[1]))
	    foreach ($var[1] as $key) {
        if (!isset($vars[$name]) || !( $array = $vars[$name] ) instanceof iXmlArray)
          return $undefined ? $this -> undefined : null;

        $vars = $array -> array;
        $name = $key;
	    }

	  if (isset($vars[$name]))
	    return $vars[$name];

	  if ($undefined && !array_key_exists($name, $vars))
	    return $this -> undefined;
  }

  protected function &getVarRef($var) {
    $vars =& $this -> local;

    if (!isset($vars[ $name = $var[0] ])) {
      $global =& $this -> global;

      isset($global[$name]) || array_key_exists($name, $global) AND !array_key_exists($name, $vars) AND $vars =& $global;
    }

  	if (isset($var[1])) {
  	  foreach ($var[1] as $key) {
  	    if ($name === null) {
  	      unset($array);
  	      $vars[] = $array = new iXmlArray;
  	    } else if (!( $array =& $vars[$name] ) instanceof iXmlArray)
  	      $array = new iXmlArray;

  	    $vars =& $array -> array;
  	    $name = $key;
  	  }

  	  if ($name === null)
  	    return $vars[];
  	}

  	return $vars[$name];
  }

  protected function getVarArray($var) {
    return $var && ( $array = $this -> getVar($var) ) instanceof iXmlArray ? $array -> array : [];
  }

  protected function getVarArrayExport($var) {
    return $var && ( $array = $this -> getVar($var) ) instanceof iXmlArray ? self::export($array) : [];
  }

  protected function &getVarRefArray($var) {
    ( $ref =& $this -> getVarRef($var) ) instanceof iXmlArray OR $ref = new iXmlArray;
    return $ref -> array;
  }

  protected function getVarClass($var) {
    if ($var) {
      if (( $class = $this -> getVar($var) ) instanceof iXmlClass)
        return $class;

      if ($class instanceof iXmlObject)
        return $class -> class;
    }

    throw new iXmlException("Undefined or invalid class '".$this -> getVarDebug($var)."'");
  }

  protected function getVarSerialized($var) {
    return $var && ( $value = $this -> getVar($var, true) ) === $this -> undefined ? '' : serialize(self::export($value));
  }

  protected function getVarDebug($var) {
    return $var ? $var[0].(isset($var[1]) ? '['.join('][', $var[1]).']' : '') : '';
  }

  protected function getPath($path) {
    return $path;
  }

  protected function getUrlHttp($url) {
    if (preg_match('/^https?:\/\//i', $url))
      return $url;

    throw new iXmlException("Invalid URL '$url' for HTTP request");
  }

  protected function debugOutput($data) {
    echo $data;
  }

  protected function debugLog($message) {
    error_log($message);
  }

  protected function unserialize($string) {
    return $string === '' ? null : self::import(unserialize($string));
  }

  final public static function import($struct) {
    if (!$struct instanceof iXmlComplex AND is_array($struct) || is_object($struct)) {
      $stack = [ $struct = new iXmlArray((array) $struct) ];

      for ($depth = 2, $index = 1; $index--;) {
        $size = --$depth;

        foreach ($stack[$index] -> array as &$value)
          if (!$value instanceof iXmlComplex AND is_array($value) || is_object($value)) {
            if ($size === $depth && ++$depth === 512)
              throw new iXmlException('Maximum stack depth exceeded for data import');

            $stack[ $index++ ] = $value = new iXmlArray((array) $value);
          }
      }
    }

    return $struct;
  }

  final public static function export($struct) {
    if ( $array = $struct instanceof iXmlArray OR is_array($struct)) {
      $array AND $struct = $struct -> array;
      $stack = [&$struct];

      for ($depth = 2, $index = 1; $index--;) {
        $size = --$depth;

        foreach ($stack[$index] as &$value) {
          if ($value instanceof iXmlArray) {
            if ($size === $depth && ++$depth === 512)
              throw new iXmlException('Maximum stack depth exceeded for data export');

            $value = $value -> array;
            $stack[ $index++ ] =& $value;
          } else if ($value instanceof iXmlComplex)
            $value = "$value";
        }
      }
    } else if ($struct instanceof iXmlComplex)
      return "$struct";

    return $struct;
  }

  final public static function sendHttpRequest($url, $method, $timeout, $content, $header = []) {
    array_unshift($header, 'Expect:');
    $method === 'POST' || array_unshift($header, 'Content-Type:');

    curl_setopt_array( $curl = curl_init($url) , [
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HEADER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_MAXREDIRS => 20,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_CONNECTTIMEOUT => 30,
      CURLOPT_TIMEOUT => $timeout,
      CURLOPT_POSTFIELDS => $content,
      CURLOPT_NOBODY => $method === 'HEAD',
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_HTTPHEADER => $header
    ]);

    $content = curl_exec($curl);

    if (curl_errno($curl))
      throw new \Exception(curl_error($curl));

    $info = curl_getinfo($curl);
    curl_close($curl);

    return [
      'status' => $info['http_code'],
      'type' => $info['content_type'],
      'header' => substr($content, 0, $pos = $info['header_size'] ),
      'body' => substr($content, $pos)
    ];
  }

  protected function initSchema() {
    $this -> schema = [
  	  'IF' => [
        SIGN_ATTR => [
  	      'FUNC' => false,
          'VALUE1' => false,
          'VALUE2' => false
        ],
        'ELSEIF' => 'ELSE_IF_IS',
        'ELSEIS' => 'ELSE_IF_IS',
        'ELSE' => true
  	  ],
      'ELSEIF' => [
        SIGN_ATTR => [
          'FUNC' => false,
          'VALUE1' => false,
          'VALUE2' => false
        ],
        SIGN_DESC => true,
        SIGN_CONST => true
      ],
      'ELSE' => [
  	    SIGN_DESC => false
  	  ],
  	  'IS' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
  	      'TYPE' => TYPE_TYPE
        ],
        'ELSEIF' => 'ELSE_IF_IS',
        'ELSEIS' => 'ELSE_IF_IS',
        'ELSE' => true
  	  ],
      'ELSEIS' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
  	      'TYPE' => TYPE_TYPE
        ],
        SIGN_DESC => true,
        SIGN_CONST => false
      ],
      'SWITCH' => [
        SIGN_ATTR => [
          'VALUE' => false
        ],
        'CASE' => true,
        'DEFAULT' => true
      ],
      'CASE' => [
        SIGN_ATTR => [
          'VALUE' => false
        ],
        SIGN_DESC => true
      ],
      'DEFAULT' => [
        SIGN_DESC => false
      ],
      'WHILE' => [
        SIGN_ATTR => [
          'FUNC' => false,
          'VALUE1' => false,
          'VALUE2' => false
        ],
        'ELSE' => true
      ],
      'FOR' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'FROM' => TYPE_INT,
          'STEP' => [TYPE_INT, 1],
          'TO' => TYPE_INT
        ]
      ],
      'FOREACH' => [
  	    SIGN_VAR => ['VAR', 'VAR_KEY', 'VAR_VALUE'],
        'ELSE' => true
      ],
      'EXIT' => [
        SIGN_CDATA => false
      ],
      'RETURN' => [],
      'BREAK' => [
        SIGN_ATTR => [
          'LEAP' => TYPE_INT
        ]
      ],
      'NEXT' => [
        SIGN_ATTR => [
          'LEAP' => TYPE_INT
        ]
      ],
      'INCLUDE' => [
  	    SIGN_VAR => 'VAR_PARAMS',
        SIGN_ATTR => [
          'FILENAME' => false,
          'ONCE' => TYPE_BOOL
        ],
        SIGN_STORE => 'VAR'
      ],
      'PARAM' => [
  	    SIGN_VAR => 'VAR',
  	    SIGN_ATTR => [
  	      'NAME' => false
  	    ],
        SIGN_CDATA => false
      ],
      'FUNCTION' => [
  	    SIGN_VAR => 'VAR',
        'USE' => true
      ],
      'USE' => [
  	    SIGN_VAR => 'VAR',
  	    SIGN_ATTR => [
  	      'NAME' => false
  	    ],
        SIGN_DESC => true
      ],
      'CALL' => [
  	    SIGN_VAR => ['FUNC', 'VAR_PARAMS', 'VAR_THIS'],
        SIGN_STORE => 'VAR'
      ],
      'MACRO' => [
  	    SIGN_VAR => 'VAR'
      ],
      'EXPAND' => [
  	    SIGN_VAR => 'MACRO'
      ],
      'CLASS' => [
  	    SIGN_VAR => 'VAR',
        'EXTENDS' => true,
        'PROPERTY' => true,
        'CONSTRUCTOR' => true,
        'METHOD' => true
      ],
      'EXTENDS' => [
  	    SIGN_VAR => 'CLASS',
        SIGN_DESC => true
      ],
      'PROPERTY' => [
  	    SIGN_VAR => 'VAR',
  	    SIGN_ATTR => [
  	      'NAME' => false
  	    ],
        SIGN_CDATA => false,
        SIGN_DESC => true
      ],
      'CONSTRUCTOR' => [
        SIGN_DESC => false
      ],
      'METHOD' => [
  	    SIGN_ATTR => [
  	      'NAME' => false
  	    ],
        SIGN_DESC => true
      ],
      'NEW' => [
  	    SIGN_VAR => ['CLASS', 'VAR', 'VAR_PARAMS']
      ],
  	  'TRY' => [
  	    'CATCH' => true,
  	    'ELSE' => true,
  	    'FINALLY' => true
  	  ],
  	  'CATCH' => [
  	    SIGN_VAR => 'VAR',
        SIGN_DESC => true
      ],
      'FINALLY' => [
        SIGN_DESC => false
      ],
      'ERROR' => [
        SIGN_CDATA => false
  	  ],
      'GLOBAL' => [
  	    SIGN_VAR => 'VAR'
      ],
      'TYPEOF' => [
  	    SIGN_VAR => 'VAR',
        SIGN_STORE => 'VAR_RESULT'
      ],
      'INSTANCEOF' => [
  	    SIGN_VAR => ['CLASS', 'VAR'],
        SIGN_STORE => 'VAR_RESULT'
      ],
      'CLASSINFO' => [
  	    SIGN_VAR => ['CLASS', 'VAR']
      ],
      'EQUALS' => [
  	    SIGN_VAR => ['VAR1', 'VAR2'],
        SIGN_STORE => 'VAR_RESULT'
      ],
      'SET' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'KEY' => [TYPE_STRING, null]
        ],
        SIGN_CDATA => false
      ],
      'UNSET' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'KEY' => [TYPE_STRING, null]
        ]
      ],
      'NULL' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'KEY' => [TYPE_STRING, null]
        ]
      ],
      'TRUE' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'KEY' => [TYPE_STRING, null]
        ]
      ],
      'FALSE' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'KEY' => [TYPE_STRING, null]
        ]
      ],
      'ASSIGN' => [
  	    SIGN_VAR => ['VAR', 'VAR_SOURCE'],
        SIGN_ATTR => [
          'KEY' => [TYPE_STRING, null],
          'KEY_SOURCE' => [TYPE_STRING, null]
        ]
      ],
      'CLONE' => [
  	    SIGN_VAR => ['VAR', 'VAR_RESULT']
      ],
      'SWAP' => [
  	    SIGN_VAR => ['VAR1', 'VAR2']
      ],
      'CAST' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'TYPE' => TYPE_TYPE
        ]
      ],
      'EVAL' => [
        SIGN_ATTR => [
          'TYPE' => TYPE_TYPE
        ],
        SIGN_CDATA => false,
        SIGN_STORE => 'VAR'
      ],
      'SERIALIZE' => [
  	    SIGN_VAR => 'VAR',
        SIGN_STORE => 'VAR_RESULT'
      ],
      'UNSERIALIZE' => [
        SIGN_CDATA => false,
  	    SIGN_STORE => 'VAR'
      ],
      'LENGTH' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\mb_strlen',
        SIGN_STORE => 'VAR'
      ],
      'SIZE' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\strlen',
        SIGN_STORE => 'VAR'
      ],
      'CHAR' => [
        SIGN_CDATA => TYPE_INT,
        SIGN_FUNC => '\Zeyon\chrUTF8',
        SIGN_STORE => 'VAR'
      ],
      'ORD' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\Zeyon\ordUTF8',
        SIGN_STORE => 'VAR'
      ],
      'CONCAT' => [
  	    SIGN_VAR => 'VAR',
        SIGN_CDATA => false
      ],
      'TOLOWER' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\mb_strtolower',
        SIGN_STORE => 'VAR'
      ],
      'TOUPPER' => [
        SIGN_ATTR => [
          'TYPE' => TYPE_TYPE
        ],
        SIGN_CDATA => false,
        SIGN_STORE => 'VAR'
      ],
      'TRIM' => [
        SIGN_ATTR => [
          'TYPE' => TYPE_TYPE
        ],
        SIGN_CDATA => false,
        SIGN_STORE => 'VAR'
      ],
      'POS' => [
        SIGN_ATTR => [
          'OFFSET' => TYPE_INT,
          'TYPE' => TYPE_TYPE,
          'VALUE' => false
        ],
        SIGN_CDATA => false,
        SIGN_STORE => 'VAR'
      ],
      'SUBSTR' => [
        SIGN_ATTR => [
          'LENGTH' => [TYPE_INT, null],
          'OFFSET' => TYPE_INT,
          'TYPE' => TYPE_TYPE
        ],
        SIGN_CDATA => false,
        SIGN_STORE => 'VAR'
      ],
      'PAD' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'LENGTH' => TYPE_INT,
          'PADDING' => [TYPE_STRING, ' '],
          'TYPE' => TYPE_TYPE
        ],
        SIGN_CDATA => false,
        SIGN_STORE => 'VAR'
      ],
      'MATCH' => [
        SIGN_VAR => 'VAR_MATCHES',
        SIGN_ATTR => [
          'OFFSET' => TYPE_INT,
          'PATTERN' => false
        ],
        SIGN_CDATA => false,
        SIGN_STORE => 'VAR'
      ],
      'REPLACE' => [
        SIGN_VAR => 'VAR_COUNT',
        SIGN_ATTR => [
          'LIMIT' => TYPE_INT,
          'PATTERN' => [TYPE_STRING, null],
          'REPLACEMENT' => false,
          'VALUE' => false
        ],
        SIGN_CDATA => false,
        SIGN_STORE => 'VAR'
      ],
      'SPLIT' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'DELIMITER' => false,
          'LIMIT' => TYPE_INT,
          'PATTERN' => [TYPE_STRING, null]
        ],
        SIGN_CDATA => false
      ],
      'CONVERT' => [
        SIGN_ATTR => [
          'FROM' => [TYPE_STRING, 'UTF-8'],
          'TO' => [TYPE_STRING, 'UTF-8']
        ],
        SIGN_CDATA => false,
        SIGN_STORE => 'VAR'
      ],
      'OUTPUT' => [
        SIGN_CDATA => false
      ],
      'HEADER' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\header'
      ],
      'SLEEP' => [
        SIGN_CDATA => TYPE_INT,
        SIGN_FUNC => '\usleep'
      ],
      'EXEC' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\shell_exec',
        SIGN_STORE => 'VAR'
      ],
      'MATH:ABS' => [
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_FUNC => '\abs',
        SIGN_STORE => 'VAR'
      ],
      'MATH:SIGN' => [
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_STORE => 'VAR'
      ],
      'MATH:INC' => [
  	    SIGN_VAR => 'VAR',
        SIGN_CDATA => [TYPE_INT, 1]
      ],
      'MATH:DEC' => [
  	    SIGN_VAR => 'VAR',
        SIGN_CDATA => [TYPE_INT, 1]
      ],
      'MATH:MOD' => [
        SIGN_ATTR => [
          'X' => TYPE_FLOAT,
          'Y' => TYPE_FLOAT
        ],
        SIGN_STORE => 'VAR'
      ],
      'MATH:EXP' => [
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_FUNC => '\exp',
        SIGN_STORE => 'VAR'
      ],
      'MATH:LN' => [
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_FUNC => '\log',
        SIGN_STORE => 'VAR'
      ],
      'MATH:LOG' => [
        SIGN_ATTR => [
          'BASE' => [TYPE_FLOAT, 10.0]
        ],
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_STORE => 'VAR'
      ],
      'MATH:POW' => [
        SIGN_ATTR => [
          'EXPONENT' => [TYPE_FLOAT, 2.0]
        ],
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_STORE => 'VAR'
      ],
      'MATH:SQRT' => [
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_FUNC => '\sqrt',
        SIGN_STORE => 'VAR'
      ],
      'MATH:HYPOT' => [
        SIGN_ATTR => [
          'X' => TYPE_FLOAT,
          'Y' => TYPE_FLOAT
        ],
        SIGN_STORE => 'VAR'
      ],
      'MATH:PI' => [
        SIGN_STORE => 'VAR'
      ],
      'MATH:SIN' => [
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_FUNC => '\sin',
        SIGN_STORE => 'VAR'
      ],
      'MATH:SINH' => [
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_FUNC => '\sinh',
        SIGN_STORE => 'VAR'
      ],
      'MATH:ASIN' => [
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_FUNC => '\asin',
        SIGN_STORE => 'VAR'
      ],
      'MATH:ASINH' => [
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_FUNC => '\asinh',
        SIGN_STORE => 'VAR'
      ],
      'MATH:COS' => [
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_FUNC => '\cos',
        SIGN_STORE => 'VAR'
      ],
      'MATH:COSH' => [
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_FUNC => '\cosh',
        SIGN_STORE => 'VAR'
      ],
      'MATH:ACOS' => [
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_FUNC => '\acos',
        SIGN_STORE => 'VAR'
      ],
      'MATH:ACOSH' => [
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_FUNC => '\acosh',
        SIGN_STORE => 'VAR'
      ],
      'MATH:TAN' => [
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_FUNC => '\tan',
        SIGN_STORE => 'VAR'
      ],
      'MATH:TANH' => [
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_FUNC => '\tanh',
        SIGN_STORE => 'VAR'
      ],
      'MATH:ATAN' => [
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_FUNC => '\atan',
        SIGN_STORE => 'VAR'
      ],
      'MATH:ATANH' => [
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_FUNC => '\atanh',
        SIGN_STORE => 'VAR'
      ],
      'MATH:ATAN2' => [
        SIGN_ATTR => [
          'X' => TYPE_FLOAT,
          'Y' => TYPE_FLOAT
        ],
        SIGN_STORE => 'VAR'
      ],
      'MATH:ROUND' => [
        SIGN_ATTR => [
          'PRECISION' => TYPE_INT
        ],
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_STORE => 'VAR'
      ],
      'MATH:CEIL' => [
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_FUNC => '\ceil',
        SIGN_STORE => 'VAR'
      ],
      'MATH:FLOOR' => [
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_FUNC => '\floor',
        SIGN_STORE => 'VAR'
      ],
      'MATH:RAND' => [
        SIGN_ATTR => [
          'MAX' => [TYPE_INT, null],
          'MIN' => TYPE_INT
        ],
        SIGN_STORE => 'VAR'
      ],
      'MATH:CONVERT' => [
        SIGN_ATTR => [
          'FROM' => TYPE_TYPE,
          'TO' => TYPE_TYPE
        ],
        SIGN_CDATA => false,
        SIGN_STORE => 'VAR'
      ],
      'MATH:FORMAT' => [
        SIGN_ATTR => [
          'COUNTDEC' => TYPE_INT,
          'DECPOINT' => [TYPE_STRING, '.'],
          'SEPARATOR' => [TYPE_STRING, ',']
        ],
        SIGN_CDATA => TYPE_FLOAT,
        SIGN_STORE => 'VAR'
      ],
      'DATE:NOW' => [
        SIGN_ATTR => [
          'MICRO' => TYPE_BOOL
        ],
        SIGN_STORE => 'VAR'
      ],
      'DATE:INFO' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'TIMEZONE' => false
        ],
        SIGN_CDATA => [TYPE_INT, null]
      ],
      'DATE:CREATE' => [
        SIGN_ATTR => [
          'DAY' => TYPE_INT,
          'HOUR' => TYPE_INT,
          'MINUTE' => TYPE_INT,
          'MONTH' => TYPE_INT,
          'SECOND' => TYPE_INT,
          'TIMEZONE' => false,
          'YEAR' => TYPE_INT
        ],
        SIGN_STORE => 'VAR'
      ],
      'DATE:PARSE' => [
        SIGN_ATTR => [
          'TIMEZONE' => false
        ],
        SIGN_CDATA => false,
        SIGN_STORE => 'VAR'
      ],
      'DATE:FORMAT' => [
        SIGN_ATTR => [
          'FORMAT' => [TYPE_STRING, 'c'],
          'TIMEZONE' => false
        ],
        SIGN_CDATA => [TYPE_INT, null],
        SIGN_STORE => 'VAR'
      ],
      'ARRAY' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'KEY' => [TYPE_STRING, null]
        ]
      ],
      'ITEM' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'KEY' => [TYPE_STRING, null]
        ],
        SIGN_CDATA => false
      ],
      'ARRAY:RANGE' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'FROM' => TYPE_INT,
          'STEP' => [TYPE_INT, 1],
          'TO' => TYPE_INT
        ]
      ],
      'ARRAY:ASSOC' => [
  	    SIGN_VAR => ['VAR', 'VAR_KEYS', 'VAR_VALUES']
      ],
      'ARRAY:POPULATE' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'PREFIX' => false
        ]
      ],
      'ARRAY:LENGTH' => [
        SIGN_VAR => 'VAR',
        SIGN_STORE => 'VAR_RESULT'
      ],
      'ARRAY:KEYEXISTS' => [
  	    SIGN_VAR => 'VAR',
        SIGN_CDATA => false,
        SIGN_STORE => 'VAR_RESULT'
      ],
      'ARRAY:VALUEEXISTS' => [
  	    SIGN_VAR => 'VAR',
        SIGN_CDATA => false,
        SIGN_STORE => 'VAR_RESULT'
      ],
      'ARRAY:POS' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'OFFSET' => TYPE_INT,
          'TYPE' => TYPE_TYPE
        ],
        SIGN_CDATA => false,
        SIGN_STORE => 'VAR_RESULT'
      ],
      'ARRAY:FIRST' => [
  	    SIGN_VAR => ['VAR', 'VAR_KEY'],
        SIGN_STORE => 'VAR_VALUE'
      ],
      'ARRAY:LAST' => [
  	    SIGN_VAR => ['VAR', 'VAR_KEY'],
        SIGN_STORE => 'VAR_VALUE'
      ],
      'ARRAY:RAND' => [
  	    SIGN_VAR => ['VAR', 'VAR_KEY'],
        SIGN_STORE => 'VAR_VALUE'
      ],
      'ARRAY:PUSH' => [
  	    SIGN_VAR => 'VAR',
        SIGN_CDATA => false
      ],
      'ARRAY:POP' => [
  	    SIGN_VAR => 'VAR',
        SIGN_STORE => 'VAR_RESULT'
      ],
      'ARRAY:SHIFT' => [
  	    SIGN_VAR => 'VAR',
        SIGN_STORE => 'VAR_RESULT'
      ],
      'ARRAY:UNSHIFT' => [
  	    SIGN_VAR => 'VAR',
        SIGN_CDATA => false
      ],
      'ARRAY:CONCAT' => [
  	    SIGN_VAR => ['VAR', 'VAR_RESULT', 'VAR_TAIL']
      ],
      'ARRAY:SLICE' => [
  	    SIGN_VAR => ['VAR', 'VAR_RESULT'],
        SIGN_ATTR => [
          'LENGTH' => [TYPE_INT, null],
          'OFFSET' => TYPE_INT
        ]
      ],
      'ARRAY:EXTRACT' => [
  	    SIGN_VAR => ['VAR', 'VAR_REPLACEMENT', 'VAR_RESULT'],
        SIGN_ATTR => [
          'LENGTH' => [TYPE_INT, null],
          'OFFSET' => TYPE_INT
        ]
      ],
      'ARRAY:PAD' => [
  	    SIGN_VAR => ['VAR', 'VAR_RESULT'],
        SIGN_ATTR => [
          'LENGTH' => TYPE_INT,
          'PADDING' => [TYPE_STRING, null]
        ]
      ],
      'ARRAY:REVERSE' => [
  	    SIGN_VAR => ['VAR', 'VAR_RESULT']
      ],
      'ARRAY:FLIP' => [
  	    SIGN_VAR => ['VAR', 'VAR_RESULT']
      ],
      'ARRAY:UNIQUE' => [
  	    SIGN_VAR => ['VAR', 'VAR_RESULT'],
        SIGN_ATTR => [
          'KEY' => [TYPE_STRING, null]
        ]
      ],
      'ARRAY:MERGE' => [
  	    SIGN_VAR => ['VAR', 'VAR_RESULT', 'VAR_SET']
      ],
      'ARRAY:COMPLEMENT' => [
  	    SIGN_VAR => ['VAR', 'VAR_RESULT', 'VAR_SET'],
        SIGN_ATTR => [
          'TYPE' => TYPE_TYPE
        ]
      ],
      'ARRAY:DIFF' => [
  	    SIGN_VAR => ['VAR', 'VAR_RESULT', 'VAR_SET'],
        SIGN_ATTR => [
          'TYPE' => TYPE_TYPE
        ]
      ],
      'ARRAY:INTERSECT' => [
  	    SIGN_VAR => ['VAR', 'VAR_RESULT', 'VAR_SET'],
        SIGN_ATTR => [
          'TYPE' => TYPE_TYPE
        ]
      ],
      'ARRAY:UNION' => [
  	    SIGN_VAR => ['VAR', 'VAR_RESULT', 'VAR_SET'],
        SIGN_ATTR => [
          'TYPE' => TYPE_TYPE
        ]
      ],
      'ARRAY:REPLACE' => [
  	    SIGN_VAR => ['VAR', 'VAR_REPLACEMENT', 'VAR_RESULT']
      ],
      'ARRAY:FILTER' => [
  	    SIGN_VAR => ['VAR', 'VAR_RESULT'],
        SIGN_ATTR => [
  	      'KEY' => [TYPE_STRING, null],
  	      'FUNC' => false,
          'VALUE' => false
        ]
      ],
      'ARRAY:TRIM' => [
  	    SIGN_VAR => ['VAR', 'VAR_RESULT'],
        SIGN_ATTR => [
          'KEY' => [TYPE_STRING, null],
          'TYPE' => TYPE_TYPE
        ]
      ],
      'ARRAY:KEYS' => [
  	    SIGN_VAR => ['VAR', 'VAR_RESULT']
      ],
      'ARRAY:VALUES' => [
  	    SIGN_VAR => ['VAR', 'VAR_RESULT'],
        SIGN_ATTR => [
          'KEY' => [TYPE_STRING, null]
        ]
      ],
      'ARRAY:CHUNK' => [
  	    SIGN_VAR => ['VAR', 'VAR_RESULT'],
        SIGN_ATTR => [
          'LENGTH' => TYPE_INT
        ]
      ],
      'ARRAY:GROUP' => [
  	    SIGN_VAR => ['VAR', 'VAR_RESULT'],
        SIGN_ATTR => [
          'KEY' => false
        ]
      ],
      'ARRAY:SORT' => [
  	    SIGN_VAR => ['VAR', 'VAR_RESULT'],
        SIGN_ATTR => [
          'KEY' => [TYPE_STRING, null],
          'TYPE' => TYPE_TYPE
        ]
      ],
      'ARRAY:AGGREGATE' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'KEY' => [TYPE_STRING, null],
          'TYPE' => TYPE_TYPE
        ],
        SIGN_STORE => 'VAR_RESULT'
      ],
      'ARRAY:JOIN' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'DELIMITER' => false,
          'KEY' => [TYPE_STRING, null]
        ],
        SIGN_STORE => 'VAR_RESULT'
      ],
      // FIX downward compatibility
      'ARRAY:TRANSFORM' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'TYPE' => TYPE_TYPE
        ]
      ],
      // FIX downward compatibility
      'ARRAY:COMBINE' => [
  	    SIGN_VAR => ['VAR1', 'VAR2', 'VAR_RESULT'],
        SIGN_ATTR => [
          'TYPE' => TYPE_TYPE
        ]
      ],
      'ENCODE:MD5' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\md5',
        SIGN_STORE => 'VAR'
      ],
      'ENCODE:SHA1' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\sha1',
        SIGN_STORE => 'VAR'
      ],
      'ENCODE:CRC32' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\Zeyon\crc32',
        SIGN_STORE => 'VAR'
      ],
      'ENCODE:SOUNDEX' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\soundex',
        SIGN_STORE => 'VAR'
      ],
      'ENCODE:METAPHONE' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\metaphone',
        SIGN_STORE => 'VAR'
      ],
      'ENCODE:ROT13' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\str_rot13',
        SIGN_STORE => 'VAR'
      ],
      'ENCODE:CRYPT' => [
  	    SIGN_VAR => 'VAR_IV',
        SIGN_ATTR => [
          'CIPHER' => TYPE_TYPE,
          'KEY' => false,
          'MODE' => TYPE_TYPE
        ],
        SIGN_CDATA => false,
        SIGN_STORE => 'VAR'
      ],
      'ENCODE:DEFLATE' => [
        SIGN_ATTR => [
          'LEVEL' => [TYPE_INT, -1]
        ],
        SIGN_CDATA => false,
        SIGN_STORE => 'VAR'
      ],
      'ENCODE:ZLIB' => [
        SIGN_ATTR => [
          'LEVEL' => [TYPE_INT, -1]
        ],
        SIGN_CDATA => false,
        SIGN_STORE => 'VAR'
      ],
      'ENCODE:XML' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\Zeyon\encodeXml',
        SIGN_STORE => 'VAR'
      ],
      'ENCODE:HTML' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\Zeyon\encodeHtml',
        SIGN_STORE => 'VAR'
      ],
      'ENCODE:URL' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\rawurlencode',
        SIGN_STORE => 'VAR'
      ],
      'ENCODE:BASE64' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\base64_encode',
        SIGN_STORE => 'VAR'
      ],
      'ENCODE:QUOTEDPRINT' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\quoted_printable_encode',
        SIGN_STORE => 'VAR'
      ],
      'ENCODE:JSON' => [
  	    SIGN_VAR => 'VAR',
        SIGN_STORE => 'VAR_RESULT'
      ],
      'ENCODE:CSV' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'DELIMITER' => [TYPE_STRING, ';']
        ],
        SIGN_STORE => 'VAR_RESULT'
      ],
      'ENCODE:BINARY' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'FORMAT' => false
        ],
        SIGN_STORE => 'VAR_RESULT'
      ],
      'DECODE:CRYPT' => [
        SIGN_ATTR => [
          'CIPHER' => TYPE_TYPE,
          'IV' => false,
          'KEY' => false,
          'MODE' => TYPE_TYPE
        ],
        SIGN_CDATA => false,
        SIGN_STORE => 'VAR'
      ],
      'DECODE:DEFLATE' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\gzinflate',
        SIGN_STORE => 'VAR'
      ],
      'DECODE:ZLIB' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\gzuncompress',
        SIGN_STORE => 'VAR'
      ],
      'DECODE:XML' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\Zeyon\decodeXml',
        SIGN_STORE => 'VAR'
      ],
      'DECODE:HTML' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\Zeyon\decodeHtml',
        SIGN_STORE => 'VAR'
      ],
      'DECODE:URL' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\rawurldecode',
        SIGN_STORE => 'VAR'
      ],
      'DECODE:BASE64' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\base64_decode',
        SIGN_STORE => 'VAR'
      ],
      'DECODE:QUOTEDPRINT' => [
        SIGN_CDATA => false,
        SIGN_FUNC => '\quoted_printable_decode',
        SIGN_STORE => 'VAR'
      ],
      'DECODE:JSON' => [
        SIGN_CDATA => false,
  	    SIGN_STORE => 'VAR'
      ],
      'DECODE:CSV' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'DELIMITER' => [TYPE_STRING, ';']
        ],
        SIGN_CDATA => false
      ],
      'DECODE:BINARY' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'FORMAT' => false
        ],
        SIGN_CDATA => false
      ],
      'XML:CREATE' => [
  	    SIGN_VAR => 'VAR',
        SIGN_STORE => 'VAR_RESULT'
      ],
      'XML:PARSE' => [
  	    SIGN_VAR => 'VAR',
        SIGN_CDATA => false
      ],
      'SOAP:CLIENT' => [
        SIGN_ATTR => [
          'WSDL' => false
        ],
        'SOAP:BIND' => true
      ],
      'SOAP:BIND' => [
  	    SIGN_VAR => 'VAR',
        SIGN_CDATA => false,
        SIGN_DESC => true
      ],
      'REST:CLIENT' => [
        SIGN_ATTR => [
          'TIMEOUT' => TYPE_INT,
          'URL' => false
        ],
        'REST:BIND' => true
      ],
      'REST:BIND' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'METHOD' => [TYPE_STRING, 'GET'],
          'TYPE' => TYPE_TYPE
        ],
        SIGN_CDATA => false,
        SIGN_DESC => true
      ],
      'REST:SERVER' => [
        'REST:RESOURCE' => true
      ],
      'REST:RESOURCE' => [
  	    SIGN_VAR => ['VAR_BODY', 'VAR_HEADER'],
        SIGN_ATTR => [
          'METHOD' => [TYPE_STRING, 'GET'],
          'ROUTE' => false
        ],
        SIGN_DESC => true,
        'USE' => true
      ],
      'HTTP:REQUEST' => [
  	    SIGN_VAR => 'VAR_INFO',
        SIGN_ATTR => [
          'METHOD' => [TYPE_STRING, 'GET'],
          'TIMEOUT' => TYPE_INT,
          'URL' => false
        ],
        SIGN_STORE => 'VAR',
        'HTTP:HEADER' => true,
        'HTTP:BODY' => true
      ],
      'HTTP:HEADER' => [
        SIGN_CDATA => false,
  	    SIGN_DESC => true
  	  ],
      'HTTP:BODY' => [
        SIGN_CDATA => false,
  	    SIGN_DESC => true
  	  ],
      'HTTP:URLINFO' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'URL' => false
        ]
      ],
      'HTTP:QUERY' => [
  	    SIGN_VAR => 'VAR',
        SIGN_STORE => 'VAR_RESULT'
      ],
      'FILE:PATHINFO' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'FILENAME' => false
        ]
      ],
      'FILE:EXISTS' => [
        SIGN_ATTR => [
          'FILENAME' => false
        ],
        SIGN_STORE => 'VAR'
      ],
      'FILE:TYPEOF' => [
        SIGN_ATTR => [
          'FILENAME' => false
        ],
        SIGN_STORE => 'VAR'
      ],
      'FILE:STATUS' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'FILENAME' => false,
          'FOLLOW' => TYPE_BOOL
        ]
      ],
      'FILE:READ' => [
  	    SIGN_VAR => 'VAR_STRUCT',
        SIGN_ATTR => [
          'FILENAME' => false
        ],
        SIGN_STORE => 'VAR'
      ],
      'FILE:WRITE' => [
  	    SIGN_VAR => 'VAR_STRUCT',
        SIGN_ATTR => [
          'FILENAME' => false
        ],
        SIGN_CDATA => false
      ],
      'FILE:APPEND' => [
        SIGN_ATTR => [
          'FILENAME' => false
        ],
        SIGN_CDATA => false
      ],
      'FILE:DELETE' => [
        SIGN_ATTR => [
          'FILENAME' => false
        ]
      ],
      'FILE:RENAME' => [
        SIGN_ATTR => [
          'NEWNAME' => false,
          'OLDNAME' => false
        ]
      ],
      'FILE:LINK' => [
        SIGN_ATTR => [
          'LINKNAME' => false,
          'TARGETNAME' => false
        ]
      ],
      'FILE:LISTDIR' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'DIRNAME' => false
        ]
      ],
      'FILE:MAKEDIR' => [
        SIGN_ATTR => [
          'DIRNAME' => false
        ]
      ],
      'FILE:REMOVEDIR' => [
        SIGN_ATTR => [
          'DIRNAME' => false
        ]
      ],
      'FILE:GLOB' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'PATTERN' => false
        ]
      ],
      'ZIP:ARCHIVE' => [
        SIGN_ATTR => [
          'FILENAME' => false
        ]
      ],
      'ZIP:EXISTS' => [
        SIGN_ATTR => [
          'FILENAME' => false
        ],
        SIGN_STORE => 'VAR'
      ],
      'ZIP:STATUS' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'FILENAME' => false
        ]
      ],
      'ZIP:READ' => [
  	    SIGN_VAR => 'VAR_STRUCT',
        SIGN_ATTR => [
          'FILENAME' => false
        ],
        SIGN_STORE => 'VAR'
      ],
      'ZIP:WRITE' => [
  	    SIGN_VAR => 'VAR_STRUCT',
        SIGN_ATTR => [
          'FILENAME' => false
        ],
        SIGN_CDATA => false
      ],
      'ZIP:DELETE' => [
        SIGN_ATTR => [
          'FILENAME' => false
        ]
      ],
      'ZIP:RENAME' => [
        SIGN_ATTR => [
          'NEWNAME' => false,
          'OLDNAME' => false
        ]
      ],
      'ZIP:LIST' => [
  	    SIGN_VAR => 'VAR',
      ],
      'DB:CONNECTION' => [
        SIGN_ATTR => [
          'DBNAME' => false,
          'PASSWORD' => false,
          'SERVER' => false,
          'TYPE' => TYPE_TYPE,
          'USERNAME' => false
        ]
      ],
      'DB:GET' => [
  	    SIGN_VAR => ['VAR', 'VAR_FIELDS'],
        SIGN_ATTR => [
          'ENTITY' => false,
          'ID' => TYPE_INT
        ]
      ],
      'DB:FIELD' => [
        SIGN_ATTR => [
          'ALIAS' => false
        ],
        SIGN_CDATA => false
      ],
      'DB:EXPRESSION' => [
        SIGN_ATTR => [
          'ALIAS' => false
        ],
        SIGN_CDATA => false
      ],
      'DB:SET' => [
  	    SIGN_VAR => 'VAR_DATA',
        SIGN_ATTR => [
          'ENTITY' => false,
          'ID' => [TYPE_INT, null]
        ],
        SIGN_STORE => 'VAR'
      ],
      'DB:DATA' => [
        SIGN_ATTR => [
          'ALIAS' => false,
          'FIELD' => false
        ],
        SIGN_CDATA => false
      ],
      'DB:NULL' => [
        SIGN_ATTR => [
          'ALIAS' => false,
          'FIELD' => false
        ]
      ],
      'DB:REMOVE' => [
        SIGN_ATTR => [
          'ENTITY' => false,
          'ID' => TYPE_INT
        ]
      ],
      'DB:LOOKUP' => [
        SIGN_ATTR => [
          'ENTITY' => false
        ],
        SIGN_STORE => 'VAR'
      ],
      'DB:SELECT' => [
  	    SIGN_VAR => ['VAR_COUNT', 'VAR_RESULT'],
        SIGN_ATTR => [
          'DISTINCT' => TYPE_BOOL,
          'LIMIT' => TYPE_INT,
          'OFFSET' => TYPE_INT,
          'TYPE' => TYPE_TYPE
        ],
        SIGN_STORE => 'VAR',
        'DB:FIELDS' => true,
        'DB:TABLE' => true,
        'DB:JOIN' => true,
        'DB:GROUPBY' => true,
        'DB:HAVING' => true,
        'DB:ORDERBY' => true
      ],
      'DB:FIELDS' => [
  	    SIGN_VAR => 'VAR',
        SIGN_DESC => true
      ],
      'DB:TABLE' => [
        SIGN_ATTR => [
          'ALIAS' => false
        ],
        SIGN_CDATA => false,
        SIGN_DESC => true
      ],
      'DB:JOIN' => [
        SIGN_DESC => false
      ],
      'DB:GROUPBY' => [
  	    SIGN_VAR => 'VAR',
        SIGN_DESC => true
      ],
      'DB:HAVING' => [
        SIGN_DESC => true
      ],
      'DB:ORDERBY' => [
  	    SIGN_VAR => 'VAR',
        SIGN_DESC => true
      ],
      'DB:CROSS' => [
        SIGN_ATTR => [
          'ALIAS' => false,
          'TABLE' => false
        ]
      ],
      'DB:NATURAL' => [
        SIGN_ATTR => [
          'ALIAS' => false,
          'TABLE' => false
        ]
      ],
      'DB:INNER' => [
        SIGN_ATTR => [
          'ALIAS' => false,
          'FIELD1' => false,
          'FIELD2' => false,
          'FUNC' => false,
          'TABLE' => false
        ]
      ],
      'DB:LEFTOUTER' => [
        SIGN_ATTR => [
          'ALIAS' => false,
          'FIELD1' => false,
          'FIELD2' => false,
          'FUNC' => false,
          'TABLE' => false
        ]
      ],
      'DB:RIGHTOUTER' => [
        SIGN_ATTR => [
          'ALIAS' => false,
          'FIELD1' => false,
          'FIELD2' => false,
          'FUNC' => false,
          'TABLE' => false
        ]
      ],
      'DB:GROUPFIELD' => [
        SIGN_CDATA => false
      ],
      'DB:ORDERFIELD' => [
        SIGN_ATTR => [
          'TYPE' => TYPE_TYPE
        ],
        SIGN_CDATA => false
      ],
      'DB:AND' => [],
      'DB:OR' => [],
      'DB:NOT' => [],
      'DB:IS' => [
        SIGN_ATTR => [
          'FIELD' => [TYPE_STRING, null],
          'FIELD1' => false,
          'FIELD2' => false,
          'FUNC' => false
        ],
        SIGN_CDATA => false
      ],
      'DB:ISNULL' => [
        SIGN_ATTR => [
          'FIELD' => false
        ]
      ],
      'DB:ISNOTNULL' => [
        SIGN_ATTR => [
          'FIELD' => false
        ]
      ],
      'DB:ALL' => [
        SIGN_ATTR => [
          'FIELD' => false,
          'FUNC' => false
        ],
        SIGN_CDATA => false
      ],
      'DB:ANY' => [
        SIGN_ATTR => [
          'FIELD' => false,
          'FUNC' => false
        ],
        SIGN_CDATA => false
      ],
      'DB:EXISTS' => [
        SIGN_CDATA => false
      ],
      'DB:NOTEXISTS' => [
        SIGN_CDATA => false
      ],
      'DB:IN' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'FIELD' => false
        ],
        SIGN_CDATA => false
      ],
      'DB:NOTIN' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'FIELD' => false
        ],
        SIGN_CDATA => false
      ],
      'DB:SEARCH' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'QUERY' => false
        ]
      ],
      'DB:SEARCHFIELD' => [
        SIGN_CDATA => false
      ],
      'DB:INSERT' => [
  	    SIGN_VAR => 'VAR_DATA',
        SIGN_ATTR => [
          'TABLE' => false
        ],
        SIGN_STORE => 'VAR'
      ],
      'DB:UPDATE' => [
  	    SIGN_VAR => 'VAR_DATA',
        SIGN_ATTR => [
          'TABLE' => false
        ]
      ],
      'DB:DELETE' => [
        SIGN_ATTR => [
          'TABLE' => false
        ]
      ],
      'DB:TRANSACTION' => [],
      'MONGO:CONNECTION' => [
        SIGN_ATTR => [
          'DBNAME' => false,
          'PASSWORD' => false,
          'SERVER' => false,
          'USERNAME' => false
        ]
      ],
      'MONGO:GET' => [
  	    SIGN_VAR => ['VAR', 'VAR_FIELDS'],
        SIGN_ATTR => [
          'COLLECTION' => false,
          'ID' => false
        ]
      ],
      'MONGO:FIELD' => [
        SIGN_CDATA => false
      ],
      'MONGO:SET' => [
  	    SIGN_VAR => 'VAR_DATA',
        SIGN_ATTR => [
          'COLLECTION' => false,
          'ID' => false
        ],
        SIGN_STORE => 'VAR'
      ],
      'MONGO:DATA' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'FIELD' => false
        ],
        SIGN_CDATA => false
      ],
      'MONGO:NULL' => [
        SIGN_ATTR => [
          'FIELD' => false
        ]
      ],
      'MONGO:TRUE' => [
        SIGN_ATTR => [
          'FIELD' => false
        ]
      ],
      'MONGO:FALSE' => [
        SIGN_ATTR => [
          'FIELD' => false
        ]
      ],
      'MONGO:REMOVE' => [
        SIGN_ATTR => [
          'COLLECTION' => false,
          'ID' => false
        ]
      ],
      'MONGO:LOOKUP' => [
        SIGN_ATTR => [
          'COLLECTION' => false
        ],
        SIGN_STORE => 'VAR'
      ],
      'MONGO:DISTINCT' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'COLLECTION' => false,
          'FIELD' => false
        ]
      ],
      'MONGO:FIND' => [
  	    SIGN_VAR => ['VAR_COUNT', 'VAR_RESULT'],
        SIGN_ATTR => [
          'COLLECTION' => false,
          'LIMIT' => TYPE_INT,
          'OFFSET' => TYPE_INT,
          'TYPE' => TYPE_TYPE
        ],
        'MONGO:FIELDS' => true,
        'MONGO:SORT' => true
      ],
      'MONGO:FIELDS' => [
  	    SIGN_VAR => 'VAR',
        SIGN_DESC => true
      ],
      'MONGO:SORT' => [
  	    SIGN_VAR => 'VAR',
        SIGN_DESC => true
      ],
      'MONGO:SORTFIELD' => [
        SIGN_ATTR => [
          'TYPE' => TYPE_TYPE
        ],
        SIGN_CDATA => false
      ],
      'MONGO:AND' => [],
      'MONGO:OR' => [],
      'MONGO:IS' => [
        SIGN_ATTR => [
          'FIELD' => false,
          'FUNC' => false
        ],
        SIGN_CDATA => false
      ],
      'MONGO:ISNULL' => [
        SIGN_ATTR => [
          'FIELD' => false
        ]
      ],
      'MONGO:ISNOTNULL' => [
        SIGN_ATTR => [
          'FIELD' => false
        ]
      ],
      'MONGO:ISTRUE' => [
        SIGN_ATTR => [
          'FIELD' => false
        ]
      ],
      'MONGO:ISFALSE' => [
        SIGN_ATTR => [
          'FIELD' => false
        ]
      ],
      'MONGO:ALL' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'FIELD' => false
        ]
      ],
      'MONGO:EXISTS' => [
        SIGN_ATTR => [
          'FIELD' => false
        ]
      ],
      'MONGO:NOTEXISTS' => [
        SIGN_ATTR => [
          'FIELD' => false
        ]
      ],
      'MONGO:IN' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'FIELD' => false
        ]
      ],
      'MONGO:NOTIN' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'FIELD' => false
        ]
      ],
      'MONGO:INSERT' => [
  	    SIGN_VAR => 'VAR_DATA',
        SIGN_ATTR => [
          'COLLECTION' => false
        ],
        SIGN_STORE => 'VAR'
      ],
      'MONGO:UPDATE' => [
  	    SIGN_VAR => 'VAR_DATA',
        SIGN_ATTR => [
          'COLLECTION' => false
        ]
      ],
      'MONGO:DELETE' => [
        SIGN_ATTR => [
          'COLLECTION' => false
        ]
      ],
      'MAIL:PARSE' => [
  	    SIGN_VAR => 'VAR',
        SIGN_CDATA => false
      ],
      'MAIL:SEND' => [
        'MAIL:HEADER' => true,
        'MAIL:BODY' => true
      ],
      'MAIL:HEADER' => [
        SIGN_CDATA => false,
  	    SIGN_DESC => true
  	  ],
      'MAIL:BODY' => [
        SIGN_CDATA => false,
  	    SIGN_DESC => true
  	  ],
      'MAIL:RECEIVE' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'MAILBOX' => false,
          'PASSWORD' => false,
          'USERNAME' => false
        ]
      ],
      'MAIL:MULTIPART' => [
        SIGN_ATTR => [
          'BOUNDARY' => false
        ],
        SIGN_STORE => 'VAR'
      ],
      'MAIL:PART' => [
        'MAIL:HEADER' => true,
        'MAIL:BODY' => true
      ],
      'MAIL:QUOTE' => [
        SIGN_CDATA => false,
        SIGN_STORE => 'VAR'
      ],
      'EXCEL:WORKBOOK' => [
        SIGN_ATTR => [
          'FILENAME' => false,
          'FORMAT' => false,
          'KEYWORDS' => [TYPE_STRING, null],
          'SUBJECT' => [TYPE_STRING, null],
          'TITLE' => [TYPE_STRING, null]
        ]
      ],
      'EXCEL:LENGTH' => [
        SIGN_VAR => ['VAR_COLS', 'VAR_ROWS', 'VAR_SHEETS']
      ],
      'EXCEL:NEXT' => [
        SIGN_ATTR => [
          'OFFSET_COL' => [TYPE_INT, 1],
          'OFFSET_ROW' => TYPE_INT,
          'OFFSET_SHEET' => TYPE_INT
        ]
      ],
      'EXCEL:GETPOS' => [
        SIGN_VAR => ['VAR_COL', 'VAR_COORDS', 'VAR_ROW', 'VAR_SHEET']
      ],
      'EXCEL:SETPOS' => [
        SIGN_ATTR => [
          'COL' => [TYPE_INT, null],
          'COORDS' => false,
          'ROW' => [TYPE_INT, null],
          'SHEET' => [TYPE_INT, null]
        ]
      ],
      'EXCEL:GET' => [
        SIGN_ATTR => [
          'COORDS' => false,
          'TYPE' => TYPE_TYPE
        ],
        SIGN_STORE => 'VAR'
      ],
      'EXCEL:SET' => [
        SIGN_ATTR => [
          'COORDS' => false,
          'FORMAT' => [TYPE_STRING, null],
          'TYPE' => TYPE_TYPE
        ],
        SIGN_CDATA => false
      ],
      'EXCEL:STYLE' => [
  	    SIGN_VAR => 'VAR',
        SIGN_CDATA => false
      ],
      'EXCEL:AUTOFILTER' => [
        SIGN_CDATA => false
      ],
      'EXCEL:ADDCOL' => [
        SIGN_ATTR => [
          'INDEX' => [TYPE_INT, null],
          'WIDTH' => [TYPE_FLOAT, null]
        ]
      ],
      'EXCEL:REMOVECOL' => [
        SIGN_ATTR => [
          'INDEX' => [TYPE_INT, null]
        ]
      ],
      'EXCEL:ADDROW' => [
        SIGN_ATTR => [
          'HEIGHT' => [TYPE_FLOAT, null],
          'INDEX' => [TYPE_INT, null]
        ]
      ],
      'EXCEL:REMOVEROW' => [
        SIGN_ATTR => [
          'INDEX' => [TYPE_INT, null]
        ]
      ],
      'EXCEL:ADDSHEET' => [
        SIGN_ATTR => [
          'INDEX' => [TYPE_INT, null]
        ],
        SIGN_CDATA => false
      ],
      'EXCEL:REMOVESHEET' => [
        SIGN_ATTR => [
          'INDEX' => [TYPE_INT, null]
        ]
      ],
      'EXCEL:ARRAY' => [
  	    SIGN_VAR => 'VAR',
        SIGN_ATTR => [
          'TYPE' => TYPE_TYPE
        ]
      ],
      'EXCEL:CREATE' => [
        SIGN_ATTR => [
          'FORMAT' => [TYPE_STRING, 'Excel2007']
        ],
        SIGN_STORE => 'VAR'
      ],
      'PDF:DOCUMENT' => [
        SIGN_ATTR => [
          'AUTHOR' => false,
          'KEYWORDS' => false,
          'LAYOUT' => [TYPE_STRING, 'SinglePage'],
          'MODE' => [TYPE_STRING, 'UseNone'],
          'SUBJECT' => false,
          'TITLE' => false,
          'UNIT' => [TYPE_STRING, 'mm'],
          'ZOOM' => [TYPE_STRING, 'default']
        ],
        SIGN_STORE => 'VAR',
        'PDF:SIGNATURE' => true
      ],
      'PDF:SIGNATURE' => [
        SIGN_ATTR => [
          'EXTRACERTS' => false,
          'PASSWORD' => false,
          'PRIVATEKEY' => false,
          'SIGNCERT' => false
        ],
        SIGN_DESC => true
      ],
      'PDF:SECTION' => [
        SIGN_ATTR => [
          'BOTTOMMARGIN' => TYPE_FLOAT,
          'FORMAT' => [TYPE_STRING, 'A4'],
          'HEIGHT' => TYPE_FLOAT,
          'LEFTMARGIN' => TYPE_FLOAT,
          'ORIENTATION' => [TYPE_STRING, 'P'],
          'RIGHTMARGIN' => TYPE_FLOAT,
          'TOPMARGIN' => TYPE_FLOAT,
          'WIDTH' => TYPE_FLOAT
        ],
        'PDF:TEMPLATES' => true,
        'PDF:HEADER' => true,
        'PDF:FOOTER' => true,
        'PDF:BODY' => true
      ],
      'PDF:TEMPLATES' => [
        SIGN_ATTR => [
          'FILENAME' => false
        ],
        SIGN_DESC => true
      ],
      'PDF:TEMPLATE' => [
        SIGN_ATTR => [
          'SOURCE' => [TYPE_INT, 1],
          'TARGET' => [TYPE_INT, '']
        ]
      ],
      'PDF:HEADER' => [
        SIGN_DESC => false
      ],
      'PDF:FOOTER' => [
        SIGN_DESC => false
      ],
      'PDF:BODY' => [
        SIGN_DESC => false
      ],
      'PDF:STYLE' => [
        SIGN_ATTR => [
          'ALIGN' => [TYPE_STRING, null],
          'BGCOLOR' => [TYPE_STRING, null],
          'BORDER' => [TYPE_STRING, null],
          'BORDERCOLOR' => [TYPE_STRING, null],
          'BORDERWIDTH' => [TYPE_FLOAT, null],
          'DIR' => [TYPE_STRING, null],
          'FONT' => [TYPE_STRING, null],
          'FONTSIZE' => [TYPE_FLOAT, null],
          'FONTSPACE' => [TYPE_INT, null],
          'FONTSTRETCH' => [TYPE_INT, null],
          'FONTSTYLE' => [TYPE_STRING, null],
          'LINEHEIGHT' => [TYPE_INT, null],
          'PADDING' => [TYPE_FLOAT, null],
          'TEXTCOLOR' => [TYPE_STRING, null],
          'VALIGN' => [TYPE_STRING, null]
        ]
      ],
      'PDF:LINEBREAK' => [
        SIGN_ATTR => [
          'OFFSET' => TYPE_FLOAT
        ]
      ],
      'PDF:PAGEBREAK' => [
        SIGN_ATTR => [
          'OFFSET' => TYPE_FLOAT
        ]
      ],
      'PDF:INLINE' => [
        SIGN_ATTR => [
          'HTML' => TYPE_BOOL,
          'LEFTMARGIN' => TYPE_FLOAT,
          'RIGHTMARGIN' => TYPE_FLOAT,
          'X' => [TYPE_FLOAT, ''],
          'Y' => [TYPE_FLOAT, '']
        ],
        SIGN_CDATA => false
      ],
      'PDF:BLOCK' => [
        SIGN_ATTR => [
          'HEIGHT' => TYPE_FLOAT,
          'HTML' => TYPE_BOOL,
          'LEFTMARGIN' => TYPE_FLOAT,
          'NOWRAP' => TYPE_BOOL,
          'RIGHTMARGIN' => TYPE_FLOAT,
          'WIDTH' => TYPE_FLOAT,
          'X' => [TYPE_FLOAT, ''],
          'Y' => [TYPE_FLOAT, '']
        ],
        SIGN_CDATA => false
      ],
      'PDF:ROW' => [
        SIGN_ATTR => [
          'LEFTMARGIN' => TYPE_FLOAT,
          'RIGHTMARGIN' => TYPE_FLOAT,
          'X' => [TYPE_FLOAT, ''],
          'Y' => [TYPE_FLOAT, '']
        ]
      ],
      'PDF:COL' => [
        SIGN_ATTR => [
          'HEIGHT' => TYPE_FLOAT,
          'HTML' => TYPE_BOOL,
          'NOWRAP' => TYPE_BOOL,
          'WIDTH' => TYPE_FLOAT
        ],
        SIGN_CDATA => false
      ],
      'PDF:IMAGE' => [
        SIGN_ATTR => [
          'DPI' => [TYPE_INT, 300],
          'FILENAME' => false,
          'HEIGHT' => TYPE_FLOAT,
          'TYPE' => false,
          'WIDTH' => TYPE_FLOAT,
          'X' => [TYPE_FLOAT, ''],
          'Y' => [TYPE_FLOAT, '']
        ]
      ],
      'PDF:BARCODE' => [
        SIGN_ATTR => [
          'HEIGHT' => TYPE_FLOAT,
          'TYPE' => [TYPE_STRING, 'C128B'],
          'WIDTH' => TYPE_FLOAT,
          'X' => [TYPE_FLOAT, ''],
          'Y' => [TYPE_FLOAT, '']
        ],
        SIGN_CDATA => false
      ],
      'PDF:GETPOS' => [
        SIGN_VAR => ['VAR_PAGE', 'VAR_SUBPAGE', 'VAR_X', 'VAR_Y']
      ],
      'PDF:SETPOS' => [
        SIGN_ATTR => [
          'PAGE' => [TYPE_INT, null],
          'X' => [TYPE_FLOAT, null],
          'Y' => [TYPE_FLOAT, null]
        ]
      ],
      'DEBUG:OUTPUT' => [
        SIGN_CDATA => false
      ],
      'DEBUG:DUMP' => [
  	    SIGN_VAR => 'VAR'
      ],
      'DEBUG:LOG' => [
        SIGN_CDATA => false
      ],
      'DEBUG:TIMER' => [
  	    SIGN_VAR => 'VAR'
      ],
      'DEBUG:EXCLUDE' => []
  	];
  }

  protected function getCondition($value1, $value2, $func) {
    switch ($func) {
      case '':
      case '=':
        break;

      case '!=':
      case '<>':
        return $value1 !== $value2;

      case '=*':
        return $value1 === $value2 || mb_strtolower($value1) === mb_strtolower($value2);

      case '!=*':
      case '<>*':
        return $value1 !== $value2 && mb_strtolower($value1) !== mb_strtolower($value2);

      case '<':
        return $value1 < $value2;

      case '<=':
        return $value1 <= $value2;

      case '>':
        return $value1 > $value2;

      case '>=':
        return $value1 >= $value2;

      case '_':
        return $value2 === '' || strpos($value1, $value2) !== false;

      case '_*':
        return $value2 === '' || mb_stripos($value1, $value2) !== false;

      case '!_':
        return $value2 !== '' && strpos($value1, $value2) === false;

      case '!_*':
        return $value2 !== '' && mb_stripos($value1, $value2) === false;

      case '^':
        return $value2 === '' || strpos($value1, $value2) === 0;

      case '^*':
        return $value2 === '' || mb_stripos($value1, $value2) === 0;

      case '!^':
        return $value2 !== '' && strpos($value1, $value2) !== 0;

      case '!^*':
        return $value2 !== '' && mb_stripos($value1, $value2) !== 0;

      case '$':
        return $value2 === '' || substr($value1, -strlen($value2)) == $value2;

      case '$*':
        return $value2 === '' || substr(mb_strtolower($value1), -strlen( $value2 = mb_strtolower($value2) )) == $value2;

      case '!$':
        return $value2 !== '' && substr($value1, -strlen($value2)) != $value2;

      case '!$*':
        return $value2 === '' || substr(mb_strtolower($value1), -strlen( $value2 = mb_strtolower($value2) )) != $value2;

      case '~':
        return preg_match($value2, $value1);

      case '!~':
        return !preg_match($value2, $value1);
    }

    return $value1 === $value2; // =
  }

  protected function getTypeIs($elem) {
    $value = ( $var = $elem['VAR'] ) ? $this -> getVar($var, true) : $this -> undefined;

    switch ($elem['TYPE']) {
      case '':
      case 'valid':
        break;

      case 'non-valid':
      case 'invalid': // FIX downward compatibility
        return $value === null || $value === false || $value === $this -> undefined;

      case 'empty':
        return $value instanceof iXmlArray ? !$value -> array : !$value || $value === $this -> undefined;

      case 'non-empty':
        return $value instanceof iXmlArray ? $value -> array : $value && $value !== $this -> undefined;

      case 'defined':
        return $value !== $this -> undefined;

      case 'undefined':
        return $value === $this -> undefined;

      case 'null':
        return $value === null;

      case 'non-null':
        return $value !== null;

      case 'true':
        return $value === true;

      case 'false':
        return $value === false;

      case 'bool':
        return $value === true || $value === false;

      case 'non-bool':
        return $value !== true && $value !== false;

      case 'int':
        return is_int($value);

      case 'non-int':
        return !is_int($value);

      case 'float':
        return is_float($value);

      case 'non-float':
        return !is_float($value);

      case 'string':
        return is_string($value);

      case 'non-string':
        return !is_string($value);

      case 'array':
        return $value instanceof iXmlArray;

      case 'non-array':
        return !$value instanceof iXmlArray;

      case 'function':
        return $value instanceof iXmlFunction;

      case 'non-function':
        return !$value instanceof iXmlFunction;

      case 'macro':
        return $value instanceof iXmlMacro;

      case 'non-macro':
        return !$value instanceof iXmlMacro;

      case 'class':
        return $value instanceof iXmlClass;

      case 'non-class':
        return !$value instanceof iXmlClass;

      case 'number':
        return is_int($value) || is_float($value);

      case 'non-number':
        return !is_int($value) && !is_float($value);

      case 'numeric':
        return is_numeric($value);

      case 'non-numeric':
        return !is_numeric($value);

      case 'scalar':
        return is_scalar($value);

      case 'non-scalar':
        return !is_scalar($value);

      case 'subroutine':
        return $value instanceof iXmlSubroutine;

      case 'non-subroutine':
        return !$value instanceof iXmlSubroutine;

      case 'complex':
        return $value instanceof iXmlComplex;

      case 'non-complex':
        return !$value instanceof iXmlComplex;

      case 'nan':
        return is_float($value) && is_nan($value);

      case 'infinite':
        return is_float($value) && is_infinite($value);
    }

    return $value !== null && $value !== false && $value !== $this -> undefined; // valid
  }

  protected function runElseIfIs($elem) {
    if (isset($elem['ELSE_IF_IS']))
      foreach ($elem['ELSE_IF_IS'] as $child) {
        isset($child[SIGN_MAP]) AND $child = $this -> map($child);

        if ($child[SIGN_CONST] ? $this -> getCondition($child['VALUE1'], $child['VALUE2'], $child['FUNC']) : $this -> getTypeIs($child))
          return isset($child[SIGN_CHILD]) ? $this -> run($child[SIGN_CHILD]) : null;
      }

    if (isset($elem['ELSE']))
      return $this -> run($elem['ELSE'][0][SIGN_CHILD]);
  }

  protected function _IF($elem) {
    return $this -> getCondition($elem['VALUE1'], $elem['VALUE2'], $elem['FUNC'])
         ? (isset($elem[SIGN_CHILD]) ? $this -> run($elem[SIGN_CHILD]) : null) : $this -> runElseIfIs($elem);
  }

  protected function _IS($elem) {
    return $this -> getTypeIs($elem)
         ? (isset($elem[SIGN_CHILD]) ? $this -> run($elem[SIGN_CHILD]) : null) : $this -> runElseIfIs($elem);
  }

  protected function _SWITCH($elem) {
    if (isset($elem['CASE'])) {
      $value = $elem['VALUE'];

      $eval = true;

	    foreach ($elem['CASE'] as $child) {
	      if ($eval) {
	        isset($child[SIGN_MAP]) AND $child = $this -> map($child);

	        if ($value !== $child['VALUE'])
	          continue;
	      }

        if (!isset($child[SIGN_CHILD]))
          return;

        try {
          return $this -> run($child[SIGN_CHILD]);
        } catch (iXmlBreak $e) {
          if (!$e -> checkLeap() instanceof iXmlNext)
            return;

          $eval = false;
        }
	    }
    }

    if (isset($elem['DEFAULT']))
      try {
        return $this -> run($elem['DEFAULT'][0][SIGN_CHILD]);
      } catch (iXmlBreak $e) {
        $e -> checkLeap();
      }
  }

  protected function _WHILE($elem) {
    if (isset($elem[SIGN_CHILD])) {
      $attr_t = isset($elem[SIGN_ATTR_T]) ? $elem[SIGN_ATTR_T] : [];
      $children = isset($elem[SIGN_CHILD]) ? $elem[SIGN_CHILD] : null;

      $value1 =& $elem['VALUE1'];
      $value2 =& $elem['VALUE2'];
      $func =& $elem['FUNC'];

      $else = true;

      while ($this -> getCondition($value1, $value2, $func)) {
        $else = false;

        if ($children)
          try {
            $this -> run($children);
          } catch (iXmlBreak $e) {
            if (!$e -> checkLeap() instanceof iXmlNext)
              return;
          }

        foreach ($attr_t as $name => $attr)
          $elem[$name] = $this -> processTokens($attr[0], $attr[1]);
      }

      $else && isset($elem['ELSE']) && $this -> run($elem['ELSE'][0][SIGN_CHILD]);
    }
  }

  protected function _FOR($elem) {
	  if (( $step = $elem['STEP'] ) > 0) {
	    $var = $elem['VAR'] AND $from =& $this -> getVarRef($var);

	    $from = $elem['FROM'];
	    $to = $elem['TO'];

      if (isset($elem[SIGN_CHILD])) {
        $children = $elem[SIGN_CHILD];

        if ($from > $to) {
          $to = $from - $to;
          $offset = -$step;
        } else {
          $to -= $from;
          $offset = $step;
        }

        while (true) {
          try {
            $this -> run($children);
          } catch (iXmlBreak $e) {
            if (!$e -> checkLeap() instanceof iXmlNext)
              return;
          }

          if (( $to -= $step ) < 0)
            break;

          $from += $offset;
        }
      } else if ($var)
        $from = $to + ($from - $to) % $step;
	  }
  }

  protected function _FOREACH($elem) {
    if ( $array = $this -> getVarArray($elem['VAR']) ) {
    	$var = $elem['VAR_KEY'] AND $key =& $this -> getVarRef($var);
      $var = $elem['VAR_VALUE'] AND $value =& $this -> getVarRef($var);

      if (isset($elem[SIGN_CHILD])) {
        $children = $elem[SIGN_CHILD];

        foreach ($array as $key => $value)
          try {
            $this -> run($children);
          } catch (iXmlBreak $e) {
            if (!$e -> checkLeap() instanceof iXmlNext)
              return;
          }
      } else {
        $value = end($array);
        $key = key($array);
      }
    } else if (isset($elem['ELSE']))
	    $this -> run($elem['ELSE'][0][SIGN_CHILD]);
  }

  protected function _EXIT($elem) {
    echo $elem[SIGN_CDATA];

    throw new iXmlExit;
  }

  protected function _RETURN() {
    throw new iXmlReturn;
  }

  protected function _BREAK($elem) {
    throw new iXmlBreak($elem['LEAP']);
  }

  protected function _NEXT($elem) {
    throw new iXmlNext($elem['LEAP']);
  }

  protected function runParams($elem) {
    return isset($elem[SIGN_CHILD]) && ( $params = $this -> runStruct('PARAMS', $elem[SIGN_CHILD]) )
         ? $params : $this -> getVarArray($elem['VAR_PARAMS']);
  }

  protected function _INCLUDE($elem) {
    $params = $this -> runParams($elem);

    $filename = $this -> getPath($elem['FILENAME']);

    if ($elem['ONCE']) {
      if ( $included =& $this -> includeonce[$filename] )
        return;

      $included = true;
    }

    try {
      $root = $this -> parse(file_get_contents($filename));
    } catch (iXmlParserException $e) {
      throw new iXmlException("Unable to parse '$filename' -> ".$e -> getMessage());
    }

    if (isset($root[SIGN_CHILD])) {
      $vars = $params;
      $vars['return'] =& $return;
      $params AND $vars['arguments'] = new iXmlArray($params);

      $this -> runSub($root[SIGN_CHILD], $vars);

      return $return;
    }
  }

  protected function _PARAM($elem) {
    $var = $elem['VAR'];

    $this -> getStruct('PARAMS')[( $name = $elem['NAME'] ) === '' && $var ? (isset($var[1]) ? end($var[1]) : $var[0]) : $name] = $var ? $this -> getVar($var) : $elem[SIGN_CDATA];
  }

  protected function _FUNCTION($elem) {
    $vars = [];

    if (isset($elem['USE']))
      foreach ($elem['USE'] as $child) {
        isset($child[SIGN_MAP]) AND $child = $this -> map($child);

        $var = $child['VAR'] AND $vars[( $name = $child['NAME'] ) === '' ? (isset($var[1]) ? end($var[1]) : $var[0]) : $name] =& $this -> getVarRef($var);
      }

    $var = $elem['VAR'] AND $this -> setVar($var, new iXmlFunctionClosure(isset($elem[SIGN_CHILD]) ? $elem[SIGN_CHILD] : [], $vars));
  }

  protected function _CALL($elem) {
    $params = $this -> runParams($elem);

    if ( $func = $elem['FUNC'] ) {
      $vars = $this -> local;

      if (!isset($vars[ $name = $func[0] ])) {
        $vars = $this -> global;

        if (!isset($vars[$name]) || array_key_exists($name, $this -> local))
          goto undefined;
      }

      if (isset($func[1]))
        foreach ($func[1] as $key) {
          if (!( $that = $vars[$name] ) instanceof iXmlArray)
            goto undefined;

          $vars = $that -> array;

          if (!isset($vars[ $name = $key ]))
            goto undefined;
        }
      else
        $that = null;

      if (( $function = $vars[$name] ) instanceof iXmlFunction) {
        if ($function instanceof iXmlBind)
          return $function -> call($params);

        $vars = $function -> vars + $params;

        if ( $var = $elem['VAR_THIS'] )
          $vars['this'] = $this -> getVar($var);
        else if ($that !== null)
          $vars['this'] = $that;

        $vars['return'] =& $return;
        $params AND $vars['arguments'] = new iXmlArray($params);

        $this -> runSub($function -> elems, $vars);

        return $return;
      }
    }

    undefined:
    throw new iXmlException("Undefined or invalid function '".$this -> getVarDebug($func)."'");
  }

  protected function _MACRO($elem) {
  	$var = $elem['VAR'] AND $this -> setVar($var, new iXmlMacro(isset($elem[SIGN_CHILD]) ? $elem[SIGN_CHILD] : []));
  }

  protected function _EXPAND($elem) {
    if ( $macro = $elem['MACRO'] AND ( $realmacro = $this -> getVar($macro) ) instanceof iXmlMacro)
      return $this -> run($realmacro -> elems);

    throw new iXmlException("Undefined or invalid macro '".$this -> getVarDebug($macro)."'");
  }

  protected function _CLASS($elem) {
    if ( $var = $elem['VAR'] ) {
      $extends = [];
      $parents = new \SplObjectStorage;
      $constructors = [];
      $prototype = [];

      if (isset($elem['EXTENDS']))
        foreach ($elem['EXTENDS'] as $child) {
          isset($child[SIGN_MAP]) AND $child = $this -> map($child);

          if ($parents -> contains( $class = $this -> getVarClass($child['CLASS']) ))
            throw new iXmlException("Class '".$this -> getVarDebug($var)."' cannot extend '".$this -> getVarDebug($child['CLASS'])."' repeatedly");

          $extends[] = $class;
          $parents -> attach($class);
          $parents -> addAll($class -> parents);
          $constructors = array_merge($constructors, $class -> constructors);
          $prototype = $class -> prototype + $prototype;
        }

      if (isset($elem['PROPERTY']))
        foreach ($elem['PROPERTY'] as $child) {
          isset($child[SIGN_MAP]) AND $child = $this -> map($child);

          $property_var = $child['VAR'];

          $prototype[( $name = $child['NAME'] ) === '' && $property_var ? (isset($property_var[1]) ? end($property_var[1]) : $property_var[0]) : $name] = $property_var ? $this -> getVar($property_var) : $child[SIGN_CDATA];
        }

      isset($elem['CONSTRUCTOR']) AND $constructors[] = $elem['CONSTRUCTOR'][0][SIGN_CHILD];

      if (isset($elem['METHOD']))
        foreach ($elem['METHOD'] as $child) {
          isset($child[SIGN_MAP]) AND $child = $this -> map($child);

          $prototype[$child['NAME']] = new iXmlFunctionSimple(isset($child[SIGN_CHILD]) ? $child[SIGN_CHILD] : []);
        }

      $this -> setVar($var, new iXmlClass($extends, $parents, $constructors, $prototype));
    }
  }

  protected function _NEW($elem) {
    $params = $this -> runParams($elem);

    $that = new iXmlObject( $class = $this -> getVarClass($elem['CLASS']) );

    if ($class -> constructors) {
      $vars = $params;
      $vars['this'] = $that;
      $params AND $vars['arguments'] = new iXmlArray($params);

      foreach ($class -> constructors as $constructor)
        $this -> runSub($constructor, $vars);
    }

    $var = $elem['VAR'] AND $this -> setVar($var, $that);
  }

  protected function _TRY($elem) {
    $e = null;

    try {
      if (isset($elem[SIGN_CHILD]))
        if (isset($elem['CATCH']))
    	    try {
    	      $this -> run($elem[SIGN_CHILD]);
          } catch (iXmlExit $e) {
            goto finish;
    	    } catch (\Exception $e) {
  	    		$child = $elem['CATCH'][0];

  	    	  isset($child[SIGN_MAP]) AND $child = $this -> map($child);

  	    		$var = $child['VAR'] AND $this -> setVar($var, $e -> getMessage());

  	        isset($child[SIGN_CHILD]) && $this -> run($child[SIGN_CHILD]);

    	      $e = null;
    	      goto finish;
    	    }
    	  else
    	    $this -> run($elem[SIGN_CHILD]);

      isset($elem['ELSE']) && $this -> run($elem['ELSE'][0][SIGN_CHILD]);
    } catch (\Exception $e) {}

    finish:

    isset($elem['FINALLY']) && $this -> run($elem['FINALLY'][0][SIGN_CHILD]);

    if ($e)
      throw $e;
  }

  protected function _ERROR($elem) {
    throw new iXmlUserException($elem[SIGN_CDATA]);
  }

  protected function _GLOBAL($elem) {
    if ( $var = $elem['VAR'] AND !array_key_exists( $name = $var[0] , $this -> global)) {
      $this -> global[$name] =& $this -> local[$name];
      unset($this -> local[$name]);
    }
  }

  protected function _TYPEOF($elem) {
    if ( $var = $elem['VAR'] AND ( $value = $this -> getVar($var, true) ) !== $this -> undefined ) {
		  if ($value === null)
		    return 'null';

	    if ($value === true || $value === false)
	      return 'bool';

		  if ($value instanceof iXmlComplex)
		    return "$value"; // array, function, macro, class

	    if (is_string($value))
	      return 'string';

	    if (is_float($value))
	      return 'float';

	    if (is_int($value))
	      return 'int';
		}

    return 'undefined';
  }

  protected function _INSTANCEOF($elem) {
    return $var = $elem['VAR'] AND
           ( $array = $this -> getVar($var) ) instanceof iXmlObject AND
           ( $array_class = $array -> class ) === ( $class = $this -> getVarClass($elem['CLASS']) ) || $array_class -> parents -> contains($class);
  }

  protected function _CLASSINFO($elem) {
    if ( $var = $elem['VAR'] ) {
      $class = $this -> getVarClass($elem['CLASS']);

      $parents = [];

      foreach ($class -> parents as $parent) // Iterate because of SplObjectStorage
        $parents[] = $parent;

      $constructors = $class -> constructors;

      foreach ($constructors as &$constructor)
        $constructor = new iXmlFunctionSimple($constructor);

      $this -> setVarArray($var, [
        'class' => $class,
        'extends' => new iXmlArray($class -> extends),
        'parents' => new iXmlArray($parents),
        'constructors' => new iXmlArray($constructors),
        'prototype' => new iXmlArray($class -> prototype)
      ]);
    }
  }

  protected function _EQUALS($elem) {
    return $var1 = $elem['VAR1'] AND $var2 = $elem['VAR2'] AND
           ( $value = $this -> getVar($var1, true) ) !== $this -> undefined AND
           $value === $this -> getVar($var2, true);
  }

  protected function _SET($elem) {
    $value = $elem[SIGN_CDATA];

    if ( $var = $elem['VAR'] ) {
  	  ( $key = $elem['KEY'] ) === null OR $var[1][] = $key;

  	  $this -> setVar($var, $value);
    }

    return $value;
  }

  protected function _UNSET($elem) {
    if ( $var = $elem['VAR'] ) {
  	  ( $key = $elem['KEY'] ) === null OR $var[1][] = $key;

      $this -> unsetVar($var);
    }
  }

  protected function _NULL($elem) {
  	if ( $var = $elem['VAR'] ) {
  	  ( $key = $elem['KEY'] ) === null OR $var[1][] = $key;

  	  $this -> setVar($var, null);
  	}
  }

  protected function _TRUE($elem) {
  	if ( $var = $elem['VAR'] ) {
  	  ( $key = $elem['KEY'] ) === null OR $var[1][] = $key;

  	  $this -> setVar($var, true);
  	}
  }

  protected function _FALSE($elem) {
  	if ( $var = $elem['VAR'] ) {
  	  ( $key = $elem['KEY'] ) === null OR $var[1][] = $key;

  	  $this -> setVar($var, false);
  	}
  }

  protected function _ASSIGN($elem) {
    if ( $var = $elem['VAR'] ) {
      ( $key = $elem['KEY'] ) === null OR $var[1][] = $key;

      if ( $var_source = $elem['VAR_SOURCE'] ) {
        ( $key = $elem['KEY_SOURCE'] ) === null OR $var_source[1][] = $key;

        $value = $this -> getVar($var_source);
      } else
        $value = null;

      $this -> setVar($var, $value);
    }
  }

  protected function _CLONE($elem) {
    $var_result = $elem['VAR_RESULT'] ?: $elem['VAR'] AND
    $this -> setVar($var_result, ( $var = $elem['VAR'] ) ? (( $value = $this -> getVar($var) ) instanceof iXmlComplex ? clone $value : $value) : null);
  }

  protected function _SWAP($elem) {
    if ( $var = $elem['VAR1'] )
      $ref1 =& $this -> getVarRef($var);
    else
      $ref1 = null;

    if ( $var = $elem['VAR2'] )
      $ref2 =& $this -> getVarRef($var);
    else
      $ref2 = null;

    $temp = $ref1;
    $ref1 = $ref2;
    $ref2 = $temp;
  }

  protected function _CAST($elem) {
    if ( $var = $elem['VAR'] )
      $ref =& $this -> getVarRef($var);
    else
      $ref = null;

    switch ($elem['TYPE']) {
      case 'bool':
        return $ref = (bool) $ref;

      case 'int':
        return $ref = is_numeric($ref) ? (int) +$ref : 0;

      case 'float':
        return $ref = is_numeric($ref) ? (float) +$ref : 0.0;
    }

    return $ref = "$ref"; // string
  }

  protected function _EVAL($elem) {
    switch ($elem['TYPE']) {
      case 'bool':
        $type = TYPE_BOOL;
        break;

      case 'int':
        $type = TYPE_INT;
        break;

      case 'float':
        $type = TYPE_FLOAT;
        break;

      default: // string
        $type = TYPE_STRING;
    }

    $type = [$type, null];

    return is_array( $value = $this -> prepareTokens($elem[SIGN_CDATA]) )
         ? $this -> processTokens($value, $type) : $this -> processValue($value, $type);
  }

  protected function _SERIALIZE($elem) {
    return $this -> getVarSerialized($elem['VAR']);
  }

  protected function _UNSERIALIZE($elem) {
    return $this -> unserialize($elem[SIGN_CDATA]);
  }

  protected function _CONCAT($elem) {
    $value = $elem[SIGN_CDATA];

    if ( $var = $elem['VAR'] ) {
      $ref =& $this -> getVarRef($var);
      return $ref .= $value;
    }

    return $value;
  }

  protected function _TOUPPER($elem) {
    $value = $elem[SIGN_CDATA];

    switch ($elem['TYPE']) {
      case 'words':
        return mb_convert_case($value, MB_CASE_TITLE);

      case 'first':
        return mb_strtoupper(mb_substr($value, 0, 1)).mb_substr($value, 1);
    }

    return mb_strtoupper($value); // chars
  }

  protected function _TRIM($elem) {
    $value = $elem[SIGN_CDATA];

    switch ($elem['TYPE']) {
      case 'left':
        return ltrim($value);

      case 'right':
        return rtrim($value);
    }

    return trim($value); // both
  }

  protected function _POS($elem) {
    if (( $value = $elem['VALUE'] ) !== '') {
      $subject = $elem[SIGN_CDATA];

      switch ( $type = $elem['TYPE'] ) {
        case 'bytes-first':
        case 'bytes-last':
          $length = strlen($subject);
          break;

        default: // chars-first, chars-last
          $length = mb_strlen($subject);
      }

      if (( $offset = $elem['OFFSET'] ) < $length) {
        $offset < 0 AND ( $offset += $length ) < 0 AND $offset = 0;

        switch ($type) {
          case 'bytes-first':
            $pos = strpos($subject, $value, $offset);
            break;

          case 'bytes-last':
            $pos = strrpos($subject, $value, $offset);
            break;

          case 'chars-last':
            $pos = mb_strrpos($subject, $value, $offset);
            break;

          default: // chars-first
            $pos = mb_strpos($subject, $value, $offset);
        }

        if ($pos !== false)
          return $pos;
      }
    }
  }

  protected function _SUBSTR($elem) {
    $value = $elem[SIGN_CDATA];
    $length = $elem['LENGTH'];
    $offset = $elem['OFFSET'];

    return $elem['TYPE'] === 'bytes'
         ? ''.($length === null ? substr($value, $offset) : substr($value, $offset, $length)) // bytes
         : ($length === null ? mb_substr($value, $offset) : mb_substr($value, $offset, $length)); // chars
  }

  protected function _PAD($elem) {
    $value = $elem[SIGN_CDATA];

    if (( $padding = $elem['PADDING'] ) === '')
      return $value;

    $neg = ( $length = $elem['LENGTH'] ) < 0;

    if ($elem['TYPE'] === 'bytes')
      return str_pad($value, $neg ? -$length : $length, $padding, $neg ? STR_PAD_LEFT : STR_PAD_RIGHT);

    // chars

    if (( $count = ($neg ? -$length : $length) - mb_strlen($value) ) <= 0)
      return $value;

    $padding = mb_substr(str_repeat($padding, $count / mb_strlen($padding) + 1), 0, $count);
    return $neg ? $padding.$value : $value.$padding;
  }

  protected function _MATCH($elem) {
    $result = preg_match_all($elem['PATTERN'], $elem[SIGN_CDATA], $matches, PREG_SET_ORDER, $elem['OFFSET']);

    if ( $var = $elem['VAR_MATCHES'] ) {
      foreach ($matches as &$match)
        $match = new iXmlArray($match);

      $this -> setVarArray($var, $matches);
    }

    return $result;
  }

  protected function _REPLACE($elem) {
    $subject = $elem[SIGN_CDATA];
    $replacement = $elem['REPLACEMENT'];

    $result = ( $pattern = $elem['PATTERN'] ) === null
            ? str_replace($elem['VALUE'], $replacement, $subject, $count)
            : preg_replace($pattern, $replacement, $subject, ( $limit = $elem['LIMIT'] ) > 0 ? $limit : -1, $count);

    $var = $elem['VAR_COUNT'] AND $this -> setVar($var, $count);
    return $result;
  }

  protected function _SPLIT($elem) {
    if ( $var = $elem['VAR'] ) {
      $subject = $elem[SIGN_CDATA];
      $limit = $elem['LIMIT'];

      if (( $pattern = $elem['PATTERN'] ) === null) {
        $delimiter = $elem['DELIMITER'];

        $array = $limit > 0 ? explode($delimiter, $subject, $limit) : explode($delimiter, $subject);
      } else
        $array = preg_split($pattern, $subject, $limit > 0 ? $limit : -1);

      $this -> setVarArray($var, $array);
    }
  }

  protected function _CONVERT($elem) {
    return convert($elem[SIGN_CDATA], strtoupper($elem['TO']), strtoupper($elem['FROM']));
  }

  protected function _OUTPUT($elem) {
    echo $elem[SIGN_CDATA];
  }

  protected function _MATH_SIGN($elem) {
    return ( $value = $elem[SIGN_CDATA] ) > 0 ? 1 : ($value < 0 ? -1 : 0);
  }

  protected function _MATH_INC($elem) {
    $value = $elem[SIGN_CDATA];

    return ( $var = $elem['VAR'] )
         ? ( $ref = is_numeric( $ref =& $this -> getVarRef($var) ) ? (int) $ref + $value : $value ) : $value;
  }

  protected function _MATH_DEC($elem) {
    $value = -$elem[SIGN_CDATA];

    return ( $var = $elem['VAR'] )
         ? ( $ref = is_numeric( $ref =& $this -> getVarRef($var) ) ? (int) $ref + $value : $value ) : $value;
  }

  protected function _MATH_MOD($elem) {
    return fmod($elem['X'], $elem['Y']);
  }

  protected function _MATH_LOG($elem) {
    return log($elem[SIGN_CDATA], $elem['BASE']);
  }

  protected function _MATH_POW($elem) {
    return pow($elem[SIGN_CDATA], $elem['EXPONENT']);
  }

  protected function _MATH_HYPOT($elem) {
    return hypot($elem['X'], $elem['Y']);
  }

  protected function _MATH_PI() {
    return M_PI;
  }

  protected function _MATH_ATAN2($elem) {
    return atan2($elem['Y'], $elem['X']);
  }

  protected function _MATH_ROUND($elem) {
    return round($elem[SIGN_CDATA], $elem['PRECISION']);
  }

  protected function _MATH_RAND($elem) {
    return mt_rand($elem['MIN'], ( $max = $elem['MAX'] ) === null ? mt_getrandmax() : $max);
  }

  protected function _MATH_CONVERT($elem) {
  	$value = $elem[SIGN_CDATA];

    switch ( $from = $elem['FROM'] ) {
      case 'bin':
        $value = bindec($value);
        break;

      case 'oct':
        $value = octdec($value);
        break;

      case 'hex':
        $value = hexdec($value);
        break;

      default: // dec, deg, rad
        $value = is_numeric($value) ? +$value : 0;
    }

    switch ( $to = $elem['TO'] ) {
      case 'bin':
        return decbin($value);

      case 'oct':
        return decoct($value);

      case 'hex':
        return dechex($value);

      case 'deg':
        if ($from === $to)
          break;

        return rad2deg($value);

      case 'rad':
        if ($from === $to)
          break;

        return deg2rad($value);
    }

    return $value; // dec
  }

  protected function _MATH_FORMAT($elem) {
    return number_format($elem[SIGN_CDATA], $elem['COUNTDEC'], $elem['DECPOINT'], $elem['SEPARATOR']);
  }

  protected function _DATE_NOW($elem) {
    return $elem['MICRO'] ? microtime(true) : time();
  }

  protected function runTimeZone($elem) {
    if ( $hastz = ( $timezone = $elem['TIMEZONE'] ) !== '' ) {
      $original = date_default_timezone_get();
      date_default_timezone_set($timezone);
    }

    $e = null;

    try {
      $result = $this -> $elem[SIGN_FUNC]($elem, false);
    } catch (\Exception $e) {}

    $hastz && date_default_timezone_set($original);

    if ($e)
      throw $e;

    return $result;
  }

  protected function _DATE_INFO($elem, $timezone = true) {
    if ($timezone)
      $this -> runTimeZone($elem);
    else if ( $var = $elem['VAR'] ) {
      $info = ( $timestamp = $elem[SIGN_CDATA] ) === null ? getdate() : getdate($timestamp);

      $this -> setVarArray($var, [
        'day' => $info['mday'],
        'month' => $info['mon'],
        'year' => $info['year'],
        'hour' => $info['hours'],
        'minute' => $info['minutes'],
        'second' => $info['seconds'],
        'wday' => $info['wday'],
        'yday' => $info['yday']
      ]);
    }
  }

  protected function _DATE_CREATE($elem, $timezone = true) {
    if ($timezone)
      return $this -> runTimeZone($elem);

    if (( $date = mktime($elem['HOUR'], $elem['MINUTE'], $elem['SECOND'], $elem['MONTH'], $elem['DAY'], $elem['YEAR']) ) !== false)
      return $date;
  }

  protected function _DATE_PARSE($elem, $timezone = true) {
    return $timezone ? $this -> runTimeZone($elem) : parseTime($elem[SIGN_CDATA]);
  }

  protected function _DATE_FORMAT($elem, $timezone = true) {
    if ($timezone)
      return $this -> runTimeZone($elem);

    $format = $elem['FORMAT'];
    return ( $timestamp = $elem[SIGN_CDATA] ) === null ? date($format) : date($format, $timestamp);
  }

  protected function _ARRAY($elem) {
  	$array = new iXmlArray(isset($elem[SIGN_CHILD]) ? $this -> runStruct('ARRAY', $elem[SIGN_CHILD]) : []);

    if (isset($this -> structs['ARRAY'])) {
      $parent =& $this -> structs['ARRAY'];

      if (( $key = $elem['KEY'] ) === null)
        $parent[] = $array;
      else
        $parent[$key] = $array;
    }

  	$var = $elem['VAR'] AND $this -> setVar($var, $array);
  }

  protected function _ITEM($elem) {
    $array =& $this -> getStruct('ARRAY');

    $value = ( $var = $elem['VAR'] ) ? $this -> getVar($var) : $elem[SIGN_CDATA];

    if (( $key = $elem['KEY'] ) === null)
      $array[] = $value;
    else
      $array[$key] = $value;
  }

  protected function _ARRAY_RANGE($elem) {
    $var = $elem['VAR'] AND $this -> setVarArray($var, ( $step = $elem['STEP'] ) > 0 ? ($step > abs(( $from = $elem['FROM'] ) - ( $to = $elem['TO'] )) ? [$from] : range($from, $to, $step)) : []);
  }

  protected function _ARRAY_ASSOC($elem) {
    if ( $var = $elem['VAR'] ) {
      $keys = $this -> getVarArray($elem['VAR_KEYS']);
      $values = $this -> getVarArray($elem['VAR_VALUES']);

		  if (( $count_keys = count($keys) ) > ( $count_values = count($values) ))
		    $keys = array_slice($keys, 0, $count_values);
		  else if ($count_keys < $count_values)
		    $values = array_slice($values, 0, $count_keys);

      $this -> setVarArray($var, array_combine($keys, $values));
    }
  }

  protected function _ARRAY_POPULATE($elem) {
    $prefix = $elem['PREFIX'];

    $local =& $this -> local;

    foreach ($this -> getVarArray($elem['VAR']) as $key => $value)
      $local[$prefix.$key] = $value;
  }

  protected function _ARRAY_LENGTH($elem) {
    return ( $var = $elem['VAR'] ) ? count($this -> getVarArray($var)) : 0;
  }

  protected function _ARRAY_KEYEXISTS($elem) {
    return array_key_exists($elem[SIGN_CDATA], $this -> getVarArray($elem['VAR']));
  }

  protected function _ARRAY_VALUEEXISTS($elem) {
    if ( $array = $this -> getVarArray($elem['VAR']) ) {
      $value = $elem[SIGN_CDATA];

      foreach ($array as $array_value)
        if ($value === "$array_value")
          return true;
    }

    return false;
  }

  protected function _ARRAY_POS($elem) {
    if (( $offset = $elem['OFFSET'] ) < ( $length = count( $array = $this -> getVarArray($elem['VAR']) ) )) {
      $value = $elem[SIGN_CDATA];
      $first = $elem['TYPE'] !== 'last';

      $offset < 0 AND $offset += $length;

      $index = 0;
      $result = null;

      foreach ($array as $key => $array_value)
        if ( ++$index > $offset && $value === "$array_value") {
          if ($first)
            return $key;

          $result = $key;
        }

      return $result;
    }
  }

  protected function _ARRAY_FIRST($elem) {
    if ( $array = $this -> getVarArray($elem['VAR']) )
      foreach ($array as $key => $value)
        break;
    else
      $key = $value = null;

    $var = $elem['VAR_KEY'] AND $this -> setVar($var, $key);
    return $value;
  }

  protected function _ARRAY_LAST($elem) {
    if ( $array = $this -> getVarArray($elem['VAR']) ) {
      $value = end($array);
      $key = key($array);
    } else
      $key = $value = null;

    $var = $elem['VAR_KEY'] AND $this -> setVar($var, $key);
    return $value;
  }

  protected function _ARRAY_RAND($elem) {
    if ( $array = $this -> getVarArray($elem['VAR']) )
      $value = $array[ $key = array_rand($array) ];
    else
      $key = $value = null;

    $var = $elem['VAR_KEY'] AND $this -> setVar($var, $key);
    return $value;
  }

  protected function _ARRAY_PUSH($elem) {
    if ( $var = $elem['VAR'] ) {
      $var[1][] = null;

      $this -> setVar($var, $elem[SIGN_CDATA]);
    }
  }

  protected function _ARRAY_POP($elem) {
    if ( $var = $elem['VAR'] AND $array =& $this -> getVarRefArray($var) )
      return array_pop($array);
  }

  protected function _ARRAY_SHIFT($elem) {
    if ( $var = $elem['VAR'] AND $array =& $this -> getVarRefArray($var) )
      return array_shift($array);
  }

  protected function _ARRAY_UNSHIFT($elem) {
    if ( $var = $elem['VAR'] ) {
      $array =& $this -> getVarRefArray($var);
      array_unshift($array, $elem[SIGN_CDATA]);
    }
  }

  protected function &getArrayResult($elem) {
    $var = $elem['VAR'];

    if ( $var_result = $elem['VAR_RESULT'] )
      $array = $this -> getVarArray($var);
    else if ($var)
      $array =& $this -> getVarRefArray($var);
    else
      $array = null;

    return $array;
  }

  protected function _ARRAY_CONCAT($elem) {
    if (( $array =& $this -> getArrayResult($elem) ) !== null) {
      $array = array_merge($array, array_values($this -> getVarArray($elem['VAR_TAIL'])));

      $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $array);
    }
  }

  protected function _ARRAY_SLICE($elem) {
    if (( $array =& $this -> getArrayResult($elem) ) !== null) {
      $count = count($array);
      ( $length = $elem['LENGTH'] ) === null AND $length = $count;
      ( $offset = $elem['OFFSET'] ) !== 0 || $length !== $count AND $array = array_slice($array, $offset, $length, true);

      $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $array);
    }
  }

  protected function _ARRAY_EXTRACT($elem) {
    if ( $var = $elem['VAR'] ) {
      $array = array_values( $array =& $this -> getVarRefArray($var) );
      $extract = array_splice($array, $elem['OFFSET'], ( $length = $elem['LENGTH'] ) === null ? count($array) : $length, $this -> getVarArray($elem['VAR_REPLACEMENT']));
    } else
      $extract = [];

    $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $extract);
  }

  protected function _ARRAY_PAD($elem) {
    if (( $array =& $this -> getArrayResult($elem) ) !== null) {
      $array = array_pad(array_values($array), $elem['LENGTH'], $elem['PADDING']);

      $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $array);
    }
  }

  protected function _ARRAY_REVERSE($elem) {
    if (( $array =& $this -> getArrayResult($elem) ) !== null) {
      $array = array_reverse($array, true);

      $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $array);
    }
  }

  protected function _ARRAY_FLIP($elem) {
    if (( $array =& $this -> getArrayResult($elem) ) !== null) {
      $flip = [];

      foreach ($array as $key => $value)
        $flip["$value"] = $key;

      $array = $flip;

      $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $array);
    }
  }

  protected function _ARRAY_UNIQUE($elem) {
    if (( $array =& $this -> getArrayResult($elem) ) !== null) {
      $key = $elem['KEY'];

      $null = $true = $false = false;
      $complex = new \SplObjectStorage;
      $scalar = [];

      foreach ($array as $key_array => $value) {
        if ($key !== null) {
          if (!$value instanceof iXmlArray)
            goto discard;

          $value_array = $value -> array;

          if (!isset($value_array[$key]) && !array_key_exists($key, $value_array))
            goto discard;

          $value = $value_array[$key];
        }

        if ($value === null) {
          if (!$null) {
            $null = true;
            continue;
          }
        } else if ($value === true) {
          if (!$true) {
            $true = true;
            continue;
          }
        } else if ($value === false) {
          if (!$false) {
            $false = true;
            continue;
          }
        } else if ($value instanceof iXmlComplex) {
          if (!$complex -> contains($value)) {
            $complex -> attach($value);
            continue;
          }
        } else if (! $ref =& $scalar["$value"] ) {
          $ref = true;
          continue;
        }

        discard:
        unset($array[$key_array]);
      }

      $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $array);
    }
  }

  protected function _ARRAY_MERGE($elem) {
    if (( $array =& $this -> getArrayResult($elem) ) !== null) {
      $array = array_merge($array, $this -> getVarArray($elem['VAR_SET']));

      $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $array);
    }
  }

  protected function getArrayHashed($array) {
    $null = $true = $false = false;
    $complex = new \SplObjectStorage;
    $scalar = [];

    foreach ($array as $value)
      if ($value === null)
        $null = true;
      else if ($value === true)
        $true = true;
      else if ($value === false)
        $false = true;
      else if ($value instanceof iXmlComplex)
        $complex -> attach($value);
      else
        $scalar["$value"] = true;

    return [$null, $true, $false, $complex, $scalar];
  }

  protected function getArrayFiltered($array, $filters, $diff = true) {
    list($null, $true, $false, $complex, $scalar) = $filters;

    foreach ($array as $key => $value) {
      $exists = $value === null ? $null : ($value === true ? $true : ($value === false ? $false : (
                $value instanceof iXmlComplex ? $complex -> contains($value) : isset($scalar["$value"]))));

      if ($diff ? $exists : !$exists)
        unset($array[$key]);
    }

    return $array;
  }

  protected function _ARRAY_COMPLEMENT($elem) {
    if (( $array =& $this -> getArrayResult($elem) ) !== null) {
      $set = $this -> getVarArray($elem['VAR_SET']);
      $array = $elem['TYPE'] === 'keys' ? array_diff_key($array, $set) : $this -> getArrayFiltered($array, $this -> getArrayHashed($set));

      $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $array);
    }
  }

  protected function _ARRAY_DIFF($elem) {
    if (( $array =& $this -> getArrayResult($elem) ) !== null) {
      $set = $this -> getVarArray($elem['VAR_SET']);

      if ($elem['TYPE'] === 'keys')
        $array = array_diff_key($array, $set) + array_diff_key($set, $array);
      else {
        list($null, $true, $false, $complex, $scalar) = $this -> getArrayHashed($array);
        list($null_set, $true_set, $false_set, $complex_set, $scalar_set) = $this -> getArrayHashed($set);

        $complex -> removeAllExcept($complex_set);
        $filters = [$null && $null_set, $true && $true_set, $false && $false_set, $complex, array_intersect_key($scalar, $scalar_set)];

        $array = array_merge($this -> getArrayFiltered($array, $filters), $this -> getArrayFiltered($array, $filters));
      }

      $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $array);
    }
  }

  protected function _ARRAY_INTERSECT($elem) {
    if (( $array =& $this -> getArrayResult($elem) ) !== null) {
      $set = $this -> getVarArray($elem['VAR_SET']);
      $array = $elem['TYPE'] === 'keys' ? array_intersect_key($array, $set) : $this -> getArrayFiltered($array, $this -> getArrayHashed($set), false);

      $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $array);
    }
  }

  protected function _ARRAY_UNION($elem) {
    if (( $array =& $this -> getArrayResult($elem) ) !== null) {
      $set = $this -> getVarArray($elem['VAR_SET']);
      $array = $elem['TYPE'] === 'keys' ? $array + $set : array_merge($array, $this -> getArrayFiltered($set, $this -> getArrayHashed($array)));

      $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $array);
    }
  }

  protected function _ARRAY_REPLACE($elem) {
    if (( $array =& $this -> getArrayResult($elem) ) !== null) {
      $array = array_replace($array, array_intersect_key($this -> getVarArray($elem['VAR_REPLACEMENT']), $array));

      $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $array);
    }
  }

  protected function _ARRAY_FILTER($elem) {
    if (( $array =& $this -> getArrayResult($elem) ) !== null) {
      $func = $elem['FUNC'];
      $key = $elem['KEY'];
      $value2 = $elem['VALUE'];

      foreach ($array as $key_array => $value) {
        if ($key === null) {
          if ($this -> getCondition("$value", $value2, $func))
            continue;
        } else if ($value instanceof iXmlArray) {
          $value_array = $value -> array;

          if (isset($value_array[$key]) || array_key_exists($key, $value_array) AND $this -> getCondition("$value_array[$key]", $value2, $func))
            continue;
        }

        unset($array[$key_array]);
      }

      $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $array);
    }
  }

  protected function _ARRAY_TRIM($elem) {
    if (( $array =& $this -> getArrayResult($elem) ) !== null) {
	    $key = $elem['KEY'];
	    $type = $elem['TYPE'];

      foreach ($array as &$value) {
        if ($key !== null) {
          if (!$value instanceof iXmlArray)
            continue;

          $value =& $value -> array;

          if (!isset($value[$key]) && !array_key_exists($key, $value))
            continue;

          $value =& $value[$key];
        }

        switch ($type) {
          case 'left':
            $value = ltrim("$value");
            break;

          case 'right':
            $value = rtrim("$value");
            break;

          default: // both
            $value = trim("$value");
        }
      }

      $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $array);
	  }
  }

  protected function _ARRAY_KEYS($elem) {
    if (( $array =& $this -> getArrayResult($elem) ) !== null) {
      $array = array_keys($array);

      $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $array);
    }
  }

  protected function _ARRAY_VALUES($elem) {
    if (( $array =& $this -> getArrayResult($elem) ) !== null) {
      if (( $key = $elem['KEY'] ) === null)
        $array = array_values($array);
      else {
        $values = [];

        foreach ($array as $value)
          if ($value instanceof iXmlArray) {
            $value = $value -> array;
            isset($value[$key]) || array_key_exists($key, $value) AND $values[] = $value[$key];
          }

        $array = $values;
      }

      $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $array);
    }
  }

  protected function _ARRAY_CHUNK($elem) {
    if (( $array =& $this -> getArrayResult($elem) ) !== null) {
      $array = array_chunk($array, ( $length = $elem['LENGTH'] ) > 1 ? $length : 1, true);

      $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $array);
    }
  }

  protected function _ARRAY_GROUP($elem) {
    if (( $array =& $this -> getArrayResult($elem) ) !== null) {
      $key = $elem['KEY'];

      $group = [];

      foreach ($array as $key_array => $value)
        if ($value instanceof iXmlArray) {
          $value_array = $value -> array;
          isset($value_array[$key]) || array_key_exists($key, $value_array) AND $group["$value_array[$key]"][$key_array] = $value;
        }

      foreach ($group as &$value)
        $value = new iXmlArray($value);

      $array = $group;

      $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $array);
    }
  }

  protected function _ARRAY_SORT($elem) {
    if (( $array =& $this -> getArrayResult($elem) ) !== null) {
      switch ( $type = $elem['TYPE'] ) {
        case 'keys-asc':
        case 'keys-nat-asc':
          ksort($array, $type === 'keys-nat-asc' ? SORT_NATURAL | SORT_FLAG_CASE : SORT_REGULAR);
          break;

        case 'keys-desc':
        case 'keys-nat-desc':
          krsort($array, $type === 'keys-nat-desc' ? SORT_NATURAL | SORT_FLAG_CASE : SORT_REGULAR);
          break;

        case 'rand':
          $keys = array_keys($array);
          shuffle($keys);

          $sort = [];

          foreach ($keys as $key)
            $sort[$key] = $array[$key];

          $array = $sort;
          break;

        default: // asc, desc, nat-asc, nat-desc
          if (( $key = $elem['KEY'] ) === null)
            switch ($type) {
              case 'desc':
              case 'nat-desc':
                arsort($array, $type === 'nat-desc' ? SORT_NATURAL | SORT_FLAG_CASE : SORT_REGULAR);
                break;

              default:
                asort($array, $type === 'nat-asc' ? SORT_NATURAL | SORT_FLAG_CASE : SORT_REGULAR);
            }
          else {
            $sort = $array;

            foreach ($sort as &$value) {
              if ($value instanceof iXmlArray) {
                $value_array = $value -> array;

                if (isset($value_array[$key]) || array_key_exists($key, $value_array)) {
                  $value = $value_array[$key];
                  continue;
                }
              }

              $value = null;
            }

            switch ($type) {
              case 'desc':
              case 'nat-desc':
                arsort($sort, $type === 'nat-desc' ? SORT_NATURAL | SORT_FLAG_CASE : SORT_REGULAR);
                break;

              default:
                asort($sort, $type === 'nat-asc' ? SORT_NATURAL | SORT_FLAG_CASE : SORT_REGULAR);
            }

            foreach ($sort as $key => &$value)
              $value = $array[$key];

            $array = $sort;
          }
      }

      $var = $elem['VAR_RESULT'] AND $this -> setVarArray($var, $array);
    }
  }

  protected function _ARRAY_AGGREGATE($elem) {
    $type = $elem['TYPE'];

    if ( $array = $this -> getVarArray($elem['VAR']) ) {
      $key = $elem['KEY'];

      foreach ($array as $key_array => &$value) {
        if ($key !== null) {
          if (!$value instanceof iXmlArray)
            goto discard;

          $value_array = $value -> array;

          if (!isset($value_array[$key]))
            goto discard;

          $value = $value_array[$key];
        }

        if (is_numeric($value)) {
          $value = +$value;
          continue;
        }

        discard:
        unset($array[$key_array]);
      }
    }

    if ($array) {
      switch ($type) {
        case 'sum':
          return array_sum($array);

        case 'product':
          return array_product($array);

        case 'min':
          return min($array);

        case 'max':
          return max($array);

        case 'range':
          return max($array) - min($array);

        case 'avg':
        case 'mean-arith':
          return array_sum($array) / count($array);

        case 'median':
          sort($array, SORT_NUMERIC);
          return ( $count = count($array) ) & 1 ? $array[($count + 1) / 2 - 1] : ($array[$count / 2] + $array[$count / 2 - 1]) / 2;

        case 'mean-geo':
          return pow(array_product($array), 1 / count($array));

        case 'mean-harm':
          $sum = 0;

          foreach ($array as $value) {
            if ($value == 0)
              return;

            $sum += 1 / $value;
          }

          return count($array) / $sum;

        case 'mean-sqr':
          $sum = 0;

          foreach ($array as $value)
            $sum += $value * $value;

          return sqrt($sum / count($array));

        case 'stddev-pop':
        case 'stddev-samp':
        case 'var-pop':
        case 'var-samp':
          if (( $count = count($array) ) === 1)
            switch ($type) {
              case 'stddev-samp':
              case 'var-samp':
                return;
            }

          $avg = array_sum($array) / $count;
          $variance = 0;

          foreach ($array as $value)
            $variance += ( $delta = $value - $avg ) * $delta;

          switch ($type) {
            case 'stddev-samp':
            case 'var-samp':
              $count--;
              break;
          }

          $variance /= $count;

          switch ($type) {
            case 'stddev-pop':
            case 'stddev-samp':
              return sqrt($variance);
          }

          return $variance;
      }

      return count($array); // count
    }

    switch ($type) {
      case 'min':
      case 'max':
      case 'median':
        return;
    }

    return 0;
  }

  protected function _ARRAY_JOIN($elem) {
    if ( $array = $this -> getVarArray($elem['VAR']) ) {
      if (( $key = $elem['KEY'] ) !== null)
        foreach ($array as $key_array => &$value) {
          if ($value instanceof iXmlArray) {
            $value_array = $value -> array;

            if (isset($value_array[$key]) || array_key_exists($key, $value_array)) {
              $value = $value_array[$key];
              continue;
            }
          }

          unset($array[$key_array]);
        }

      return join($elem['DELIMITER'], $array);
    }

    return '';
  }

  // FIX downward compatibility
  protected function _ARRAY_TRANSFORM($elem) {
    if ( $var = $elem['VAR'] AND $array =& $this -> getVarRefArray($var) )
    	switch ($elem['TYPE']) {
    	  case 'reverse':
          $array = array_reverse($array);
          break;

    	  case 'keys':
          $array = array_keys($array);
          break;

    	  case 'unique':
    	    $array = array_flip($array);

    	  case 'flip':
          $array = array_flip($array);
          break;

    	  default: // reindex
          $array = array_values($array);
    	}
  }

  // FIX downward compatibility
  protected function _ARRAY_COMBINE($elem) {
    if (! $var = $elem['VAR_RESULT'] ?: $elem['VAR1'] )
      return;

  	$array1 = $this -> getVarArray($elem['VAR1']);
  	$array2 = $this -> getVarArray($elem['VAR2']);

  	switch ($elem['TYPE']) {
  	  case 'assoc':
			  if (( $length1 = count($array1) ) > ( $length2 = count($array2) ))
			    $array1 = array_slice($array1, 0, $length2);
			  else if ($length1 < $length2)
			    $array2 = array_slice($array2, 0, $length1);

			  $array = array_combine($array1, $array2);
        break;

  	  case 'complement':
        $array = array_diff($array1, $array2);
        break;

  	  case 'difference':
        $array = array_merge(array_diff($array1, $array2), array_diff($array2, $array1));
        break;

  	  case 'intersection':
        $array = array_intersect($array1, $array2);
        break;

  	  case 'union':
  	    $array = array_merge($array1, array_diff($array2, $array1));
  	    break;

  	  case 'keys-complement':
        $array = array_diff_key($array1, $array2);
        break;

  	  case 'keys-difference':
        $array = array_diff_key($array1, $array2) + array_diff_key($array2, $array1);
        break;

  	  case 'keys-intersection':
        $array = array_intersect_key($array1, $array2);
        break;

  	  case 'keys-union':
        $array = $array1 + $array2;
        break;

  	  default: // concat
        $array = array_merge($array1, $array2);
  	}

    $this -> setVarArray($var, $array);
  }

  protected function _ENCODE_CRYPT($elem) {
    $result = mcrypt_encrypt( $cipher = $elem['CIPHER'] , $elem['KEY'], $elem[SIGN_CDATA], $mode = $elem['MODE'] , $iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher, $mode)));
    $var = $elem['VAR_IV'] AND $this -> setVar($var, $iv);
    return $result;
  }

  protected function _ENCODE_DEFLATE($elem) {
    return gzdeflate($elem[SIGN_CDATA], $elem['LEVEL']);
  }

  protected function _ENCODE_ZLIB($elem) {
    return gzcompress($elem[SIGN_CDATA], $elem['LEVEL']);
  }

  protected function _ENCODE_JSON($elem) {
    return ( $var = $elem['VAR'] ) && ( $value = $this -> getVar($var, true) ) === $this -> undefined ? '' : json_encode(self::export($value));
  }

  protected function _ENCODE_CSV($elem) {
    $delimiter = $elem['DELIMITER'];

    $rows = [];

    foreach ($this -> getVarArray($elem['VAR']) as $row)
      if ($row instanceof iXmlArray) {
        $cols = $row -> array;

        foreach ($cols as &$col)
          $col = '"'.str_replace('"', '""', $col).'"';

        $rows[] = join($delimiter, $cols);
      }

    return join("\r\n", $rows);
  }

  protected function _ENCODE_BINARY($elem) {
    $args = $this -> getVarArray($elem['VAR']);
    array_unshift($args, $elem['FORMAT']);
    return call_user_func_array('\pack', $args);
  }

  protected function _DECODE_CRYPT($elem) {
    return mcrypt_decrypt($elem['CIPHER'], $elem['KEY'], $elem[SIGN_CDATA], $elem['MODE'], $elem['IV']);
  }

  protected function _DECODE_JSON($elem) {
    return self::import(decodeJson($elem[SIGN_CDATA]));
  }

  protected function _DECODE_CSV($elem) {
  	if ( $var = $elem['VAR'] ) {
  	  $csv = decodeCsv($elem[SIGN_CDATA], $elem['DELIMITER']);

  	  foreach ($csv as &$row)
  	    $row = new iXmlArray($row);

  	  $this -> setVarArray($var, $csv);
  	}
  }

  protected function _DECODE_BINARY($elem) {
    $var = $elem['VAR'] AND $this -> setVarArray($var, unpack($elem['FORMAT'], $elem[SIGN_CDATA]));
  }

  protected function _XML_CREATE($elem) {
    loadCommon('xml');

    return ''.new Xml($this -> getVarArrayExport($elem['VAR']));
  }

  protected function _XML_PARSE($elem) {
  	if ( $var = $elem['VAR'] ) {
      loadCommon('xml');

      $this -> setVar($var, self::import((new Xml) -> parse($elem[SIGN_CDATA])));
  	}
  }

  protected function _SOAP_CLIENT($elem) {
    if (isset($elem['SOAP:BIND'])) {
      $client = iXmlFunctionSoap::createClient($this -> getPath($elem['WSDL']));

      foreach ($elem['SOAP:BIND'] as $child) {
        isset($child[SIGN_MAP]) AND $child = $this -> map($child);

        $var = $child['VAR'] AND ( $operation = $child[SIGN_CDATA] ) !== '' AND
        $this -> setVar($var, new iXmlFunctionSoap($client, $operation));
      }
    }
  }

  protected function _REST_CLIENT($elem) {
    if (isset($elem['REST:BIND'])) {
      $url = $this -> getUrlHttp($elem['URL']);
      $timeout = $elem['TIMEOUT'];

      foreach ($elem['REST:BIND'] as $child) {
        isset($child[SIGN_MAP]) AND $child = $this -> map($child);

        $var = $child['VAR'] AND $this -> setVar($var, new iXmlFunctionRest($url, $timeout, $child[SIGN_CDATA], $child['METHOD'], $child['TYPE'] === 'raw'));
      }
    }
  }

  protected function _REST_SERVER($elem) {
    if (isset($elem['REST:RESOURCE'])) {
      list($path, $method) = initArrayMulti($_SERVER, [
        'PATH_INFO' => TYPE_STRING,
        'REQUEST_METHOD' => TYPE_STRING
      ], true);

      if (( $path = trim($path, '/') ) === '')
        $path = [];
      else
        foreach (( $path = explode('/', $path) ) as $component)
          if ($component === '') {
            http_response_code(400);
            return;
          }

      $count = count($path);

      foreach ($elem['REST:RESOURCE'] as $child) {
        isset($child[SIGN_MAP]) AND $child = $this -> map($child);

        if (strtoupper($child['METHOD']) === $method) {
          $route = ( $route = trim($child['ROUTE'], '/') ) === '' ? [] : explode('/', $route);

          if (count($route) === $count) {
            $params = [];

            for ($index = 0; $index < $count; $index++)
              if (( $component = $route[$index] ) !== '' && $component[0] === ':')
                $params[substr($component, 1)] = $path[$index];
              else if ($component !== $path[$index])
                continue 2;

            $vars = $params += self::import($method === 'POST' ? $_POST : $_GET) -> array;

            if (isset($child['USE']))
              foreach ($child['USE'] as $child_use) {
                isset($child_use[SIGN_MAP]) AND $child_use = $this -> map($child_use);

                $var = $child_use['VAR'] AND $vars[( $name = $child_use['NAME'] ) === '' ? (isset($var[1]) ? end($var[1]) : $var[0]) : $name] =& $this -> getVarRef($var);
              }

            ob_start();

            $e = null;
            $status = 200;

            if (isset($child[SIGN_CHILD]))
              try {
                $var = $child['VAR_HEADER'] AND $this -> setVarVirtual($vars, $var, new iXmlArray(getallheaders()));
                $var = $child['VAR_BODY'] AND $this -> setVarVirtual($vars, $var, readRawInput());

                $vars['return'] =& $status;
                $params AND $vars['arguments'] = new iXmlArray($params);

                $this -> runSub($child[SIGN_CHILD], $vars);

                $status = is_numeric($status) ? (int) +$status : 200;
              } catch (iXmlExit $e) {
              } catch (\Exception $e) {
                $status = 500;
              }

            http_response_code($status);

            ob_end_flush();

            if ($e)
              throw $e;

            return;
          }
        }
      }
    }

    http_response_code(404);
  }

  protected function _HTTP_REQUEST($elem) {
    if (isset($elem['HTTP:HEADER'])) {
      $child = $elem['HTTP:HEADER'][0];

      isset($child[SIGN_MAP]) AND $child = $this -> map($child);

      $header = preg_split('/(^|\r\n|\n|\r)\s*/', $child[SIGN_CDATA], -1, PREG_SPLIT_NO_EMPTY);
    } else
      $header = [];

    if (isset($elem['HTTP:BODY'])) {
      $child = $elem['HTTP:BODY'][0];

      isset($child[SIGN_MAP]) AND $child = $this -> map($child);

      $body = $child[SIGN_CDATA];
    } else
      $body = '';

    $info = self::sendHttpRequest($this -> getUrlHttp($elem['URL']), strtoupper($elem['METHOD']), $elem['TIMEOUT'], $body, $header);

    $var = $elem['VAR_INFO'] AND $this -> setVarArray($var, $info);
    return $info['body'];
  }

  protected function _HTTP_URLINFO($elem) {
  	if ( $var = $elem['VAR'] ) {
  	  $info = parse_url($elem['URL']);
  	  isset($info['query']) && parse_str($info['query'], $info['args']);
  	  $this -> setVarArray($var, $info);
  	}
  }

  protected function _HTTP_QUERY($elem) {
    return http_build_query($this -> getVarArrayExport($elem['VAR']), '', '&');
  }

  protected function _FILE_PATHINFO($elem) {
  	$var = $elem['VAR'] AND $this -> setVarArray($var, pathinfo($elem['FILENAME']));
  }

  protected function _FILE_EXISTS($elem) {
    if (( $filename = $this -> getPath($elem['FILENAME']) ) === '') // Empty filename for realpath would default to current working directory
      return false;

    clearstatcache(true, $filename);

    return realpath($filename);
  }

  protected function _FILE_TYPEOF($elem) {
    clearstatcache(true, $filename = $this -> getPath($elem['FILENAME']) );

    if (is_link($filename)) // Test first to avoid link following
      return 'link';

    if (is_file($filename))
      return 'file';

    if (is_dir($filename))
      return 'dir';

    return 'undefined';
  }

  protected function _FILE_STATUS($elem) {
  	if ( $var = $elem['VAR'] ) {
  	  clearstatcache(true, $filename = $this -> getPath($elem['FILENAME']) );

  	  $this -> setVarArray($var, $elem['FOLLOW'] ? stat($filename) : lstat($filename));
  	}
  }

  protected function _FILE_READ($elem) {
    $result = strcasecmp( $filename = $elem['FILENAME'] , 'php://input') === 0 ? readRawInput() : file_get_contents($this -> getPath($filename));
    $var = $elem['VAR_STRUCT'] AND $this -> setVar($var, $this -> unserialize($result));
    return $result;
  }

  protected function _FILE_WRITE($elem) {
    static $context;

    file_put_contents($this -> getPath($elem['FILENAME']), ( $var = $elem['VAR_STRUCT'] )
      ? $this -> getVarSerialized($var) : $elem[SIGN_CDATA]
    , 0, $context = $context ?: stream_context_create(['ftp' => ['overwrite' => true]]));
  }

  protected function _FILE_APPEND($elem) {
    file_put_contents($this -> getPath($elem['FILENAME']), $elem[SIGN_CDATA], FILE_APPEND);
  }

  protected function _FILE_DELETE($elem) {
    unlink($this -> getPath($elem['FILENAME']));
  }

  protected function _FILE_RENAME($elem) {
    rename($this -> getPath($elem['OLDNAME']), $this -> getPath($elem['NEWNAME']));
  }

  protected function _FILE_LINK($elem) {
    symlink($this -> getPath($elem['TARGETNAME']), $this -> getPath($elem['LINKNAME']));
  }

  protected function _FILE_LISTDIR($elem) {
    $var = $elem['VAR'] AND $this -> setVarArray($var, scandir($this -> getPath($elem['DIRNAME'])));
  }

  protected function _FILE_MAKEDIR($elem) {
    mkdir($this -> getPath($elem['DIRNAME']), 0777, true);
  }

  protected function _FILE_REMOVEDIR($elem) {
    rmdir($this -> getPath($elem['DIRNAME']));
  }

  protected function _FILE_GLOB($elem) {
    $var = $elem['VAR'] AND $this -> setVarArray($var, glob($this -> getPath($elem['PATTERN'])));
  }

  protected function _ZIP_ARCHIVE($elem) {
    $zip = new \ZipArchive;
    $code = $zip -> open($this -> getPath($elem['FILENAME']), \ZipArchive::CREATE);

    if ($code !== true)
      throw new \Exception("ZipArchive error code $code");

    $e = null;

    if (isset($elem[SIGN_CHILD]))
      try {
        $this -> runStruct('ZIP', $elem[SIGN_CHILD], $zip);
      } catch (\Exception $e) {}

    $zip -> close();

    if ($e)
      throw $e;
  }

  protected function _ZIP_EXISTS($elem) {
    return $this -> getStruct('ZIP') -> locateName($elem['FILENAME']);
  }

  protected function _ZIP_STATUS($elem) {
    $zip = $this -> getStruct('ZIP');

  	if ( $var = $elem['VAR'] ) {
  	  if (( $status = $zip -> statName($elem['FILENAME']) ) === false)
  	    $status = null;
  	  else {
  	    ( $crc =& $status['crc'] ) > 2147483647 AND $crc -= 4294967296; // 64-bit integer to 32-bit

  	    $status = new iXmlArray($status);
  	  }

  	  $this -> setVar($var, $status);
  	}
  }

  protected function _ZIP_READ($elem) {
    $result = ''.$this -> getStruct('ZIP') -> getFromName($elem['FILENAME']);
    $var = $elem['VAR_STRUCT'] AND $this -> setVar($var, $this -> unserialize($result));
    return $result;
  }

  protected function _ZIP_WRITE($elem) {
    $this -> getStruct('ZIP') -> addFromString($elem['FILENAME'], ( $var = $elem['VAR_STRUCT'] ) ? $this -> getVarSerialized($var) : $elem[SIGN_CDATA]);
  }

  protected function _ZIP_DELETE($elem) {
    $this -> getStruct('ZIP') -> deleteName($elem['FILENAME']);
  }

  protected function _ZIP_RENAME($elem) {
    $this -> getStruct('ZIP') -> renameName($elem['OLDNAME'], $elem['NEWNAME']);
  }

  protected function _ZIP_LIST($elem) {
    $zip = $this -> getStruct('ZIP');

    if ( $var = $elem['VAR'] ) {
      $list = [];

      for ($index = 0, $count = $zip -> numFiles; $index < $count; $index++)
        $list[] = $zip -> getNameIndex($index);

      $this -> setVarArray($var, $list);
    }
  }

  protected function _DB_CONNECTION($elem) {
    loadCommon('db');

    $conn = new Db\connect($elem['TYPE'], $elem['SERVER'], $elem['DBNAME'], $elem['USERNAME'], $elem['PASSWORD']);

    $e = null;

    if (isset($elem[SIGN_CHILD]))
      try {
        $this -> runStruct('DB', $elem[SIGN_CHILD], $conn);
      } catch (\Exception $e) {}

    $conn -> close();

    if ($e)
      throw $e;
  }

  protected function runDbData($elem, $name = null) {
    $fields = $name !== null;

    return isset($elem[SIGN_CHILD]) && ( $data = $this -> runStruct('DB_DATA', $elem[SIGN_CHILD], [$fields, []])[1] )
         ? $data : $this -> getVarArrayExport($elem[$fields ? $name : 'VAR_DATA']);
  }

  protected function _DB_GET($elem) {
    $conn = $this -> getStruct('DB');
    $fields = $this -> runDbData($elem, 'VAR_FIELDS');

    $var = $elem['VAR'] AND $this -> setVarArray($var, Db\Select::fetchRowById($elem['ENTITY'], $fields, $elem['ID'], true, $conn));
  }

  protected function _DB_FIELD($elem) {
    $data =& $this -> getStruct('DB_DATA');
    $data[0] AND $data[1][] = [$elem[SIGN_CDATA], $elem['ALIAS']];
  }

  protected function _DB_EXPRESSION($elem) {
    $data =& $this -> getStruct('DB_DATA');
    $data[0] AND $data[1][] = ['('.$elem[SIGN_CDATA].')', $elem['ALIAS']];
  }

  protected function _DB_SET($elem) {
    $conn = $this -> getStruct('DB');
    $data = $this -> runDbData($elem);

    $entity = $elem['ENTITY'];

    if (( $id = $elem['ID'] ) === null)
      return Db\Insert::exec($entity, $data, $conn);

    Db\Update::exec($entity, $data, Db\whereIs('ID', $id), $conn);
    return $id;
  }

  protected function setDbData($elem, $value) {
    $data =& $this -> getStruct('DB_DATA');

    if ($data[0])
      $data[1][] = ['('.Db\quote($value).')', $elem['ALIAS']];
    else
      $data[1][$elem['FIELD']] = $value;
  }

  protected function _DB_DATA($elem) {
    $this -> setDbData($elem, $elem[SIGN_CDATA]);
  }

  protected function _DB_NULL($elem) {
    $this -> setDbData($elem, null);
  }

  protected function _DB_REMOVE($elem) {
    $conn = $this -> getStruct('DB');

    Db\Delete::exec($elem['ENTITY'], Db\whereIs('ID', $elem['ID']), $conn);
  }

  protected function runDbCondition($elem, $or = false) {
    if (isset($elem[SIGN_CHILD])) {
      $condition = $this -> runStruct('DB_CONDITION', $elem[SIGN_CHILD]);
      return $or ? Db\whereOr($condition) : Db\whereAnd($condition);
    }

    return '';
  }

  protected function _DB_LOOKUP($elem) {
    $conn = $this -> getStruct('DB');

    return Db\Select::lookup($elem['ENTITY'], $this -> runDbCondition($elem), $conn);
  }

  protected function _DB_SELECT($elem) {
    $db = new Db\Select($this -> getStruct('DB'));
    $db -> distinct = $elem['DISTINCT'];

    if (isset($elem['DB:FIELDS'])) {
      $child = $elem['DB:FIELDS'][0];

      isset($child[SIGN_MAP]) AND $child = $this -> map($child);

      $db -> fields = $this -> runDbData($child, 'VAR');
    }

    if (isset($elem['DB:TABLE'])) {
      $child = $elem['DB:TABLE'][0];

      isset($child[SIGN_MAP]) AND $child = $this -> map($child);

      $db -> table = [$child[SIGN_CDATA], $child['ALIAS']];
    }

    isset($elem['DB:JOIN']) AND $db -> join = $this -> runStruct('DB_JOIN', $elem['DB:JOIN'][0][SIGN_CHILD]);
    $db -> where = $this -> runDbCondition($elem);

    if (isset($elem['DB:GROUPBY'])) {
      $child = $elem['DB:GROUPBY'][0];

      isset($child[SIGN_MAP]) AND $child = $this -> map($child);

      if (
        !isset($child[SIGN_CHILD]) ||
        (! $db -> groupby = $this -> runStruct('DB_GROUPBY', $child[SIGN_CHILD]) )
      )
        $db -> groupby = $this -> getVarArrayExport($child['VAR']);
    }

    isset($elem['DB:HAVING']) AND $db -> having = $this -> runDbCondition($elem['DB:HAVING'][0]);

    if (isset($elem['DB:ORDERBY'])) {
      $child = $elem['DB:ORDERBY'][0];

      isset($child[SIGN_MAP]) AND $child = $this -> map($child);

      if (
        !isset($child[SIGN_CHILD]) ||
        (! $db -> orderby = $this -> runStruct('DB_ORDERBY', $child[SIGN_CHILD]) )
      )
        foreach ($this -> getVarArray($child['VAR']) as $name => $type)
          $db -> orderby[$name] = strcasecmp($type, 'desc') === 0;
    }

    $db -> limit = $elem['LIMIT'];
    $db -> offset = $elem['OFFSET'];

    $var = $elem['VAR_COUNT'] AND $this -> setVar($var, $db -> count());

    if ( $var = $elem['VAR_RESULT'] ) {
      switch ( $type = $elem['TYPE'] ) {
        case 'list':
          $data = $db -> resultList();
          break;

        case 'av':
          $data = $db -> resultAV();
          break;

        case 'single':
          $data = $db -> result();
          break;

        case 'self':
          $data = $db -> result(true);
          break;

        default: // assoc, num
          $data = $db -> resultAll($type === 'assoc');

          foreach ($data as &$item)
            $item = new iXmlArray($item);
      }

      $this -> setVarArray($var, $data);
    }

    return "$db";
  }

  protected function runDbJoinCondition($elem) {
    return isset($elem[SIGN_CHILD])
         ? $this -> runDbCondition($elem)
         : Db\whereFieldIs($elem['FIELD1'], $elem['FIELD2'], $elem['FUNC'], $this -> getStruct('DB'));
  }

  protected function _DB_CROSS($elem) {
    $this -> getStruct('DB_JOIN')[] = Db\joinCross([$elem['TABLE'], $elem['ALIAS']]);
  }

  protected function _DB_NATURAL($elem) {
    $this -> getStruct('DB_JOIN')[] = Db\joinNatural([$elem['TABLE'], $elem['ALIAS']]);
  }

  protected function _DB_INNER($elem) {
    $this -> getStruct('DB_JOIN')[] = Db\joinInner([$elem['TABLE'], $elem['ALIAS']], $this -> runDbJoinCondition($elem));
  }

  protected function _DB_LEFTOUTER($elem) {
    $this -> getStruct('DB_JOIN')[] = Db\joinLeft([$elem['TABLE'], $elem['ALIAS']], $this -> runDbJoinCondition($elem));
  }

  protected function _DB_RIGHTOUTER($elem) {
    $this -> getStruct('DB_JOIN')[] = Db\joinRight([$elem['TABLE'], $elem['ALIAS']], $this -> runDbJoinCondition($elem));
  }

  protected function _DB_GROUPFIELD($elem) {
    $this -> getStruct('DB_GROUPBY')[] = $elem[SIGN_CDATA];
  }

  protected function _DB_ORDERFIELD($elem) {
    $this -> getStruct('DB_ORDERBY')[$elem[SIGN_CDATA]] = $elem['TYPE'] === 'desc';
  }

  protected function _DB_AND($elem) {
    $this -> getStruct('DB_CONDITION')[] = $this -> runDbCondition($elem);
  }

  protected function _DB_OR($elem) {
    $this -> getStruct('DB_CONDITION')[] = $this -> runDbCondition($elem, true);
  }

  protected function _DB_NOT($elem) {
    $this -> getStruct('DB_CONDITION')[] = Db\whereNot($this -> runDbCondition($elem));
  }

  protected function _DB_IS($elem) {
    $conn = $this -> getStruct('DB');

    $func = $elem['FUNC'];

    $this -> getStruct('DB_CONDITION')[] = ( $field = $elem['FIELD'] ) === null
                                         ? Db\whereFieldIs($elem['FIELD1'], $elem['FIELD2'], $func, $conn)
                                         : Db\whereIs($field, $elem[SIGN_CDATA], $func, $conn);
  }

  protected function _DB_ISNULL($elem) {
    $this -> getStruct('DB_CONDITION')[] = Db\whereIs($elem['FIELD'], null);
  }

  protected function _DB_ISNOTNULL($elem) {
    $this -> getStruct('DB_CONDITION')[] = Db\whereIs($elem['FIELD'], null, '<>');
  }

  protected function _DB_ALL($elem) {
    $this -> getStruct('DB_CONDITION')[] = Db\whereAll($elem['FIELD'], $elem[SIGN_CDATA], $elem['FUNC']);
  }

  protected function _DB_ANY($elem) {
    $this -> getStruct('DB_CONDITION')[] = Db\whereAny($elem['FIELD'], $elem[SIGN_CDATA], $elem['FUNC']);
  }

  protected function _DB_EXISTS($elem) {
    $this -> getStruct('DB_CONDITION')[] = Db\whereExists($elem[SIGN_CDATA]);
  }

  protected function _DB_NOTEXISTS($elem) {
    $this -> getStruct('DB_CONDITION')[] = Db\whereNotExists($elem[SIGN_CDATA]);
  }

  protected function _DB_IN($elem) {
    $this -> getStruct('DB_CONDITION')[] = Db\whereIn($elem['FIELD'], ( $var = $elem['VAR'] ) ? $this -> getVarArrayExport($var) : $elem[SIGN_CDATA]);
  }

  protected function _DB_NOTIN($elem) {
    $this -> getStruct('DB_CONDITION')[] = Db\whereNotIn($elem['FIELD'], ( $var = $elem['VAR'] ) ? $this -> getVarArrayExport($var) : $elem[SIGN_CDATA]);
  }

  protected function _DB_SEARCH($elem) {
    $this -> getStruct('DB_CONDITION')[] = Db\whereSearch((
      !isset($elem[SIGN_CHILD]) ||
      (! $fields = $this -> runStruct('DB_SEARCH', $elem[SIGN_CHILD]) )
    ) ? $this -> getVarArrayExport($elem['VAR']) : $fields, $elem['QUERY']);
  }

  protected function _DB_SEARCHFIELD($elem) {
    $this -> getStruct('DB_SEARCH')[] = $elem[SIGN_CDATA];
  }

  protected function _DB_INSERT($elem) {
    $conn = $this -> getStruct('DB');

    return Db\Insert::exec($elem['TABLE'], $this -> runDbData($elem), $conn);
  }

  protected function _DB_UPDATE($elem) {
    $conn = $this -> getStruct('DB');

    $structs =& $this -> structs;

    $where = [];
    $preserve =& $structs['DB_CONDITION'];
    $structs['DB_CONDITION'] =& $where;

    $e = null;

    try {
      $data = $this -> runDbData($elem);
    } catch (\Exception $e) {}

    $structs['DB_CONDITION'] =& $preserve;

    if ($e)
      throw $e;

    if (( $where = Db\whereAnd($where) ) === '')
      throw new iXmlException('Missing where condition in DB:UPDATE');

    Db\Update::exec($elem['TABLE'], $data, $where, $conn);
  }

  protected function _DB_DELETE($elem) {
    $conn = $this -> getStruct('DB');

    if (( $where = $this -> runDbCondition($elem) ) === '')
      throw new iXmlException('Missing where condition in DB:DELETE');

  	Db\Delete::exec($elem['TABLE'], $where, $conn);
  }

  protected function _DB_TRANSACTION($elem) {
    $conn = $this -> getStruct('DB');

    if (isset($elem[SIGN_CHILD])) {
      $trans = new Db\Transaction($conn);

      try {
        $result = $this -> run($elem[SIGN_CHILD]);

        $trans -> commit();
      } catch (iXmlExit $e) {
        $trans -> commit();

        throw $e;
      } catch (\Exception $e) {
        $trans -> rollback();

        throw $e;
      }

      return $result;
    }
  }

  protected function _MONGO_CONNECTION($elem) {
    loadCommon('mongo');

    $conn = new Mongo\Conn($elem['SERVER'], $elem['DBNAME'], $elem['USERNAME'], $elem['PASSWORD']);

    if (isset($elem[SIGN_CHILD]))
      $this -> runStruct('MONGO', $elem[SIGN_CHILD], $conn);
  }

  protected function runMongoFields($elem, $name) {
    return isset($elem[SIGN_CHILD]) && ( $data = $this -> runStruct('MONGO_FIELDS', $elem[SIGN_CHILD]) )
         ? $data : $this -> getVarArrayExport($elem[$name]);
  }

  protected function _MONGO_GET($elem) {
    $conn = $this -> getStruct('MONGO');
    $fields = $this -> runMongoFields($elem, 'VAR_FIELDS');

    $var = $elem['VAR'] AND $this -> setVar($var, self::import($conn -> findOne($elem['ID'], $fields)));
  }

  protected function _MONGO_FIELD($elem) {
    $this -> getStruct('MONGO_FIELDS')[] = $elem[SIGN_CDATA];
  }

  protected function runMongoData($elem) {
    return isset($elem[SIGN_CHILD]) && ( $data = $this -> runStruct('MONGO_DATA', $elem[SIGN_CHILD]) )
         ? $data : $this -> getVarArrayExport($elem['VAR_DATA']);
  }

  protected function _MONGO_SET($elem) {
    return $this -> getStruct('MONGO') -> save($elem['COLLECTION'], $this -> runMongoData($elem), $elem['ID']);
  }

  protected function _MONGO_DATA($elem) {
    $this -> getStruct('MONGO_DATA')[$elem['FIELD']] = ( $var = $elem['VAR'] ) ? $this -> getVarArrayExport($var) : $elem[SIGN_CDATA];
  }

  protected function _MONGO_NULL($elem) {
    $this -> getStruct('MONGO_DATA')[$elem['FIELD']] = null;
  }

  protected function _MONGO_TRUE($elem) {
    $this -> getStruct('MONGO_DATA')[$elem['FIELD']] = true;
  }

  protected function _MONGO_FALSE($elem) {
    $this -> getStruct('MONGO_DATA')[$elem['FIELD']] = false;
  }

  protected function _MONGO_REMOVE($elem) {
    $this -> getStruct('MONGO') -> removeById($elem['COLLECTION'], $elem['ID']);
  }

  protected function runMongoCondition($elem, $or = false) {
    if (isset($elem[SIGN_CHILD])) {
      $condition = $this -> runStruct('MONGO_CONDITION', $elem[SIGN_CHILD]);
      return $or ? Mongo\queryOr($condition) : Mongo\queryAnd($condition);
    }

    return [];
  }

  protected function _MONGO_LOOKUP($elem) {
    return $this -> getStruct('MONGO') -> lookup($elem['COLLECTION'], $this -> runMongoCondition($elem));
  }

  protected function _MONGO_DISTINCT($elem) {
    $conn = $this -> getStruct('MONGO');

    $var = $elem['VAR'] AND $this -> setVar($var, self::import($conn -> distinct($elem['COLLECTION'], $elem['FIELD'], $this -> runMongoCondition($elem))));
  }

  protected function _MONGO_FIND($elem) {
    $conn = $this -> getStruct('MONGO');

    $collection = $elem['COLLECTION'];

    $query = $this -> runMongoCondition($elem);

    if (isset($elem['MONGO:FIELDS'])) {
      $child = $elem['MONGO:FIELDS'][0];

      isset($child[SIGN_MAP]) AND $child = $this -> map($child);

      $fields = $this -> runMongoFields($child, 'VAR');
    } else
      $fields = [];

    $sort = [];

    if (isset($elem['MONGO:SORT'])) {
      $child = $elem['MONGO:SORT'][0];

      isset($child[SIGN_MAP]) AND $child = $this -> map($child);

      if (
        !isset($child[SIGN_CHILD]) ||
        (! $sort = $this -> runStruct('MONGO_SORT', $child[SIGN_CHILD]) )
      )
        foreach ($this -> getVarArray($child['VAR']) as $name => $type)
          $sort[$name] = strcasecmp($type, 'desc') === 0;
    }

    $var = $elem['VAR_COUNT'] AND $this -> setVar($var, $conn -> count($collection, $query));

    if ( $var = $elem['VAR_RESULT'] ) {
      if ( $data = $conn -> find($collection, $query, $fields, $sort, $elem['LIMIT'], $elem['OFFSET']) AND $elem['TYPE'] === 'self') {
        reset($data);
        $data = current($data);
      }

      $this -> setVar($var, self::import($data));
    }
  }

  protected function _MONGO_SORTFIELD($elem) {
    $this -> getStruct('MONGO_SORT')[$elem[SIGN_CDATA]] = $elem['TYPE'] === 'desc';
  }

  protected function _MONGO_AND($elem) {
    $this -> getStruct('MONGO_CONDITION')[] = $this -> runMongoCondition($elem);
  }

  protected function _MONGO_OR($elem) {
    $this -> getStruct('MONGO_CONDITION')[] = $this -> runMongoCondition($elem, true);
  }

  protected function _MONGO_IS($elem) {
    $this -> getStruct('MONGO_CONDITION')[] = Mongo\queryIs($elem['FIELD'], $elem[SIGN_CDATA], $elem['FUNC']);
  }

  protected function _MONGO_ISNULL($elem) {
    $this -> getStruct('MONGO_CONDITION')[] = Mongo\queryIs($elem['FIELD'], null);
  }

  protected function _MONGO_ISNOTNULL($elem) {
    $this -> getStruct('MONGO_CONDITION')[] = Mongo\queryIs($elem['FIELD'], null, '<>');
  }

  protected function _MONGO_ISTRUE($elem) {
    $this -> getStruct('MONGO_CONDITION')[] = Mongo\queryIs($elem['FIELD'], true);
  }

  protected function _MONGO_ISFALSE($elem) {
    $this -> getStruct('MONGO_CONDITION')[] = Mongo\queryIs($elem['FIELD'], false);
  }

  protected function _MONGO_ALL($elem) {
    $this -> getStruct('MONGO_CONDITION')[] = Mongo\queryAll($elem['FIELD'], $this -> getVarArrayExport($elem['VAR']));
  }

  protected function _MONGO_EXISTS($elem) {
    $this -> getStruct('MONGO_CONDITION')[] = Mongo\queryExists($elem['FIELD']);
  }

  protected function _MONGO_NOTEXISTS($elem) {
    $this -> getStruct('MONGO_CONDITION')[] = Mongo\queryNotExists($elem['FIELD']);
  }

  protected function _MONGO_IN($elem) {
    $this -> getStruct('MONGO_CONDITION')[] = Mongo\queryIn($elem['FIELD'], $this -> getVarArrayExport($elem['VAR']));
  }

  protected function _MONGO_NOTIN($elem) {
    $this -> getStruct('MONGO_CONDITION')[] = Mongo\queryNotIn($elem['FIELD'], $this -> getVarArrayExport($elem['VAR']));
  }

  protected function _MONGO_INSERT($elem) {
    return $this -> getStruct('MONGO') -> insert($elem['COLLECTION'], $this -> runMongoData($elem));
  }

  protected function _MONGO_UPDATE($elem) {
    $conn = $this -> getStruct('MONGO');

    $structs =& $this -> structs;

    $query = [];
    $preserve =& $structs['MONGO_CONDITION'];
    $structs['MONGO_CONDITION'] =& $query;

    $e = null;

    try {
      $data = $this -> runMongoData($elem);
    } catch (\Exception $e) {}

    $structs['MONGO_CONDITION'] =& $preserve;

    if ($e)
      throw $e;

    if (! $query = Mongo\queryAnd($query) )
      throw new iXmlException('Missing query condition in MONGO:UPDATE');

    $conn -> update($elem['COLLECTION'], $data, $query, true);
  }

  protected function _MONGO_DELETE($elem) {
    $conn = $this -> getStruct('MONGO');

    if (! $query = $this -> runMongoCondition($elem) )
      throw new iXmlException('Missing query condition in MONGO:REMOVE');

    $conn -> remove($elem['COLLECTION'], $query);
  }

  protected function _MAIL_PARSE($elem) {
    if ( $var = $elem['VAR'] ) {
      loadCommon('mail');

      $this -> setVar($var, self::import((new Mail\Mail($elem[SIGN_CDATA])) -> toArrayComplete()));
    }
  }

  protected function getMailMessage($elem) {
    if (isset($elem['MAIL:HEADER'])) {
      $child = $elem['MAIL:HEADER'][0];

      isset($child[SIGN_MAP]) AND $child = $this -> map($child);

      $rawmessage = trim($child[SIGN_CDATA]);
    } else
      $rawmessage = '';

    $rawmessage .= "\r\n\r\n";

    if (isset($elem['MAIL:BODY'])) {
      $child = $elem['MAIL:BODY'][0];

      isset($child[SIGN_MAP]) AND $child = $this -> map($child);

      $rawmessage .= $child[SIGN_CDATA];
    }

    return $rawmessage;
  }

  protected function _MAIL_SEND($elem) {
  	loadCommon('mail');

    $mail = new Mail\Mail($this -> getMailMessage($elem));

    $original = ini_set('sendmail_from', $mail -> sender_email);

    $e = null;

    try {
      if (!mail(
        Mail\Mail::createAddressList($mail -> to, ','),
        Mail\Mail::encodeHeader($mail -> subject),
        normalizeLineBreaks($mail -> createBody()),
        normalizeLineBreaks($mail -> createHeader()),
        "-oi -f $mail->sender_email"
      ))
        throw new \Exception('Unable to send mail');
    } catch (\Exception $e) {}

    ini_set('sendmail_from', $original);

    if ($e)
      throw $e;
  }

  protected function _MAIL_RECEIVE($elem) {
    if ( $var = $elem['VAR'] ) {
      loadCommon('mail');

      $list = [];

      $fetch = new Mail\Fetch($elem['MAILBOX'], $elem['USERNAME'], $elem['PASSWORD']);

      while ($fetch -> next()) {
        list($rawheader, $rawbody) = $fetch -> fetch();
        $list[] = "$rawheader\r\n\r\n$rawbody";
      }

      $fetch -> reset();

      while ($fetch -> next())
        $fetch -> delete();

      $fetch -> close();

      $this -> setVarArray($var, $list);
    }
  }

  protected function _MAIL_MULTIPART($elem) {
    $boundary = $elem['BOUNDARY'];

    $result = "This is a message with multiple parts in MIME format.\r\n\r\n--$boundary\r\n";
    isset($elem[SIGN_CHILD]) AND $result .= join("\r\n--$boundary\r\n", $this -> runStruct('MAIL_MULTIPART', $elem[SIGN_CHILD]));
    $result .= "\r\n--$boundary--";
    return $result;
  }

  protected function _MAIL_PART($elem) {
    $this -> getStruct('MAIL_MULTIPART')[] = $this -> getMailMessage($elem);
  }

  protected function _MAIL_QUOTE($elem) {
    loadCommon('mail');

  	return Mail\Mail::encodeHeader($elem[SIGN_CDATA]);
  }

  protected function _EXCEL_WORKBOOK($elem) {
    loadCommon('PHPExcel/PHPExcel');

    $excel = ( $filename = $elem['FILENAME'] ) === ''
           ? new \PHPExcel
           : \PHPExcel_IOFactory::createReader(( $format = $elem['FORMAT'] ) === '' ? \PHPExcel_IOFactory::identify($filename) : $format) -> load($this -> getPath($filename));

    $properties = $excel -> getProperties() -> setCreator('iXML');
    ( $keywords = $elem['KEYWORDS'] ) === null || $properties -> setKeywords($keywords);
    ( $subject = $elem['SUBJECT'] ) === null || $properties -> setSubject($subject);
    ( $title = $elem['TITLE'] ) === null || $properties -> setTitle($title);

    if (isset($elem[SIGN_CHILD])) {
      $sheet = 0;
      $row = 0;
      $col = 0;

      $this -> runStruct('EXCEL', $elem[SIGN_CHILD], [$excel, [&$sheet, &$row, &$col]]);
    }
  }

  protected function _EXCEL_LENGTH($elem) {
    list($excel, $pos) = $this -> getStruct('EXCEL');

    $var = $elem['VAR_COLS'] AND $this -> setVar($var, \PHPExcel_Cell::columnIndexFromString($excel -> getSheet($pos[0]) -> getHighestColumn()));
    $var = $elem['VAR_ROWS'] AND $this -> setVar($var, $excel -> getSheet($pos[0]) -> getHighestRow() - 1);
    $var = $elem['VAR_SHEETS'] AND $this -> setVar($var, $excel -> getSheetCount());
  }

  protected function _EXCEL_NEXT($elem) {
    list(, $pos) = $this -> getStruct('EXCEL');

    $pos[0] += $elem['OFFSET_SHEET'];
    $pos[1] += $elem['OFFSET_ROW'];
    $pos[2] += $elem['OFFSET_COL'];
  }

  protected function _EXCEL_GETPOS($elem) {
    list($excel, list($sheet, $row, $col)) = $this -> getStruct('EXCEL');

    $var = $elem['VAR_COL'] AND $this -> setVar($var, $col);
    $var = $elem['VAR_COORDS'] AND $this -> setVar($var, "'".$excel -> getSheet($sheet) -> getTitle()."'!".\PHPExcel_Cell::stringFromColumnIndex($col).($row + 1));
    $var = $elem['VAR_ROW'] AND $this -> setVar($var, $row);
    $var = $elem['VAR_SHEET'] AND $this -> setVar($var, $sheet);
  }

  protected function getExcelCoords($excel, $pos, $coords) {
    if (strpos($coords, '!') !== false) {
      list($sheet, $coords) = \PHPExcel_Worksheet::extractSheetTitle($coords, true);
      $pos[0] = $excel -> getIndex($excel -> getSheetByName($sheet));
    }

    list($col, $row) = \PHPExcel_Cell::coordinateFromString($coords);
    $pos[1] = $row - 1;
    $pos[2] = \PHPExcel_Cell::columnIndexFromString($col) - 1;
  }

  protected function _EXCEL_SETPOS($elem) {
    list($excel, $pos) = $this -> getStruct('EXCEL');

    if (( $coords = $elem['COORDS'] ) === '') {
      ( $sheet = $elem['SHEET'] ) === null OR $pos[0] = $sheet;
      ( $row = $elem['ROW'] ) === null OR $pos[1] = $row;
      ( $col = $elem['COL'] ) === null OR $pos[2] = $col;
    } else
      $this -> getExcelCoords($excel, $pos, $coords);
  }

  protected function getExcelCellValue($cell, $type) {
    switch ($type) {
      case 'formatted':
        return $cell -> getFormattedValue();

      case 'calculated':
        $value = ''.$cell -> getCalculatedValue();
        break;

      default: // raw
        $value = "$cell";
    }

    return $value !== '' && \PHPExcel_Shared_Date::isDateTime($cell)
         ? ''.\PHPExcel_Shared_Date::ExcelToPHP($value) : $value;
  }

  protected function _EXCEL_GET($elem) {
    list($excel, $pos) = $this -> getStruct('EXCEL');

    ( $coords = $elem['COORDS'] ) === '' || $this -> getExcelCoords($excel, $pos, $coords);

    return $this -> getExcelCellValue($excel -> getSheet($pos[0]) -> getCellByColumnAndRow($pos[2], $pos[1] + 1), $elem['TYPE']);
  }

  protected function _EXCEL_SET($elem) {
    list($excel, $pos) = $this -> getStruct('EXCEL');

    $value = $elem[SIGN_CDATA];

    ( $coords = $elem['COORDS'] ) === '' || $this -> getExcelCoords($excel, $pos, $coords);

    switch ( $type = $elem['TYPE'] ) {
      case 'text':
        $exceltype = \PHPExcel_Cell_DataType::TYPE_STRING;
        break;

      case 'bool':
        $exceltype = \PHPExcel_Cell_DataType::TYPE_BOOL;
        break;

      case 'numeric':
      case 'date':
        $date = $type === 'date';

        if (is_numeric($value) || $date && ( $value = parseTime($value) ) !== null) {
          $exceltype = \PHPExcel_Cell_DataType::TYPE_NUMERIC;
          $date AND $value = \PHPExcel_Shared_Date::PHPToExcel($value);
          break;
        }

      case 'null':
        $exceltype = \PHPExcel_Cell_DataType::TYPE_NULL;
        $value = '';
        break;

      default: // auto
        $exceltype = \PHPExcel_Cell_DefaultValueBinder::dataTypeForValue($value);
    }

    list($sheet, $row, $col) = $pos;
    $row++;

    $sheet = $excel -> getSheet($sheet);
    $sheet -> getCellByColumnAndRow($col, $row) -> setValueExplicit($value, $exceltype);

    ( $format = $elem['FORMAT'] ) === null || $sheet -> getStyleByColumnAndRow($col, $row) -> getNumberFormat() -> setFormatCode($format);
  }

  protected function _EXCEL_STYLE($elem) {
    list($excel, $pos) = $this -> getStruct('EXCEL');

    if ( $array = $this -> getVarArrayExport($elem['VAR']) ) {
      $sheet = $excel -> getSheet($pos[0]);

      if (( $range = $elem[SIGN_CDATA] ) === '') {
        list(, $row, $col) = $pos;
        $row++;

        if (isset($array['freeze'])) {
          $array['freeze'] && $sheet -> freezePaneByColumnAndRow($col, $row);
          unset($array['freeze']);
        }

        if (isset($array['height'])) {
          $sheet -> getRowDimension($row) -> setHeight($array['height']);
          unset($array['height']);
        }

        if (isset($array['width'])) {
          $sheet -> getColumnDimensionByColumn($col) -> setWidth($array['width']);
          unset($array['width']);
        }

        $array && $sheet -> getStyleByColumnAndRow($col, $row) -> applyFromArray($array);
      } else
        $sheet -> getStyle($range) -> applyFromArray($array);
    }
  }

  protected function _EXCEL_AUTOFILTER($elem) {
    list($excel, $pos) = $this -> getStruct('EXCEL');

    $sheet = $excel -> getSheet($pos[0]);
    $sheet -> setAutoFilter(( $range = $elem[SIGN_CDATA] ) === '' ? $sheet -> calculateWorksheetDimension() : $range);
  }

  protected function _EXCEL_ADDCOL($elem) {
    list($excel, $pos) = $this -> getStruct('EXCEL');

    if (( $index = $elem['INDEX'] ) === null)
      $index = $pos[2];
    else
      $pos[2] = $index;

    $sheet = $excel -> getSheet($pos[0]) -> insertNewColumnBeforeByIndex($index);

    ( $width = $elem['WIDTH'] ) === null || $sheet -> getColumnDimensionByColumn($index) -> setWidth($width);
  }

  protected function _EXCEL_REMOVECOL($elem) {
    list($excel, $pos) = $this -> getStruct('EXCEL');

    if (( $index = $elem['INDEX'] ) === null)
      $index = $pos[2];
    else
      $pos[2] = $index;

    $excel -> getSheet($pos[0]) -> removeColumnByIndex($index);
  }

  protected function _EXCEL_ADDROW($elem) {
    list($excel, $pos) = $this -> getStruct('EXCEL');

    if (( $index = $elem['INDEX'] ) === null)
      $index = $pos[1];
    else
      $pos[1] = $index;

    $index++;

    $sheet = $excel -> getSheet($pos[0]) -> insertNewRowBefore($index);

    ( $height = $elem['HEIGHT'] ) === null || $sheet -> getRowDimension($index) -> setHeight($height);
  }

  protected function _EXCEL_REMOVEROW($elem) {
    list($excel, $pos) = $this -> getStruct('EXCEL');

    if (( $index = $elem['INDEX'] ) === null)
      $index = $pos[1];
    else
      $pos[1] = $index;

    $excel -> getSheet($pos[0]) -> removeRow($index + 1);
  }

  protected function _EXCEL_ADDSHEET($elem) {
    list($excel, $pos) = $this -> getStruct('EXCEL');

    $pos[0] = ( $index = $elem['INDEX'] ) === null ? $excel -> getSheetCount() : $index;
    $excel -> addSheet(new \PHPExcel_Worksheet($excel, $elem[SIGN_CDATA]), $index);
  }

  protected function _EXCEL_REMOVESHEET($elem) {
    list($excel, $pos) = $this -> getStruct('EXCEL');

    if (( $index = $elem['INDEX'] ) === null)
      $index = $pos[0];
    else
      $pos[0] = $index;

    $index > 0 && $pos[0]--;

    $excel -> removeSheetByIndex($index);
  }

  protected function _EXCEL_ARRAY($elem) {
    $excel = $this -> getStruct('EXCEL')[0];

    if ( $var = $elem['VAR'] ) {
      $type = $elem['TYPE'];

      $sheets = [];

      foreach ($excel -> getWorksheetIterator() as $sheet) {
        $rows = [];

        foreach ($sheet -> getRowIterator() as $row) {
          $cols = [];

          $iterator = $row -> getCellIterator();
          $iterator -> setIterateOnlyExistingCells(false);

          foreach ($iterator as $cell)
            $cols[] = $this -> getExcelCellValue($cell, $type);

          $rows[] = new iXmlArray($cols);
        }

        $sheets[] = new iXmlArray($rows);
      }

      $this -> setVarArray($var, $sheets);
    }
  }

  protected function _EXCEL_CREATE($elem) {
    $writer = \PHPExcel_IOFactory::createWriter($this -> getStruct('EXCEL')[0], $elem['FORMAT']);
    $writer -> setPreCalculateFormulas(false);
    $writer -> save( $filename = tempnam(sys_get_temp_dir(), 'ixml') );
    $result = file_get_contents($filename);
    unlink($filename);
    return $result;
  }

  protected function _PDF_DOCUMENT($elem) {
    loadCommon('ixmlpdf');

    $pdf = new iXmlPdf('P', $elem['UNIT']);
    $pdf -> pdf_ixml = $this;
    $pdf -> setFontSubsetting(false);

    $pdf -> SetDisplayMode($elem['ZOOM'], $elem['LAYOUT'], $elem['MODE']);
    $pdf -> SetAuthor($elem['AUTHOR']);
    $pdf -> SetCreator('iXML');
    $pdf -> SetKeywords($elem['KEYWORDS']);
    $pdf -> SetSubject($elem['SUBJECT']);
    $pdf -> SetTitle($elem['TITLE']);

    if (isset($elem['PDF:SIGNATURE'])) {
      $child = $elem['PDF:SIGNATURE'][0];

      isset($child[SIGN_MAP]) AND $child = $this -> map($child);

      ( $signcert = $child['SIGNCERT'] ) !== '' && stripos($signcert, 'file://') !== 0 && $pdf -> setSignature(
  		  $signcert,
  		  stripos(( $privatekey = $child['PRIVATEKEY'] ), 'file://') === 0 ? '' : $privatekey,
  		  $elem['PASSWORD'],
  		  ( $extracerts = $child['EXTRACERTS'] ) === '' ? '' : 'data://application/x-pem-file;base64,'.base64_encode($extracerts)
  		);
    }

    isset($elem[SIGN_CHILD]) && $this -> runStruct('PDF', $elem[SIGN_CHILD], $pdf);

    return $pdf -> getPDFData();
  }

  public function runPdfContent($elem, $pdf) {
    $this -> runStruct('PDF_CONTENT', $elem[SIGN_CHILD], $pdf);
  }

  protected function _PDF_SECTION($elem) {
    $pdf = $this -> getStruct('PDF');

    $pdf -> endPage();

    $pdf -> pdf_templates = [];

    if (isset($elem['PDF:TEMPLATES'])) {
      $child = $elem['PDF:TEMPLATES'][0];

      isset($child[SIGN_MAP]) AND $child = $this -> map($child);

      ( $filename = $child['FILENAME'] ) === '' || $pdf -> setSourceFile($this -> getPath($filename));

      isset($child[SIGN_CHILD]) && $this -> runStruct('PDF_TEMPLATES', $child[SIGN_CHILD], $pdf);
    }

    $pdf -> pdf_header = isset($elem['PDF:HEADER']) ? $elem['PDF:HEADER'][0] : null;
    $pdf -> pdf_footer = isset($elem['PDF:FOOTER']) ? $elem['PDF:FOOTER'][0] : null;

    $pdf -> SetMargins($elem['LEFTMARGIN'], $elem['TOPMARGIN'], $elem['RIGHTMARGIN'], true);
    $pdf -> SetAutoPageBreak(true, $elem['BOTTOMMARGIN']);

    $pdf -> startPageGroup();

    $pdf -> AddPage($elem['ORIENTATION'], ( $height = $elem['HEIGHT'] ) > 0 && ( $width = $elem['WIDTH'] ) > 0 ? [$width, $height] : $elem['FORMAT']);

    isset($elem['PDF:BODY']) && $pdf -> pdf_run($elem['PDF:BODY'][0]);
  }

  protected function _PDF_TEMPLATE($elem) {
    $pdf = $this -> getStruct('PDF_TEMPLATES');

    $pdf -> pdf_templates[$elem['TARGET']] = $pdf -> importPage($elem['SOURCE']);
  }

  protected function _PDF_STYLE($elem) {
    $pdf = $this -> getStruct('PDF_CONTENT');

    if (isset($elem[SIGN_CHILD])) {
      $styles = $elem;
      unset($styles[SIGN_MAP], $styles[SIGN_ATTR_T], $styles[SIGN_CHILD], $styles[SIGN_FUNC]);

      foreach ($styles as $name => $value)
        if ($value === null)
          unset($styles[$name]);

      $pdf -> pdf_run($elem, $styles + $pdf -> pdf_styles);
    }
  }

  protected function _PDF_LINEBREAK($elem) {
    $this -> getStruct('PDF_CONTENT') -> Ln(( $offset = $elem['OFFSET'] ) > 0 ? $offset : '');
  }

  protected function _PDF_PAGEBREAK($elem) {
    $pdf = $this -> getStruct('PDF_CONTENT');

    $pdf -> AddPage();
    $pdf -> SetY($pdf -> GetY() + $elem['OFFSET']);
  }

  protected function _PDF_INLINE($elem) {
    $pdf = $this -> getStruct('PDF_CONTENT');

    $text = $elem[SIGN_CDATA];

    $margins = $pdf -> getOriginalMargins();
    $pdf -> SetLeftMargin($margins['left'] + $elem['LEFTMARGIN']);
    $pdf -> SetRightMargin($margins['right'] + $elem['RIGHTMARGIN']);

    ( $x = $elem['X'] ) === '' || $pdf -> SetX($x);
    ( $y = $elem['Y'] ) === '' || $pdf -> SetY($y, false);

    $styles = $pdf -> pdf_styles;
    $align = $styles['ALIGN'];
    $fill = $styles['BGCOLOR'] != '';

    if ($elem['HTML'])
      $pdf -> writeHTML($text, false, $fill, false, false, $align);
    else
      $pdf -> Write($styles['LINEHEIGHT'], $text, '', $fill, $align);
  }

  protected function _PDF_BLOCK($elem) {
    $pdf = $this -> getStruct('PDF_CONTENT');

    $text = $elem[SIGN_CDATA];
    $height = $elem['HEIGHT'];
    $width = $elem['WIDTH'];
    $x = $elem['X'];
    $y = $elem['Y'];

    if ( $abs = $x !== '' || $y !== '' ) {
      $page_old = $pdf -> getPage();
      $x_old = $pdf -> GetX();
      $y_old = $pdf -> GetY();
    }

    $margins = $pdf -> getOriginalMargins();
    $pdf -> SetLeftMargin( $left = $margins['left'] + $elem['LEFTMARGIN'] );
    $pdf -> SetRightMargin($margins['right'] + $elem['RIGHTMARGIN']);

    $x === '' AND $x = $left;

    $styles = $pdf -> pdf_styles;
    $align = $styles['ALIGN'];
    $border = $styles['BORDER'];
    $fill = $styles['BGCOLOR'] != '';

    if ($elem['HTML'])
      $pdf -> writeHTMLCell($width, $height, $x, $y, $text, $border, 1, $fill, false, $align);
    else if ($elem['NOWRAP']) {
      $pdf -> SetX($x);
      $y === '' || $pdf -> SetY($y, false);

      $pdf -> Cell($width, $height, $pdf -> pdf_cutText($text, $width), $border, 1, $align, $fill, '', 0, false, 'T', $styles['VALIGN']);
    } else
      $pdf -> MultiCell($width, $height, $text, $border, $align, $fill, 1, $x, $y);

    if ($abs) {
      $pdf -> setPage($page_old);
      $pdf -> setXY($x_old, $y_old);
    }
  }

  protected function _PDF_ROW($elem) {
    $pdf = $this -> getStruct('PDF_CONTENT');

    $x = $elem['X'];
    $y = $elem['Y'];

    $page_old = $pdf -> getPage();

    if ( $abs = $x !== '' || $y !== '' ) {
      $x_old = $pdf -> GetX();
      $y_old = $pdf -> GetY();
    }

    $margins = $pdf -> getOriginalMargins();
    $pdf -> SetLeftMargin( $left = $margins['left'] + $elem['LEFTMARGIN'] );
    $pdf -> SetRightMargin($margins['right'] + $elem['RIGHTMARGIN']);
    $pdf -> SetX($x === '' ? $left : $x);

    if ($y === '')
      $y = $pdf -> GetY();
    else
      $pdf -> SetY($y, false);

    $e = null;

    if (isset($elem[SIGN_CHILD])) {
      $page = $page_old;

      try {
        $this -> runStruct('PDF_ROW', $elem[SIGN_CHILD], [$pdf, $page, $y, &$page, &$y]);
      } catch (\Exception $e) {}

      if (!$abs) {
        $pdf -> setPage($page);
        $pdf -> SetY($y);
      }
    }

    if ($abs) {
      $pdf -> setPage($page_old);
      $pdf -> setXY($x_old, $y_old);
    }

    if ($e)
      throw $e;
  }

  protected function _PDF_COL($elem) {
    list($pdf, $page, $y) = $row = $this -> getStruct('PDF_ROW');

    $text = $elem[SIGN_CDATA];
    $height = $elem['HEIGHT'];
    $width = $elem['WIDTH'];

    $pdf -> setPage($page);
    $pdf -> SetY($y, false);

    $styles = $pdf -> pdf_styles;
    $align = $styles['ALIGN'];
    $border = $styles['BORDER'];
    $fill = $styles['BGCOLOR'] != '';

    if ($elem['HTML'])
      $pdf -> writeHTMLCell($width, $height, '', '', $text, $border, 2, $fill, false, $align);
    else if ($elem['NOWRAP']) {
      $pdf -> Cell($width, $height, $pdf -> pdf_cutText($text, $width), $border, 0, $align, $fill, '', 0, false, 'T', $styles['VALIGN']);
      $pdf -> SetY($pdf -> GetY() + $pdf -> getLastH(), false);
    } else
      $pdf -> MultiCell($width, $height, $text, $border, $align, $fill, 2);

    $y = $pdf -> GetY();

    if (( $page = $pdf -> getPage() ) > $row[3]) {
      $row[3] = $page;
      $row[4] = $y;
    } else if ($page == $row[3] && $y > $row[4])
      $row[4] = $y;
  }

  protected function _PDF_IMAGE($elem) {
    $pdf = $this -> getStruct('PDF_CONTENT');

    $styles = $pdf -> pdf_styles;
    $pdf -> Image($this -> getPath($elem['FILENAME']), $elem['X'], $elem['Y'], $elem['WIDTH'], $elem['HEIGHT'], $elem['TYPE'], '', $styles['VALIGN'], false, $elem['DPI'], '', false, false, $styles['BORDER']);
  }

  protected function _PDF_BARCODE($elem) {
    $pdf = $this -> getStruct('PDF_CONTENT');

    $code = $elem[SIGN_CDATA];
    $height = $elem['HEIGHT'];
    $type = $elem['TYPE'];
    $width = $elem['WIDTH'];
    $x = $elem['X'];
    $y = $elem['Y'];

    $styles = $pdf -> pdf_styles;
    $bgcolor = $styles['BGCOLOR'];
    $valign = $styles['VALIGN'];

    $style = [
      'stretch' => true,
      'align' => $styles['ALIGN'],
      'padding' => $styles['PADDING'],
      'border' => trim($styles['BORDER']) !== '',
      'fgcolor' => $pdf -> pdf_convertColor($styles['TEXTCOLOR'])
    ];

    $bgcolor == '' OR $style['bgcolor'] = $pdf -> pdf_convertColor($bgcolor);

    if (stripos($type, 'QRCODE') === 0 || stripos($type, 'PDF417') === 0)
      $pdf -> write2DBarcode($code, $type, $x, $y, $width, $height, $style, $valign);
    else
      $pdf -> write1DBarcode($code, $type, $x, $y, $width, $height, '', $style, $valign);
  }

  protected function _PDF_GETPOS($elem) {
    $pdf = $this -> getStruct('PDF_CONTENT');

    $var = $elem['VAR_PAGE'] AND $this -> setVar($var, $pdf -> PageNo());
    $var = $elem['VAR_SUBPAGE'] AND $this -> setVar($var, $pdf -> getGroupPageNo());
    $var = $elem['VAR_X'] AND $this -> setVar($var, $pdf -> GetX());
    $var = $elem['VAR_Y'] AND $this -> setVar($var, $pdf -> GetY());
  }

  protected function _PDF_SETPOS($elem) {
    $pdf = $this -> getStruct('PDF_CONTENT');

    ( $page = $elem['PAGE'] ) === null || $pdf -> setPage($page);
    ( $x = $elem['X'] ) === null || $pdf -> SetX($x);
    ( $y = $elem['Y'] ) === null || $pdf -> SetY($y, false);
  }

  protected function _DEBUG_OUTPUT($elem) {
    $this -> debugOutput($elem[SIGN_CDATA]);
  }

  protected function _DEBUG_DUMP($elem) {
    if ( $var = $elem['VAR'] ) {
      if (( $value = $this -> getVar($var, true) ) === $this -> undefined)
        return;

      $this -> debugOutput($this -> getVarDebug($var).': ');
    } else
      $value = $this -> local + $this -> global;

    try {
      $data = print_r(self::export($value), true);
    } catch (iXmlException $e) {
      $data = 'DEBUG:DUMP '.$e -> getMessage();
    }

    $this -> debugOutput($data);
  }

  protected function _DEBUG_LOG($elem) {
    ( $message = $elem[SIGN_CDATA] ) === '' || $this -> debugLog($message);
  }

  protected function _DEBUG_TIMER() {
    $timer = $this -> timer;
    return ( $this -> timer = microtime(true) ) - $timer;
  }

  protected function _DEBUG_EXCLUDE() {}
}
