<?php
namespace Zeyon\Zpp;

class SystemException extends \Exception {}

class FatalException extends SystemException {}

class UserException extends \Exception {
  public $value;

  public function __construct($value) {
    $this -> value = $value;
  }
}

class jEXT extends SystemException {
  public $result;

  public function __construct($result = null) {
    $this -> result = $result;
  }
}

class jRET extends jEXT {}

class jCNT extends SystemException {
  protected $leap;

  public function __construct($leap = 1) {
    $this -> leap = $leap;
  }
}

class jBRK extends jCNT {}

interface tComplex {
  public function __toString();
}

class tArray implements tComplex {
  public $array;

  public function __construct($array = []) {
    $this -> array = $array;
  }

  public function __toString() {
    return 'array';
  }
}

class tObject extends tArray {
  public $class;

  public function __construct($array, $class) {
    $this -> array = $array;
    $this -> class = $class;
  }

  public function __toString() {
    return 'object';
  }
}

class tClass extends tArray {
  public $parents;

  public function __construct($array, $parents) {
    $this -> array = $array;
    $this -> parents = $parents;
  }

  public function __toString() {
    return 'class';
  }
}

class tMacro implements tComplex {
  public $zcode;

  public function __construct($zcode) {
    $this -> zcode = $zcode;
  }

  public function __toString() {
    return 'macro';
  }
}

abstract class tFunction implements tComplex {
  public function __toString() {
    return 'function';
  }
}

class tFunctionSimple extends tFunction {
  public $zcode;
  public $args = [];

  public function __construct($zcode) {
    $this -> zcode = $zcode;
  }
}

class tFunctionClosure extends tFunction {
  protected $ref = [];

  public function __construct($zcode, $args) {
    $this -> zcode = $zcode;
    $this -> args = $args;

    foreach ($args as &$value)
      $this -> ref[] =& $value; // Preserve references
  }
}

interface tBinding {
  public function call($params);
}

class Interpreter {
  public $g;
  public $l;
  public $i;

  public function exec($zcode, $global = [], $local = []) {
    $this -> g =& $global;
    $this -> l =& $local;
    $i =& $this -> i;

    try {
      for ($i = 0, $s = [], $p = -1; isset($zcode[$i]);) {
        $z = $zcode[ $i++ ];
        $this -> $z[0]($s, $p, $z);
      }
    } catch (jEXT $j) {
      return $j -> result;
    }
  }

  // Null
  protected function _N(&$s, &$p) {
    $s[ ++$p ] = null;
  }

  // True
  protected function _T(&$s, &$p) {
    $s[ ++$p ] = true;
  }

  // False
  protected function _F(&$s, &$p) {
    $s[ ++$p ] = false;
  }

  // Zero
  protected function _Z(&$s, &$p) {
    $s[ ++$p ] = 0;
  }

  // Empty string
  protected function _E(&$s, &$p) {
    $s[ ++$p ] = '';
  }

  // Literal
  protected function _L(&$s, &$p, $z) { // literal
    $s[ ++$p ] = $z[1];
  }

  // Static array
  protected function _A(&$s, &$p, $z) { // array
    $s[ ++$p ] = new tArray($z[1]);
  }

  // Dynamic array
  protected function _AD(&$s, &$p, $z) { // array, keys
    $array = $z[1];

    foreach ($z[2] as $key) {
      $array[$key] = $s[$p];
      unset($s[ $p-- ]);
    }

    $s[ ++$p ] = new tArray($array);
  }

  // Global variable value
  protected function _VG(&$s, &$p, $z) { // name
    $g = $this -> g;
    $s[ ++$p ] = isset($g[ $name = $z[1] ]) ? $g[$name] : null;
  }

  // Global variable value with keys
  protected function _VGK(&$s, &$p, $z) { // name, keys

  }

  // Local variable value
  protected function _VL(&$s, &$p, $z) { // name
  }

  // Local variable value with keys
  protected function _VLK(&$s, &$p, $z) { // name, keys

  }

  // Global variable reference
  protected function _RG(&$s, &$p, $z) { // name
    $s[ ++$p ] =& $this -> g[$z[1]];
  }

  // Global variable reference with keys
  protected function _RGK(&$s, &$p) { // name, keys
  }

  // Local variable reference
  protected function _RL(&$s, &$p) { // name
  }

  // Local variable reference with keys
  protected function _RLK(&$s, &$p) { // name, keys

  }

  // Global variable with keys and parent ('this')
  protected function _TG(&$s, &$p, $z) { // name, keys

  }

  // Local variable with keys and parent ('this')
  protected function _TL(&$s, &$p, $z) { // name, keys

  }

  // Delete global variable
  protected function _DG(&$s, &$p, $z) { // name

  }

  // Delete global variable with keys
  protected function _DGK(&$s, &$p, $z) { // name, keys

  }

  // Delete local variable
  protected function _DL(&$s, &$p, $z) { // name

  }

  // Delete local variable with keys
  protected function _DLK(&$s, &$p, $z) { // name, keys

  }

  // Unary plus
  protected function _PLU(&$s, &$p) {
    $top = ( $top =& $s[$p] ) instanceof tComplex ? 0 : +$top;
  }

  // Unary minus
  protected function _MIN(&$s, &$p) {
    $top = ( $top =& $s[$p] ) instanceof tComplex ? 0 : -$top;
  }

  // Bitwise negation
  protected function _NEG(&$s, &$p) {
    $top = ( $top =& $s[$p] ) instanceof tComplex ? -1 : ~$top;
  }

  // Logical complement
  protected function _NOT(&$s, &$p) {
    $top = ! $top =& $s[$p];
  }

  // Clone
  protected function _CLN(&$s, &$p) {
    ( $top =& $s[$p] ) instanceof tComplex AND $top = clone $top;
  }

  // Cast to bool
  protected function _CTB(&$s, &$p) {
    $top = (bool) $top =& $s[$p];
  }

  // Cast to int
  protected function _CTI(&$s, &$p) {
  }

  // Cast to float
  protected function _CTF(&$s, &$p) {
  }

  // Cast to array
  protected function _CTA(&$s, &$p) {

  }

  // Pre-increment
  protected function _INC(&$s, &$p) {
    $top = ( $top =& $s[$p] ) instanceof tComplex ? 1 : $top + 1;
  }

  // Pre-increment and pop stack
  protected function _INC_POP(&$s, &$p) {
    $top = ( $top =& $s[$p] ) instanceof tComplex ? 1 : $top + 1;
    unset($s[ $p-- ]);
  }

  // Post-increment
  protected function _INCP(&$s, &$p) {
  }

  // Post-increment and pop stack
  protected function _INCP_POP(&$s, &$p) {
  }

  // Pre-decrement
  protected function _DEC(&$s, &$p) {
    $top = ( $top =& $s[$p] ) instanceof tComplex ? -1 : $top - 1;
  }

  // Pre-decrement and pop stack
  protected function _DEC_POP(&$s, &$p) {
    $top = ( $top =& $s[$p] ) instanceof tComplex ? -1 : $top - 1;
    unset($s[ $p-- ]);
  }

  // Post-decrement
  protected function _DECP(&$s, &$p) {
  }

  // Post-decrement and pop stack
  protected function _DECP_POP(&$s, &$p) {
  }

  // Assignment
  protected function _ASGN(&$s, &$p) {
    $s[$p - 1] = $s[$p];
    unset($s[ $p-- ]);
  }

  // Assignment and pop stack
  protected function _ASGN_POP(&$s, &$p) {
    $s[$p - 1] = $s[$p];
    unset($s[ $p-- ], $s[ $p-- ]);
  }

  // Arithmetic addition
  protected function _ADD(&$s, &$p) {
    ( $last = $s[$p] ) instanceof tComplex AND $last = 0;

    if (( $first =& $s[$p - 1] ) instanceof tComplex)
      $first = $last;
    else
      $first += $last;

    unset($s[ $p-- ]);
  }

  // Arithmetic subtraction
  protected function _SUB(&$s, &$p) {
    ( $last = $s[$p] ) instanceof tComplex AND $last = 0;

    if (( $first =& $s[$p - 1] ) instanceof tComplex)
      $first = -$last;
    else
      $first -= $last;

    unset($s[ $p-- ]);
  }

  // Arithmetic multiplication
  protected function _MUL(&$s, &$p) {
    if (( $first =& $s[$p - 1] ) instanceof tComplex || ( $last = $s[$p] ) instanceof tComplex)
      $first = 0;
    else
      $first *= $last;

    unset($s[ $p-- ]);
  }

  // Arithmetic division
  protected function _DIV(&$s, &$p) {
    if (( $first =& $s[$p - 1] ) instanceof tComplex)
      $first = 0;
    else
      $first /= ( $last = $s[$p] ) instanceof tComplex ? 0 : $last;

    unset($s[ $p-- ]);
  }

  // Arithmetic division with remainder
  protected function _MOD(&$s, &$p) {
    if (( $first =& $s[$p - 1] ) instanceof tComplex)
      $first = 0;
    else
      $first %= ( $last = $s[$p] ) instanceof tComplex ? 0 : $last;

    unset($s[ $p-- ]);
  }

  // Bitwise left shift
  protected function _SHL(&$s, &$p) {
    if (( $first =& $s[$p - 1] ) instanceof tComplex)
      $first = 0;
    else
      $first <<= ( $last = $s[$p] ) instanceof tComplex ? 0 : $last;

    unset($s[ $p-- ]);
  }

  // Bitwise right shift
  protected function _SHR(&$s, &$p) {
    if (( $first =& $s[$p - 1] ) instanceof tComplex)
      $first = 0;
    else
      $first >>= ( $last = $s[$p] ) instanceof tComplex ? 0 : $last;

    unset($s[ $p-- ]);
  }

  // Bitwise AND
  protected function _AND(&$s, &$p) {
    if (( $first =& $s[$p - 1] ) instanceof tComplex || ( $last = $s[$p] ) instanceof tComplex)
      $first = 0;
    else
      $first &= $last;

    unset($s[ $p-- ]);
  }

  // Bitwise inclusive OR
  protected function _OR(&$s, &$p) {
    ( $last = $s[$p] ) instanceof tComplex AND $last = 0;

    if (( $first =& $s[$p - 1] ) instanceof tComplex)
      $first = $last;
    else
      $first |= $last;

    unset($s[ $p-- ]);
  }

  // Bitwise exclusive OR
  protected function _XOR(&$s, &$p) {
    ( $last = $s[$p] ) instanceof tComplex AND $last = 0;

    if (( $first =& $s[$p - 1] ) instanceof tComplex)
      $first = $last;
    else
      $first ^= $last;

    unset($s[ $p-- ]);
  }

  // String concatenation
  protected function _CON(&$s, &$p) {
    $s[$p - 1] .= $s[$p];
    unset($s[ $p-- ]);
  }

  // Identity test
  protected function _ID(&$s, &$p) {
    $top = ( $top =& $s[$p - 1] ) === $s[$p];
    unset($s[ $p-- ]);
  }

  // Non-identity test
  protected function _IDN(&$s, &$p) {
    $top = ( $top =& $s[$p - 1] ) !== $s[$p];
    unset($s[ $p-- ]);
  }

  // Equality test
  protected function _EQ(&$s, &$p) {
    $last = $s[$p];
    $first = ( $first =& $s[$p - 1] ) instanceof tComplex || $last instanceof tComplex ? $first === $last : $first == $last;
    unset($s[ $p-- ]);
  }

  // Non-equality test
  protected function _EQN(&$s, &$p) {
    $last = $s[$p];
    $first = ( $first =& $s[$p - 1] ) instanceof tComplex || $last instanceof tComplex ? $first !== $last : $first != $last;
    unset($s[ $p-- ]);
  }

  // Case-insenstive equality test
  protected function _EQI(&$s, &$p) {
    $top = ( $first = (string) $top =& $s[$p - 1] ) === ( $last = "$s[$p]" ) || mb_strtolower($first) === mb_strtolower($last);
    unset($s[ $p-- ]);
  }

  // Case-insenstive non-equality test
  protected function _EQIN(&$s, &$p) {
    $top = ( $first = (string) $top =& $s[$p - 1] ) !== ( $last = "$s[$p]" ) && mb_strtolower($first) !== mb_strtolower($last);
    unset($s[ $p-- ]);
  }

  // Regular expression match test
  protected function _REX(&$s, &$p) {
    $top =& $s[$p - 1];
    $top = (bool) preg_match("$s[$p]", "$top");
    unset($s[ $p-- ]);
  }

  // Regular expression non-match test
  protected function _REXN(&$s, &$p) {
    $top =& $s[$p - 1];
    $top = !preg_match("$s[$p]", "$top");
    unset($s[ $p-- ]);
  }

  // Contains test
  protected function _TC(&$s, &$p) {

  }

  // Not-contains test
  protected function _TCN(&$s, &$p) {

  }

  // Case-insenstive contains test
  protected function _TCI(&$s, &$p) {

  }

  // Case-insenstive not-contains test
  protected function _TCIN(&$s, &$p) {

  }

  // Begins-with test
  protected function _TB(&$s, &$p) {

  }

  // Not-begins-with test
  protected function _TBN(&$s, &$p) {

  }

  // Case-insenstive begins-with test
  protected function _TBI(&$s, &$p) {

  }

  // Case-insenstive not-begins-with test
  protected function _TBIN(&$s, &$p) {

  }

  // Ends-with test
  protected function _TE(&$s, &$p) {

  }

  // Not-ends-with test
  protected function _TEN(&$s, &$p) {

  }

  // Case-insenstive ends-with test
  protected function _TEI(&$s, &$p) {

  }

  // Case-insenstive not-ends-with test
  protected function _TEIN(&$s, &$p) {

  }

  // Less-than comparison
  protected function _LT(&$s, &$p) {
    $first = !( $first =& $s[$p - 1] ) instanceof tComplex && !( $last = $s[$p] ) instanceof tComplex && $first < $last;
    unset($s[ $p-- ]);
  }

  // Less-than comparison and skip
  protected function _LT_SKP(&$s, &$p, $z) {
    !( $first = $s[$p - 1] ) instanceof tComplex && !( $last = $s[$p] ) instanceof tComplex && $first < $last OR $this -> i = $z[1];
    unset($s[ $p-- ]);
  }

  // Less-than or equal comparison
  protected function _LTE(&$s, &$p) {
    $first = !( $first =& $s[$p - 1] ) instanceof tComplex && !( $last = $s[$p] ) instanceof tComplex && $first <= $last;
    unset($s[ $p-- ]);
  }

  // Greater-than comparison
  protected function _GT(&$s, &$p) {
    $first = !( $first =& $s[$p - 1] ) instanceof tComplex && !( $last = $s[$p] ) instanceof tComplex && $first > $last;
    unset($s[ $p-- ]);
  }

  // Greater-than or equal comparison
  protected function _GTE(&$s, &$p) {
    $first = !( $first =& $s[$p - 1] ) instanceof tComplex && !( $last = $s[$p] ) instanceof tComplex && $first >= $last;
    unset($s[ $p-- ]);
  }

  // Pop stack
  protected function _POP(&$s, &$p) {
    unset($s[ $p-- ]);
  }

  // Output
  protected function _OUT(&$s, &$p) {
    echo $s[$p];
    unset($s[ $p-- ]);
  }

  // Exit program
  protected function _EXT() {
    throw new jEXT;
  }

  // Exit program with result
  protected function _EXTR(&$s, &$p) {
    throw new jEXT($s[$p]);
  }

  // Return from function
  protected function _RET() {
    throw new jRET;
  }

  // Return from function with result
  protected function _RETR(&$s, &$p) {
    throw new jRET($s[$p]);
  }

  // Break loop
  protected function _BRK() {
    throw new jBRK;
  }

  // Break loop with leap
  protected function _BRKL(&$s, &$p) {
    throw new jBRK($s[$p]);
  }

  // Continue loop
  protected function _CNT() {
    throw new jCNT;
  }

  // Continue loop with leap
  protected function _CNTL(&$s, &$p) {
    throw new jCNT($s[$p]);
  }

  // Throw user exception
  protected function _ERR(&$s, &$p) {
    throw new UserException($s[$p]);
  }

  // Jump to instruction
  protected function _JMP($s, $p, $z) { // pointer
    $this -> i = $z[1];
  }

  // Skip if false
  protected function _SKP(&$s, &$p, $z) { // pointer
    $s[$p] OR $this -> i = $z[1];
    unset($s[ $p-- ]);
  }

  // Loop
  protected function _LOOP(&$s, &$p, $z) { // zcode, pointer
    $next = $i =& $this -> i;
    list(, $zcode, $loop) = $z;

    try {
      for ($i = 0, $s2 = [], $p2 = -1; isset($zcode[$i]);) {
        $z2 = $zcode[ $i++ ];
        $this -> $z2[0]($s2, $p2, $z2);
      }
    } catch (jCNT $j) {
      if (--$j -> leap)
        throw $j;

      if ($j instanceof jBRK) {
        $i = $next;
        return;
      }
    }

    $i = $loop;
  }

  // For each
  protected function _FOR(&$s, &$p, $z) { // zcode
    if (( $array = $s[$p] ) instanceof tArray AND $array = $array -> array ) {
      $next = $i =& $this -> i;
      $zcode = $z[1];

      foreach ($array as $value)
        try {
          for ($i = 0, $s2 = [], $p2 = -1; isset($zcode[$i]);) {
            $z2 = $zcode[ $i++ ];
            $this -> $z2[0]($s2, $p2, $z2);
          }
        } catch (jCNT $j) {
          if (--$j -> leap)
            throw $j;

          if ($j instanceof jBRK)
            break;
        }

      $i = $next;
    }

    unset($s[ $p-- ]);
  }

  // For each with value
  protected function _FORV(&$s, &$p, $z) { // zcode
    unset($s[ $p-- ], $s[ $p-- ]);
  }

  // For each with key and value
  protected function _FORKV(&$s, &$p, $z) { // zcode

    unset($s[ $p-- ], $s[ $p-- ], $s[ $p-- ]);
  }

  // Expand macro
  protected function _EXP(&$s, &$p) {
    if (!( $macro = $s[$p] ) instanceof tMacro)
      throw FatalException('Expandable value is not a macro');

    unset($s[ $p-- ]);
  }

  // Call function
  protected function _CLL(&$s, &$p, $z) { // params
    if (!( $function = $s[$p] ) instanceof tFunction)
      throw FatalException('Callable value is not a function');

    // $params = ...;

    try {
      $s[ ++$p ] = null;
    } catch (jRET $j) {
      $s[ ++$p ] = $j -> result;
    } catch (jCNT $j) {
      throw new FatalException('Cannot use break/continue outside of loop context');
    }
  }

  // Call function with reference to parent ('this')
  protected function _CLLT(&$s, &$p, $z) { // params
  }

  // New object
  protected function _NEW() {

  }

  // Native operation
  protected function _NAT(&$s, &$p, $z) { // name, params
    if (!method_exists($this, $name = $z[1] )) // Auslagerung in Schemavariable mit benamten Argumenten
      throw FatalException("Native operation '$name' does not exist");

    // $params = ...;

    $s[ ++$p ] = $this -> $name($params);
  }

  // Macro definition
  protected function _MCRO(&$s, &$p, $z) {

  }

  // Simple function definition
  protected function _FUNC(&$s, &$p, $z) {

  }

  // Closure function definition
  protected function _CLSR(&$s, &$p, $z) {

  }

  // Class definition
  protected function _CLASS(&$s, &$p, $z) {

  }

  // Exception handling
  protected function _TRY(&$s, &$p, $z) {

  }
}