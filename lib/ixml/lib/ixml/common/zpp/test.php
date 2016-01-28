<?php
namespace Zeyon\Zpp;

include_once(__DIR__.'/compiler.php');
include_once(__DIR__.'/interpreter.php');

header('Content-type: text/plain');

ini_set('max_execution_time', 5);

$parser = new Parser;
$interpreter = new Interpreter;

//print_r($parser -> parse(zCompiler::removeExcess('if($test){} else $x = [123, key: "hello"+12/5];')));
//exit;

$zcode_array = [ // Remember to reverse within compiler
  ['_T'],
  ['_L', 127],
  ['_AD', ['test1' => 'gestezt', 2 => null, 'test3' => null], [2, 'test3']],
  ['_EXTR']
];

$zcode_loop = [
  ['_RG', 'i'],
  ['_Z'],
  ['_ASGN_POP'],

  ['_VG', 'i'],
  ['_L', 100],
  ['_LT_SKP', 7],

  ['_LOOP', [
    ['_RG', 'i'],
    ['_INC']
  ], 3]
];

$zcode_foreach = [
  ['_RG', 'a'],
  ['_A', range(0, 100)],
  ['_ASGN_POP'],

  ['_VG', 'a'],
  ['_FOR', $zcode_loop]
];

/*
Für alle artihmetischen/bitwise operatoren:
ADD_POP

FÜR alle vergleichsoperatoren:
EQ_SKP

- POP am Ende eines Codeblocks automatisch entfernen
- POP und SKP mit möglichen funktionen mergen
*/

$benchmark = microtime(true);

var_dump($interpreter -> exec($zcode_foreach));

echo "\n", number_format(microtime(true) - $benchmark, 5), " - ", memory_get_peak_usage(true), "\n";