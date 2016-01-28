<?php

header('Content-type: text/plain; charset=UTF-8');

include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'xily'.DIRECTORY_SEPARATOR.'xily.config.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'xily'.DIRECTORY_SEPARATOR.'xily.base.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'xily'.DIRECTORY_SEPARATOR.'xily.dict.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'xily'.DIRECTORY_SEPARATOR.'xily.xml.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'xily'.DIRECTORY_SEPARATOR.'xily.bean.php';

Xily\Bean::$BEAN_DIRS = array(
	dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'xily'.DIRECTORY_SEPARATOR.'beans',
	dirname(__FILE__).DIRECTORY_SEPARATOR.'beans',
);

$data = Xily\Bean::create(dirname(__FILE__).DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'mygui.xml', 1);
echo $data->run();

?>