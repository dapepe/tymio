<?php
namespace Zeyon;

// -------------------- Implementation --------------------

class Xml {
  public $root;

  protected $index;

  public function __construct($root = []) {
    $this -> root = $root;
  }

  public function __toString() {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';

    if ($this -> root)
      try {
        $xml .= "\n".$this -> createTag($this -> root);
      } catch (\Exception $e) {}

    return $xml;
  }

  public function parse($string) {
    $this -> root = [];

    if (( $string = trim($string) ) === '')
      return;

    $this -> index = -1;

    $parser = xml_parser_create();

    $e = null;

    try {
      xml_set_object($parser, $this);
      xml_set_element_handler($parser, 'startTagCallback', 'endTagCallback');
      xml_set_character_data_handler($parser, 'cdataCallback');

      xml_parse($parser, $string, true) ||
      $e = new \Exception('XML ['.(xml_get_current_line_number($parser) + 1).']: '.xml_error_string(xml_get_error_code($parser)));
    } catch (\Exception $e) {}

    xml_parser_free($parser);

    if ($e)
      throw $e;

    return $this -> root;
  }

  protected function createTag($elem) {
  	$name = $elem['name'];

    $head = "<$name";

    if (isset($elem['attr']))
      foreach ($elem['attr'] as $attr_name => $value)
        $head .= " $attr_name=\"".encodeXml($value).'"';

    $content = '';

    if (isset($elem['nodes']))
      foreach ($elem['nodes'] as $node)
        $content .= is_array($node) ? $this -> createTag($node) : encodeXml($node);
    else if (isset($elem['cdata']))
      $content = $elem['cdata'];

    return $head.($content === '' ? '/>' : ">$content</$name>");
  }

  protected function startTagCallback($parser, $name, $attr) {
  	$elem = ['name' => $name];
  	$attr AND $elem['attr'] = $attr;
    $this -> root[ ++$this -> index ] = $elem;
  }

  protected function endTagCallback($parser, $name) {
    $root =& $this -> root;

  	$elem = $root[ $index =& $this -> index ];

  	if ($index) {
  	  unset($root[ $index-- ]);

  	  $parent =& $root[$index];
  	  $parent['nodes'][] = $elem;
  	  $parent['child'][] = $elem;
  	  $parent['elems'][$name][] = $elem;
  	} else
  	  $root = $elem;
  }

  protected function cdataCallback($parser, $cdatapart) {
    $elem =& $this -> root[$this -> index];
    $elem['nodes'][] = $cdatapart;
    $cdata =& $elem['cdata'];
    $cdata .= $cdatapart;
  }
}