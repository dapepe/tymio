<?php
namespace iXml;

require_once __DIR__.'/common/std.php';

\Zeyon\loadCommon('ixml');

class iXml extends \Zeyon\iXml {}

// -------------------- Implementation --------------------

abstract class Sandbox {
  abstract protected function sandbox();

  final public function run() {
    set_error_handler('Zeyon\handleError');

    $ini = [
      'html_errors' => 0,
      'mbstring.http_output' => 'pass',
      'mbstring.internal_encoding' => 'UTF-8',
      'mbstring.substitute_character' => 'none',
      'precision' => 14
    ];

    foreach ($ini as $name => $value)
      $ini[$name] = ini_set($name, $value);

    $e = null;

    try {
      $this -> sandbox();
    } catch (\Exception $e) {}

    foreach ($ini as $name => $value)
      ini_set($name, $value);

    restore_error_handler();

    if ($e)
      throw $e;
  }
}

class SimpleSandbox extends Sandbox {
  protected $code;
  protected $vars;

  public function __construct($code, $vars = []) {
    $this -> code = $code;
    $this -> vars = $vars;
  }

  protected function sandbox() {
    (new iXml($this -> code)) -> exec($this -> vars);
  }
}