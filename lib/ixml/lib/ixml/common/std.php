<?php
namespace Zeyon;

const VERSION = 106;

const TYPE_STRING    = 0;
const TYPE_STRING_NE = 1;
const TYPE_TRIM      = 2;
const TYPE_TRIM_NE   = 3;
const TYPE_INT       = 4;
const TYPE_FLOAT     = 5;
const TYPE_ARRAY     = 6;
const TYPE_INDEX     = 7;
const TYPE_UNIQUE    = 8;

// -------------------- Implementation --------------------

trait Singleton {
  public static function Instance() {
    static $instance;

    return $instance ?: $instance = new static;
  }
}

function loadCommon($name) {
  static $used = ['std' => true];

  if (! $included =& $used[$name] ) {
    $included = true;

    require __DIR__."/$name.php";
  }
}

function handleError($errno, $errstr, $errfile, $errline) {
  switch ($errno) {
    case E_STRICT: // FIX external libs
      return true;

    case E_NOTICE:
      switch ($errstr) {
        case 'Array to string conversion':
        case 'String offset cast occurred':
          return true;
      }

      break;
  }

  throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
}

function headerCache($maxage) {
  session_cache_limiter('');
  header('Expires: '.gmdate('D, d M Y H:i:s', time() + $maxage).' GMT');
  header("Cache-Control: max-age=$maxage, must-revalidate");
  header('Pragma: public');
}

function headerNoCache() {
  session_cache_limiter('');
  header('Expires: 0');
  header('Cache-Control: no-store, no-cache, must-revalidate');
  header('Pragma: no-cache');
}

function readRawInput() {
  static $input;

  if ($input === null)
    if (initArrayVal($_SERVER, 'REQUEST_METHOD') === 'PUT') {
      $handle = fopen('php://input', 'rb');

      while (!feof($handle))
        $input .= fread($handle, 8192);

      fclose($handle);
    } else
      $input = file_get_contents('php://input');

  return $input;
}

function initVal($value, $type = TYPE_STRING, $default = false) {
  switch ($type) {
    case TYPE_STRING:
      break;

    case TYPE_STRING_NE:
      return ( $value = "$value" ) === '' ? ($default === false ? '' : $default) : $value;

    case TYPE_TRIM:
      return $value === null ? ($default === false ? '' : $default) : trim("$value");

    case TYPE_TRIM_NE:
      return ( $value = trim("$value") ) === '' ? ($default === false ? '' : $default) : $value;

  	case TYPE_INT:
  	  return is_numeric($value) ? (int) +$value : ($default === false ? 0 : $default);

  	case TYPE_FLOAT:
  	  return is_numeric($value) ? +$value : ($default === false ? 0 : $default);

  	case TYPE_ARRAY:
  	  return is_array($value) ? $value : ($default === false ? [] : $default);

  	case TYPE_INDEX:
      if (is_array($value)) {
        $index = [];

        foreach ($value as $array_value)
          is_numeric($array_value) AND $index[] = (int) +$array_value;

  	    return $index;
      }

      return $default === false ? [] : $default;

  	case TYPE_UNIQUE:
  	  if (is_array($value)) {
  	    $unique = [];

  	    foreach ($value as $array_value)
  	      $unique[trim("$array_value")] = true;

  	    unset($unique['']);
  	    return array_keys($unique);
  	  }

      return $default === false ? [] : $default;
  }

  return $value === null ? ($default === false ? '' : $default) : "$value";
}

function initArrayVal($array, $key, $type = TYPE_STRING, $default = false) {
  return initVal(isset($array[$key]) ? $array[$key] : null, $type, $default);
}

function initArrayMulti($array, $keys, $list = false) {
  foreach ($keys as $key => &$definition) {
    if (isset($definition[0]))
  	  list($type, $default) = $definition;
  	else {
      $type = $definition;
  		$default = false;
  	}

  	$definition = initArrayVal($array, $key, $type, $default);
  }

  return $list ? array_values($keys) : $keys;
}

function normalizeLineBreaks($string) {
  return strrpos($string, "\r") === false ? $string : str_replace(["\r\n", "\r"], "\n", $string);
}

function extractServer($server, $defport) {
  return [
    $host = strtok($server, ':'),
    ( $port = strtok('') ) == '' ? $defport : (int) $port
  ];
}

function mkDate($date, $offsetday = 0) {
  $time = getdate($date);
  return mktime(0, 0, 0, $time['mon'], $time['mday'] + $offsetday, $time['year']);
}

function parseTime($string) {
  if (( $string = preg_replace('/(\d{1,2})\.\s*(\d{1,2})\.\s*(\d{2,4})/', '$2/$1/$3', trim($string), 1) ) != '' && ( $time = strtotime($string) ) !== false)
    return $time;
}

function trimText($string, $maxlength = 255) {
  return mb_strimwidth(trim($string), 0, $maxlength);
}

function trimFilename($filename, $maxlength = 255) {
  return mb_strlen( $filename = basename($filename) ) > $maxlength
       ? mb_substr($filename, 0, $maxlength - mb_strlen( $fileext = strrchr($filename, '.') )).$fileext
       : $filename;
}

function decodeJson($string) {
  $data = json_decode($string, true);

  switch (json_last_error()) {
    case JSON_ERROR_NONE:
      return $data;

    case JSON_ERROR_DEPTH:
      throw new \Exception('JSON: Maximum stack depth exceeded');

    case JSON_ERROR_STATE_MISMATCH:
      throw new \Exception('JSON: State mismatch');

    case JSON_ERROR_CTRL_CHAR:
      throw new \Exception('JSON: Control character error');

    case JSON_ERROR_SYNTAX:
      throw new \Exception('JSON: Syntax error');

    case JSON_ERROR_UTF8:
      throw new \Exception('JSON: Malformed UTF-8 characters');

    default:
      throw new \Exception('JSON: Unknown error');
  }
}

function decodeCsv($string, $delimiter = ';') {
  if ($string == '')
    return [];

  $delimiter == '' OR $delimiter = $delimiter[0];

  $simple = true;
  $array = [];
  $row = [];
  $col = '';

  for ($pos = 0, $length = strlen( $string = normalizeLineBreaks($string) ); $pos < $length;)
    switch ( $char = $string[ $pos++ ] ) {
      case '"':
        if ($simple) {
          if ($col === '')
            $simple = false;
          else
            $col .= '"';
        } else if ($pos < $length && $string[$pos] === '"') {
          $col .= '"';
          $pos++;
        } else
          $simple = true;

        break;

      case $delimiter:
      case "\n":
        if ($simple) {
          $row[] = $col;
          $col = '';

          if ($char === "\n") {
            $array[] = $row;
            $row = [];
          }

          break;
        }

      default:
        $col .= $char;
    }

  $row[] = $col;
  $array[] = $row;

  return $array;
}

function encodeXml($string) {
  return htmlspecialchars($string, ENT_COMPAT);
}

function decodeXml($string) {
  return htmlspecialchars_decode($string, ENT_QUOTES);
}

function encodeHtml($string, $urltolink = false) {
  $string = encodeXml($string);

  return str_replace(
    ["\r\n", "\r", "\n", "\t", '  ', '  '],
    ['<br />', '<br />', '<br />', ' ', '&nbsp; ', ' &nbsp;'],
    $urltolink ? preg_replace(
      ['/(?<=^|[\s(:,;@])(?=www\.\w)/', '/(?:\.\.?|[a-z\d.]+:\/)\/(?:[\w\-=$%:,;~@#?.+\/]|&amp;)+/i'],
      ['http://', '<a href="$0" target="_blank">$0</a>'],
      $string
    ) : $string
  );
}

function decodeHtml($string, $break = true) {
  $string = preg_replace('/&nbsp;|\s+/', ' ', preg_match('/<body\b.*<\/body>/is', $string, $matches) ? $matches[0] : $string);
  return decodeHtmlEntities(strip_tags($break ? preg_replace('/<(?:br\s*\/?|\/p)>\s*/i', "\n", $string) : $string));
}

function decodeHtmlEntities($string) {
  return html_entity_decode($string, ENT_QUOTES);
}

function convert($string, $to = 'UTF-8', $from = 'UTF-8') {
	switch ($from) {
		case 'UTF-8':
		  if ($from === 'ISO-8859-1')
			  return utf8_decode($string);

		  break;

		case 'ISO-8859-1':
			if ($to === 'UTF-8')
			  return utf8_encode($string);

		  break;

		case 'US-ASCII':
			$from = 'ASCII';
		  break;
	}

  try {
    return mb_convert_encoding($string, $to === 'US-ASCII' ? 'ASCII' : $to, $from);
  } catch (\ErrorException $e) {
  	return preg_replace('/[^\x00-\x7f]/', '', $string);
  }
}

function chrUTF8($ord) {
  if ($ord <= 0x7F)
    return chr($ord);

  if ($ord <= 0x7FF)
    return chr(0xC0 | $ord >> 6).chr(0x80 | $ord & 0x3F);

  if ($ord <= 0xFFFF)
    return chr(0xE0 | $ord >> 12).chr(0x80 | $ord >> 6 & 0x3F).chr(0x80 | $ord & 0x3F);

  if ($ord <= 0x10FFFF)
    return chr(0xF0 | $ord >> 18).chr(0x80 | $ord >> 12 & 0x3F).chr(0x80 | $ord >> 6 & 0x3F).chr(0x80 | $ord & 0x3F);
}

function ordUTF8($char) {
  $ord = ord($char == '' ? '' : $char[0]);

  if ($ord <= 0x7F)
    return $ord;

  if ($ord < 0xC2)
    return;

  if ($ord <= 0xDF)
    return ($ord & 0x1F) << 6 | ord($char[1]) & 0x3F;

  if ($ord <= 0xEF)
    return ($ord & 0x0F) << 12 | (ord($char[1]) & 0x3F) << 6 | ord($char[2]) & 0x3F;

  if ($ord <= 0xF4)
    return ($ord & 0x0F) << 18 | (ord($char[1]) & 0x3F) << 12 | (ord($char[2]) & 0x3F) << 6 | ord($char[3]) & 0x3F;
}

function crc32($string) {
  return ( $crc32 = \crc32($string) ) > 2147483647 ? $crc32 - 4294967296 /* 64-bit integer to 32-bit */ : $crc32;
}

function between($value, $min, $max) {
  return max($min, min($max, $value));
}

function overwrite($array1, $array2) {
  is_array($array1) OR $array1 = [];
  return is_array($array2) ? array_replace_recursive($array1, $array2) : $array1;
}

function sortMulti(&$data, $sort) {
  if (!$data || !$sort)
    return;

  $args = [];

  foreach ($sort as $item) {
  	list($key, $type, $desc) = $item;

  	$list = $data;

    foreach ($list as &$value)
      $value = $value[$key];

    $args[] = $list;
    $args[] = $desc ? SORT_DESC : SORT_ASC;
    $args[] = $type;
  }

  $args[] =& $data;

  call_user_func_array('\array_multisort', $args);
}

function tokenizeQuery($query) {
  if ($query == '')
    return [];

  static $used;

  if ( $tokens =& $used[$query] )
    return $tokens;

  $simple = true;
  $token = '';

  for ($pos = 0, $length = strlen( $string = mb_strtolower($query) ); $pos < $length;)
    switch ( $char = $string[ $pos++ ] ) {
      case '"':
      	if ($simple) {
      	  if ($token === '')
        	  $simple = false;
        	else
        	  $token .= '"';
      	} else if ($pos < $length && $string[$pos] === '"') {
    	  	$token .= '"';
    	  	$pos++;
    	  } else
    	    $simple = true;

      	break;

      case ' ':
      case ',':
      case ';':
      case '-':
      case "\n":
      case "\t":
      	if ($simple) {
      	  $tokens[trim($token)] = true;
          $token = '';
      	  break;
      	}

      default:
      	$token .= $char;
    }

  $tokens[trim($token)] = true;

  unset($tokens['']);

  return $tokens = array_keys($tokens);
}

function searchTokens($elems, $tokens) {
  if ($tokens) {
  	$search = mb_strtolower(join(' ', (array) $elems));

    foreach ($tokens as $token)
  	  if (strpos($search, $token) === false)
  	    return false;
  }

  return true;
}