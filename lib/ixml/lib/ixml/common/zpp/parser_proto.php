<?php
namespace Zeyon\Zpp\ParserProto;

abstract class Parser {
  protected $signature;

  protected static $signatures;

  abstract public function match($input, &$pos);
  abstract public function code();

  public function parse($input) {
    $pos = 0;
    $match = $this -> match("$input\0", $pos);

    if ($pos < strlen($input))
      throw new \Exception("Parser failed at '".substr($input, $pos, 100)." ...'");

    return $match;
  }

  public function createParser($name) {
    $signatures =& self::$signatures;

    $signatures = new \SplObjectStorage;

    $class = "class $name {\n"
           .   "public \$length;\n"
           .   'public function parse($input) {'
           .     '$this -> length = strlen($input);'
           .     '$input .= "\\0";'
           .     '$pos = 0;'
           .     'return ['.$this.', $pos, $this -> length];'
           .   "}\n";

    foreach ($signatures as $signature) {
      list($id, $code) = $signatures -> getInfo();
      $class .= 'public function _'.$id.'($input, &$pos) {'.$code.'}'."\n";
    }

    $class .= '}';

    return $class;
  }

  public function __toString() {
    $signatures = self::$signatures;

    if ($signatures -> contains($this))
      $id = $signatures -> offsetGet($this)[0];
    else {
      $signatures -> attach($this, [ $id = $signatures -> count() ]);
      $signatures -> attach($this, [$id, $this -> code()]);
    }

    return '$this -> _'.$id.'($input, $pos)';
  }
}

class Wrap extends Parser {
  public $parser;

  public function match($input, &$pos) {
    return $this -> parser -> match($input, $pos);
  }

  public function code() {}

  public function __toString() {
    return "$this->parser";
  }
}

class C extends Parser {
  protected $char;
  protected $result;

  public function __construct($char, $ignore = true) {
    $this -> char = $char;
    $this -> result = $ignore ?: $char;
  }

  public function match($input, &$pos) {
    if ($input[$pos] === $this -> char) {
      $pos++;
      return $this -> result;
    }
  }

  public function code() {
    return 'if ($input[$pos] === '.var_export($this -> char, true).') {'
         .   '$pos++;'
         .   'return '.var_export($this -> result, true).';'
         . '}';
  }
}

class S extends Parser {
  protected $string;
  protected $length;
  protected $result;

  public function __construct($string, $ignore = true) {
    $this -> string = $string;
    $this -> length = strlen($string);
    $this -> result = $ignore ?: $string;
  }

  public function match($input, &$pos) {
    if (substr($input, $pos, $this -> length) === $this -> string) {
      $pos += $this -> length;
      return $this -> result;
    }
  }

  public function code() {
    return 'if (substr($input, $pos, '.$this -> length.') === '.var_export($this -> string, true).') {'
         .   '$pos += '.$this -> length.';'
         .   'return '.var_export($this -> result, true).';'
         . '}';
  }
}

class Ss extends Parser {
  protected $strings = [];
  protected $ignore;

  public function __construct($strings, $ignore = false) {
    foreach ($strings as $string)
      $this -> strings[strlen($string)][$string] = true;

    krsort($this -> strings, SORT_NUMERIC);

    $this -> ignore = $ignore;
  }

  public function match($input, &$pos) {
    foreach ($this -> strings as $length => $strings)
      if (isset($strings[ $string = $length === 1 ? $input[$pos] : substr($input, $pos, $length) ])) {
        $pos += $length;
        return $this -> ignore ?: $string;
      }
  }

  public function code() {
    $code = '';

    foreach ($this -> strings as $length => $strings) {
      if ($length === 1) {
        $substr = '$input[$pos]';
        $pos = '$pos++;';
      } else {
        $substr = 'substr($input, $pos, '.$length.')';
        $pos = '$pos += '.$length.';';
      }

      if (count($strings) === 1) {
        reset($strings);

        $code .= 'if ('.$substr.' === '.( $string = var_export(key($strings), true) ).') {'
              .    $pos
              .    'return '.($this -> ignore ? 'true' : $string).';'
              .  '}';
      } else
        $code .= 'static $strings'.$length.' = '.var_export($strings, true).';'
              .  'if (isset($strings'.$length.'[ '.($this -> ignore ? '' : '$string = ').$substr.' ])) {'
              .    $pos
              .    'return '.($this -> ignore ? 'true' : '$string').';'
              .  '}';
    }

    return $code;
  }
}

class R extends Parser {
  protected $pattern;

  public function __construct($pattern) {
    $this -> pattern = "/^$pattern/ADs";
  }

  public function match($input, &$pos) {
    if (preg_match($this -> pattern, substr($input, $pos), $matches)) {
      $pos += strlen( $string = $matches[0] );
      return $string;
    }
  }

  public function code() {
    return 'if (preg_match('.var_export($this -> pattern, true).', substr($input, $pos), $matches)) {'
         .   '$pos += strlen( $string = $matches[0] );'
         .   'return $string;'
         . '}';
  }
}

class Opt extends Parser {
  protected $parser;

  public function __construct($parser) {
    $this -> parser = $parser;
  }

  public function match($input, &$pos) {
    return ( $match = $this -> parser -> match($input, $pos) ) === null ? true : $match;
  }

  public function code() {
    return 'return ( $match = '.$this -> parser.' ) === null ? true : $match;';
  }
}

class OptE extends Opt {
  public function match($input, &$pos) {
    return ( $match = $this -> parser -> match($input, $pos) ) === null || $match === true ? false : $match;
  }

  public function code() {
    return 'return ( $match = '.$this -> parser.' ) === null || $match === true ? false : $match;';
  }
}

abstract class Comb extends Parser {
  protected $parsers;

  public function __construct($parsers) {
    $args = func_get_args();

    $this -> parsers = isset($args[1]) ? $args : $parsers;
  }
}

class CombAnd extends Comb {
  const OPTIONAL = false;

  public function match($input, &$pos) {
    $matches = [];
    $save = $pos;

    foreach ($this -> parsers as $name => $parser) {
      if (( $match = $parser -> match($input, $pos) ) === null) {
        $pos = $save;
        return static::OPTIONAL ? true : null;
      }

      if ($match !== true)
        if (!is_int($name))
          $matches[$name] = $match;
        else if (is_array($match))
          $matches += $match;
    }

    return $matches ?: true;
  }

  public function code() {
    $code = '$matches = [];'
          . '$save = $pos;';

    foreach ($this -> parsers as $name => $parser)
      $code .= 'if (( $match = '.$parser.' ) === null) {'
            .    '$pos = $save;'
            .    'return'.(static::OPTIONAL ? ' true' : '').';'
            .  '}'
            .  'if ($match !== true)'
            .    (is_int($name) ? 'is_array($match) AND $matches += $match;' : '$matches['.var_export($name, true).'] = $match;');

    $code .= 'return $matches ?: true;';

    return $code;
  }
}

class OptAnd extends CombAnd {
  const OPTIONAL = true;
}

class CombOr extends Comb {
  const OPTIONAL = false;

  public function match($input, &$pos) {
    foreach ($this -> parsers as $name => $parser)
      if (( $match = $parser -> match($input, $pos) ) !== null)
        return $match === true ? true : (is_int($name) ? (is_array($match) ? $match : true) : [$name => $match]);

    return static::OPTIONAL ? true : null;
  }

  public function code() {
    $code = '';

    foreach ($this -> parsers as $name => $parser)
      $code .= 'if (( $match = '.$parser.' ) !== null)'
            .    'return $match === true ? true : '.(is_int($name) ? '(is_array($match) ? $match : true);' : '['.var_export($name, true).' => $match];');

    static::OPTIONAL AND $code .= 'return true;';

    return $code;
  }
}

class OptOr extends CombOr {
  const OPTIONAL = true;
}

class Star extends Parser {
  const PLUS = false;

  protected $parser;

  public function __construct($parser) {
    $this -> parser = $parser;
  }

  public function match($input, &$pos) {
    $parser = $this -> parser;

    if (static::PLUS) {
      if (( $match = $parser -> match($input, $pos) ) === null)
        return;

      $matches = [];
      $match === true OR $matches[] = $match;
    } else
      $matches = [];

    while (( $match = $parser -> match($input, $pos) ) !== null)
      $match === true OR $matches[] = $match;

    return $matches ?: true;
  }

  public function code() {
    $parser = "$this->parser";

    return (static::PLUS
         ? 'if (( $match = '.$parser.' ) === null)'
         .   'return;'
         . '$matches = [];'
         . '$match === true OR $matches[] = $match;'
         : '$matches = [];'
         )
         . 'while (( $match = '.$parser.' ) !== null)'
         .   '$match === true OR $matches[] = $match;'
         . 'return $matches ?: true;';
  }
}

class Plus extends Star {
  const PLUS = true;
}

class ListStar extends Parser {
  const PLUS = false;

  protected $parser_main;
  protected $parser_delim;

  public function __construct($parser_main, $parser_delim) {
    $this -> parser_main = $parser_main;
    $this -> parser_delim = $parser_delim;
  }

  public function match($input, &$pos) {
    $parser_main = $this -> parser_main;

    if (( $match = $parser_main -> match($input, $pos) ) === null)
      return static::PLUS ? true : null;

    $parser_delim = $this -> parser_delim;

    $matches = [];
    $match === true OR $matches[] = $match;

    start:

    $save = $pos;

    if ($parser_delim -> match($input, $pos) === null)
      goto end;

    if (( $match = $parser_main -> match($input, $pos) ) === null) {
      $pos = $save;
      goto end;
    }

    $match === true OR $matches[] = $match;
    goto start;

    end:
    return $matches ?: true;
  }

  public function code() {
    $parser_main = "$this->parser_main";

    return 'if (( $match = '.$parser_main.' ) === null)'
         .   'return'.(static::PLUS ? ' true' : '').';'
         . '$matches = [];'
         . '$match === true OR $matches[] = $match;'
         . 'start:'
         . '$save = $pos;'
         . 'if ('.$this -> parser_delim.' === null)'
         .   'goto end;'
         . 'if (( $match = '.$parser_main.' ) === null) {'
         .   '$pos = $save;'
         .   'goto end;'
         . '}'
         . '$match === true OR $matches[] = $match;'
         . 'goto start;'
         . 'end:'
         . 'return $matches ?: true;';
  }
}

class ListPlus extends ListStar {
  const PLUS = true;
}

class Operands extends Parser {
  protected $parser;
  protected $groups = [];
  protected $strings = [];

  public function __construct($parser, $groups) {
    $this -> parser = $parser;

    foreach ($groups as $group => $strings) {
      $this -> groups[] = $group;

      foreach ((array) $strings as $string)
        $this -> strings[strlen($string)][$string] = $group;
    }

    krsort($this -> strings, SORT_NUMERIC);
  }

  public function match($input, &$pos) {
    $parser = $this -> parser;

    if (( $init = $parser -> match($input, $pos) ) === null)
      return;

    $used = [];
    $matches = [];

    start:

    $save = $pos;

    foreach ($this -> strings as $length => $strings)
      if (isset($strings[ $string = $length === 1 ? $input[$pos] : substr($input, $pos, $length) ])) {
        $group = $strings[$string];
        $pos += $length;
        goto match;
      }

    goto end;

    match:

    if (( $match = $parser -> match($input, $pos) ) === null) {
      $pos = $save;
      goto end;
    }

    $used[$group] = true;
    $matches[] = [$group, $string, $match];
    goto start;

    end:

    if (!$matches)
      return $init;

    $groups = [];
    $index = -1;

    foreach ($this -> groups as $group)
      if (isset($used[$group])) {
        $groups[] = $group;
        $index++;
      }

    $matches = $this -> stack($matches, $groups, $index);
    $matches[-1] = $init;
    return $matches;
  }

  protected function stack($matches, $groups, $index) {
    $last = [];
    $new = [];
    $group = $groups[$index];

    foreach ($matches as $match) {
      if ($last && $match[0] === $group) {
        $index AND $last = $this -> stack($last, $groups, $index - 1);
        $new[] = isset($last[1]) ? $last : $last[0];
        $last = [];
      }

      $last[] = $match;
    }

    if ($last) {
      $index AND $last = $this -> stack($last, $groups, $index - 1);
      $new[] = isset($last[1]) ? $last : $last[0];
    }

    return $new;
  }

  public function code() {
    $parser = "$this->parser";

    $code = 'static $stack;'
          . '$stack === null AND $stack = function($matches, $groups, $index) use (&$stack) {'
          .   '$last = [];'
          .   '$new = [];'
          .   '$group = $groups[$index];'
          .   'foreach ($matches as $match) {'
          .     'if ($last && $match[0] === $group) {'
          .       '$index AND $last = $stack($last, $groups, $index - 1);'
          .       '$new[] = isset($last[1]) ? $last : $last[0];'
          .       '$last = [];'
          .     '}'
          .     '$last[] = $match;'
          .   '}'
          .   'if ($last) {'
          .     '$index AND $last = $stack($last, $groups, $index - 1);'
          .     '$new[] = isset($last[1]) ? $last : $last[0];'
          .   '}'
          .   'return $new;'
          . '};'
          . 'if (( $init = '.$parser.' ) === null)'
          .   'return;'
          . '$used = [];'
          . '$matches = [];'
          . 'start:'
          . '$save = $pos;';

    foreach ($this -> strings as $length => $strings)
      $code .= 'static $strings'.$length.' = '.var_export($strings, true).';'
            .  'if (isset($strings'.$length.'[ $string = '.($length === 1 ? '$input[$pos]' : 'substr($input, $pos, '.$length.')').' ])) {'
            .    '$group = $strings'.$length.'[$string];'
            .    '$pos += '.$length.';'
            .    'goto match;'
            .  '}';

    $code .= 'goto end;'
          .  'match:'
          .  'if (( $match = '.$parser.' ) === null) {'
          .    '$pos = $save;'
          .    'goto end;'
          .  '}'
          .  '$used[$group] = true;'
          .  '$matches[] = [$group, $string, $match];'
          .  'goto start;'
          .  'end:'
          .  'if (!$matches)'
          .    'return $init;'
          .  '$groups = [];'
          .  '$index = -1;';

    foreach ($this -> groups as $group)
      $code .= 'if (isset($used['.( $group = var_export($group, true) ).'])) {'
            .    '$groups[] = '.$group.';'
            .    '$index++;'
            .  '}';

    $code .= '$matches = $stack($matches, $groups, $index);'
          .  '$matches[-1] = $init;'
          .  'return $matches;';

    return $code;
  }
}

class StrEscSeq extends Parser {
  public function match($input, &$pos) {
    static $escape = ['n' => "\n", 'r' => "\r", 't' => "\t", 'v' => "\v", 'f' => "\f"];

    switch ( $anchor = $input[$pos] ) {
      case "'":
      case '"':
        for ($string = '', $index = $pos + 1, $length = strlen($input); $index < $length;)
          switch ( $char = $input[ $index++ ] ) {
            case '\\':
              if ($index === $length)
                return;

              switch ( $char = $input[ $index++ ] ) {
                case '\\':
                case $anchor:
                  $string .= $char;
                  break 2;
              }

              $anchor === '"' AND $string .= isset($escape[$char]) ? $escape[$char] : "\\$char";
              break;

            case $anchor:
              $pos = $index;
              return $string;

            default:
              $string .= $char;
          }
    }
  }

  public function code() {
    return 'static $escape = [\'n\' => "\\n", \'r\' => "\\r", \'t\' => "\\t", \'v\' => "\\v", \'f\' => "\\f"];'
         . 'switch ( $anchor = $input[$pos] ) {'
         .   'case "\'":'
         .   "case '\"':"
         .     'for ($string = \'\', $index = $pos + 1, $length = $this -> length; $index < $length;)'
         .       'switch ( $char = $input[ $index++ ] ) {'
         .         "case '\\\\':"
         .           'if ($index === $length)'
         .             'return;'
         .           'switch ( $char = $input[ $index++ ] ) {'
         .             "case '\\\\':"
         .             'case $anchor:'
         .               '$string .= $char;'
         .               'break 2;'
         .           '}'
         .           '$anchor === \'"\' AND $string .= isset($escape[$char]) ? $escape[$char] : "\\\\$char";'
         .           'break;'
         .         'case $anchor:'
         .           '$pos = $index;'
         .           'return $string;'
         .         'default:'
         .           '$string .= $char;'
         .       '}'
         . '}';
  }
}