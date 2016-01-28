<?php

class AssetController {
	function __construct($locale) {
		if (!isset($_REQUEST['src'])) {
			header('HTTP/1.1 404 Not Found');
			die('404 - No asset selected');
		}
		
		$src = $_REQUEST['src'];
		if (substr($src, 0, 1) == '/')
			$src = substr($src, 1);
		
		if (strpos($src, '../')) {
			header('HTTP/1.1 404 Not Found');
			die('404 - Relative path not allowed!');
		}
		
		// Attach the default asset directory and give it one more try
		if (!file_exists(ASSET_DIR.$src))
			$src = 'assets/'.$src;
			
		$src = ASSET_DIR.$src;
			
		if (!file_exists($src)) {
			header('HTTP/1.1 404 Not Found');
			die('404 - Not found');
		}
		
		$info = pathinfo($src);
		
		$info['extension'] = strtolower($info['extension']);
		switch ($info['extension']) {
		      case 'pdf':	$ct='application/pdf'; break; 
		      case 'exe':	$ct='application/octet-stream'; break; 
		      case 'zip':	$ct='application/zip'; break;
		      // MS Office
		      case 'docx': 
		      case 'doc':	$ct='application/msword'; break;
		      case 'xlsx': 
		      case 'xls':	$ct='application/vnd.ms-excel'; break; 
		      case 'ppt':	$ct='application/vnd.ms-powerpoint'; break;
		      // Images 
		      case 'gif':	$ct='image/gif'; break; 
		      case 'png':	$ct='image/png'; break; 
		      case 'jpeg': 
		      case 'jpg': 	$ct='image/jpg'; break;
		      case 'svg':	$ct='image/svg+xml'; break;
		      case 'ico':	$ct='image/vnd.microsoft.icon'; break;
		      // Content & Data
		      case 'txt': 	$ct='text/plain'; break;
		      case 'js':	$ct='application/javascript'; break;
		      case 'json':	$ct='application/json'; break;
		      case 'xml':	$ct='text/xml'; break;
		      case 'dtd':	$ct='application/xml-dtd'; break;
		      case 'html':	$ct='text/html'; break;
		      case 'csv':	$ct='text/csv'; break;
		      case 'css':	$ct='text/css'; break;
		      default:		$ct='application/octet-stream'; break;
		}
		header('Content-Type: '.$ct);
		header('Content-Disposition: '.(isset($_REQUEST['attach']) ? 'attachement' : 'inline').'; filename="'.$info['filename'].'.'.$info['extension'].'"');
		header('Content-Length: '.filesize($src));
		if (isset($_REQUEST['nocache']))
			header('Cache-Control: no-cache, must-revalidate');

		switch ($info['extension']) {
			case 'css': die(preg_replace('/(.\/)?assets\//', './?view=asset&src=', file_get_contents($src)));
			default: die(file_get_contents($src));
		}
	}
}