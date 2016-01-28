<?php
require '../lib/ixml/base.php';

set_time_limit(0);

ini_set('default_mimetype', 'text/plain');

class TestSandbox extends iXml\Sandbox {
  protected function sandbox() {
    $t1 = microtime(true);

    $ixml = new iXml\iXml(file_get_contents('./'.basename($_SERVER['SCRIPT_FILENAME'], '.php').'.xml'));

    $t2 = microtime(true);

    if ($ixml -> root)
      $ixml -> exec();

    $t3 = microtime(true);

    if (isset($_GET['metrics']))
      echo "\n======= METRICS =======\n",
           'Compile Time   : ', number_format($t2 - $t1, 3), "s\n",
           'Execution Time : ', number_format($t3 - $t2, 3), "s\n",
           "-----------------------\n",
           'Total Time     : ', number_format($t3 - $t1, 3), "s\n",
           "-----------------------\n",
           'Memory Usage   : ', number_format(memory_get_peak_usage(true) / 1024 / 1024, 1), ' MB';
  }
}

(new TestSandbox) -> run();