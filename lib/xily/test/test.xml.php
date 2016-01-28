<?php

header('Content-type: text/plain; charset=UTF-8');

include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'xily'.DIRECTORY_SEPARATOR.'xily.config.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'xily'.DIRECTORY_SEPARATOR.'xily.base.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'xily'.DIRECTORY_SEPARATOR.'xily.xml.php';

$data = Xily\Xml::create(dirname(__FILE__).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'library.xml', 1);

echo $data->toString();

?>