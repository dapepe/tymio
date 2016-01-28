<?php
namespace Zeyon\Zpp;

include_once(__DIR__.'/parser.php');

class ParserException extends \Exception {
  public $input;
  public $pos;

  public function __construct($input, $pos) {
    parent::__construct("z++ parser failed at '".substr( $this -> input = $input , $this -> pos = $pos , 100)." ...'");
  }
}

class Compiler {
  public function compile($input) {
    list($ast, $pos, $length) = (new Parser) -> parse( $input = self::removeExcess($input) );

    if ($pos < strlen($input))
      throw new zParserException($input, $pos);

    return $this -> code($ast);
  }

  public static function removeExcess($string) {
    static $whites = [' ' => true, "\n" => true, "\r" => true, "\t" => true];

    $new = '';

    for ($pos = 0, $length = strlen($string); $pos < $length;)
      switch ( $anchor = $string[ $pos++ ] ) {
        case "'":
        case '"':
          $new .= $anchor;

          while ($pos < $length) {
            $new .= $char = $string[ $pos++ ];

            switch ($char) {
              case '\\':
                if ($pos === $length)
                  return $new;

                $new .= $string[ $pos++ ];
                break;

              case $anchor:
                break 2;
            }
          }

          break;

        case '/':
          if ($pos === $length)
            return $new.$anchor;

          switch ( $char = $string[ $pos++ ] ) {
            case '/':
              $pos = strpos($string, "\n", $pos) + 1;
              break;

            case '*':
              $pos = strpos($string, '*/', $pos) + 2;
              break;

            default:
              $new .= $anchor.$char;
          }

          break;

        default:
          isset($whites[$anchor]) OR $new .= $anchor;
    }

    return $new;
  }
}