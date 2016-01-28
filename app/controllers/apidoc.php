<?php

class ApidocController extends Controller {

	public function __construct($locale) {
		include LIB_DIR.'docbook/src/docbook.core.php';
		include LIB_DIR.'docbook/src/docbook.html.php';
		include LIB_DIR.'docbook/src/docbook.srcparser.php';
		include LIB_DIR.'docbook/src/docbook.apidoc.php';

		$doc = new \Docbook\APIdoc('res/docs/index.xml', 1);
		$doc->setSCRPath(API_DIR);
		$doc->setImgURL('res/docs/img/');
		$doc->setDemoURL(\Xily\Config::get('app.url').'index.php');
		// $doc->addInclude('<link media="all" rel="stylesheet" type="text/css" href="./assets/lib/bootstrap/css/bootstrap.min.css" />');
		// $doc->addInclude('<link media="all" rel="stylesheet" type="text/css" href="./assets/lib/bootstrap/css/bootstrap-responsive.min.css" />');
		$doc->addInclude('<script type="text/javascript" src="./assets/lib/mootools/mootools-core-1.4.4.js"></script>');
		$doc->addInclude('<script type="text/javascript" src="./assets/lib/mootools/mootools-more-1.4.0.1.js"></script>');

		$data = new \Xily\Dictionary($doc->parse());

		/*
		$toc = '<ul class="nav nav-list">';
		foreach ($data->get('sections') as $key => $value) {
			$toc .= '<li class="lev'.substr_count($key, '.').'"><a href="#sect'.$key.'" style="padding-left: '.(substr_count($key, '.') * 10).'px">'.$value.'</a></li>';
		}
		$toc .= '</ul>';
		$data->set('toc', $toc);
		$data->set('css', file_get_contents('lib/docbook/html/apidoc.dyn.css'));
		*/

		die($data->insertInto(file_get_contents('lib/docbook/html/apidoc.frame.html')));
	}

}
?>