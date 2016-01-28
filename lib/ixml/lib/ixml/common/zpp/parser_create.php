<?php
namespace Zeyon\Zpp\ParserProto;

include_once(__DIR__.'/parser_proto.php');

$d  = new C('.');
$c  = new C(',');
$cl = new C(':');
$sc = new C(';');
$q  = new C('?');
$po = new C('(');
$pc = new C(')');
$bo = new C('[');
$bc = new C(']');
$co = new C('{');
$cc = new C('}');

$num  = new R('[+-]?((?:\d*\.\d+|\d+\.\d*)(?:[eE][+-]?\d+)?|[1-9]\d*|0(?:[xX][a-fA-F\d]+|b[01]+)?)');
$str  = new StrEscSeq;

$name      = new R('[a-zA-Z_]\w*');
$name_cl_o = new OptAnd(['NAME' => $name, $cl]);

$stat   = new Wrap;
$exp    = new Wrap;
$exp_o  = new Opt($exp);
$exp_e  = new OptE($exp);

$code  = new Star($stat);
$blk   = new CombAnd($co, $code, $cc);
$blk_e = new CombAnd($co, new OptE($code), $cc);
$enc   = new CombAnd($po, $exp, $pc);

$key = new CombOr(new CombAnd([$d, 'NAME' => $name]), new CombAnd([$bo, 'EXP' => $exp_e, $bc]));
$var = new CombAnd(['SCOPE' => new Ss(['$', '@']), 'VAR' => $name, 'KEYS' => new Star($key)]);

$else_o = new OptAnd([new S('else'), 'ELSE' => $stat]);

$if = new CombAnd([new S('if'), 'IF' => $enc, 'STAT' => $stat, $else_o]);

$case   = new Star(new CombAnd(new CombOr(new CombAnd([new S('case'), 'CASE' => $exp]), new S('default')), new CombOr(['CODE' => new CombAnd($cl, $code), 'BLK' => $blk_e])));
$switch = new CombAnd([new S('switch'), 'SWITCH' => $enc, $co, 'CASE' => $case, $cc]);

$while   = new CombAnd(new S('while'), $enc);
$foreach = new CombAnd([new S('foreach'), $po, 'FOREACH' => $exp, new OptAnd([$c, 'KEY' => new OptAnd($var, $cl), 'VAL' => $var]), $pc]);
$for     = new CombAnd([new S('for'), $po, 'INIT' => $exp_o, $sc, 'FOR' => $exp_e, $sc, 'REP' => $exp_o, $pc]);
$loop    = new CombAnd([new CombOr(['WHILE' => $while, $foreach, $for]), 'STAT' => $stat]);

$do = new CombAnd([new S('do'), 'STAT' => $stat, 'DO' => $while, $sc]);

$catch_o   = new OptAnd([new S('catch'), new OptAnd($po, new Opt($var), $pc), 'CATCH' => $stat]);
$finally_o = new OptAnd([new S('finally'), 'FINALLY' => $stat]);
$try       = new CombAnd([new S('try'), 'TRY' => $stat, $catch_o, $else_o, $finally_o]);

$del = new CombAnd([new S('delete'), 'DEL' => new ListStar($var, $c), $sc]);

$jmp = new CombAnd(['JMP' => new Ss(['break', 'continue']), 'LEAP' => new Opt($num), $sc]);

$misc = new CombAnd(['MISC' => new Ss(['echo', 'expand', 'throw']), $exp, $sc]);

$ret = new Ss(['exit', 'return']);

$stat -> parser = new CombOr(['BLK' => $blk, $if, $switch, $loop, $do, $try, $del, $jmp, $misc, new CombAnd(['RET' => new Opt($ret), $exp_o, $sc])]);

$incdec = new Ss(['++', '--']);

$pre = new CombAnd(['PRE' => $incdec, $var]);

$param = new CombAnd([$po, 'PARAM' => new OptE(new ListStar(new CombAnd($name_cl_o, $exp), $c)), $pc]);

$nat = new CombAnd(['NAT' => $name, $param]);

$post = new CombAnd($var, new OptOr(['POST' => $incdec, $param, new CombAnd(new S('->'), $nat)]));

$new = new CombAnd([new S('new'), 'NEW' => $var, $param]);

$arg_o = new OptAnd($po, new ListStar(new CombAnd(['NAME' => $name, 'EXP' => new OptAnd($cl, $exp)]), $c), $pc);

$use_o = new OptAnd(new S('use'), $po, new ListStar(new CombAnd($name_cl_o, $var), $c), $pc);
$func  = new CombAnd([new S('function'), 'ARG' => $arg_o, 'USE' => $use_o, 'FUNC' => $blk_e, new Opt($param)]);

$macro = new CombAnd([new S('macro'), 'MACRO' => $blk_e]);

$extends_o = new OptAnd($po, new ListStar($exp, $c), $pc);
$member    = new Star(new CombAnd(['NAME' => $name, new CombOr(new CombAnd([$cl, 'EXP' => $exp, $sc]), new CombAnd(['ARG' => $arg_o, 'FUNC' => $blk_e]))]));
$class     = new CombAnd([new S('class'), 'EXTENDS' => $extends_o, $co, 'CLASS' => new OptE($member), $cc]);

$const = new Ss(['null', 'true', 'false']);

$array = new CombAnd([$bo, 'ARRAY' => new OptE(new ListStar(new CombAnd([new OptAnd(new CombOr(['NUM' => $num, 'STR' => $str, 'NAME' => $name]), $cl), 'EXP' => $exp]), $c)), $bc]);

$cast  = new CombAnd([new C('<'), 'CAST' => new Ss(['bool', 'int', 'float', 'string', 'array']), new C('>')]);
$unary = new CombOr(['UNARY' => new Ss(['+', '-', '~', '!', 'clone']), $cast]);

$simple = new Wrap;
$simple -> parser = new CombOr([$enc, $pre, $post, $new, $func, $macro, $class, 'CONST' => $const, 'NUM' => $num, 'STR' => $str, $array, $nat, new CombAnd([$unary, 'SIMPLE' => $simple])]);

$operands = new Operands($simple, [
  'MUL' => ['*', '/', '%'],
  'ADD' => ['+', '-', '\\'],
  'SH' => ['<<', '>>'],
  'CMP' => ['<', '>', '<=', '>='],
  'EQ' => ['===', '!==', '==', '!=', '=*', '!*', '=~', '!~', '=_', '!_', '=_*', '!_*', '=^', '!^', '=^*', '!^*', '=$', '!$', '=$^', '!$^'],
  'AND' => '&',
  'XOR' => '^',
  'OR' => '|',
  'LAND' => '&&',
  'LOR' => '||'
]);

$assign = new CombAnd([new Opt($unary), $var, 'ASSIGN' => new Ss(['=', '*=', '/=', '+=', '-=', '\\=', '<<=', '>>=', '&=', '^=', '|=']), 'EXP' => $exp]);

$cond = new Wrap;
$cond -> parser = new CombAnd(['COND' => $operands, new OptAnd([$q, 'EXPT' => new Opt($operands), $cl, 'EXPF' => $cond])]);

$exp -> parser = new CombOr($assign, $cond);

file_put_contents(__DIR__.'/parser.php', "<?php\nnamespace Zeyon\Zpp;\n".$code -> createParser('Parser'));