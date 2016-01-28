<?php

/**
 * Class to specify application routes and behaviour
 *
 * @author Peter-Christoph Haider (Project Leader) et al.
 * @version 1.0 (2012-02-12)
 * @package Kickstart
 * @copyright Copyright (c) 2012, Groupion GmbH & Co. KG
 */
abstract class KickstartRouter {
	public $stages = array(
		'api' => array(
			'method' => 'any',		// Accepted request methods (post, get, any; Default: any)
			'params' => array('do'),	// Additional parameters to make it a valid call
			'callback' => 'load_api'
		),
		'view' => 'load_view',
		'asset' => array(
			'callback' => 'load_asset',
			'meta' => array(
				'dir' => 'assets/'
			)
		)
	);

	public function __construct($strViewParam='view', $strRouteSeparator='/', $strControllerDir='controllers', $strCacheDir=false) {
		$this -> _view = new \Xily\Bean('html');
		$this -> viewParam = $strViewParam;
		$this -> routeSeparator = $strRouteSeparator;
		$this -> controllerDir = xilyBase::fileFormatDir($strControllerDir);
		$this -> cacheDir = xilyBase::fileFormatDir($strCacheDir);
	}	
	
	public static function run() {
		// USE LOCALTOR INSTEAD!
		// USE STRUCTURE FROM index.php
		
		$locale = self::getLang();
		
		$arg = array();
		$crumps = explode($this -> routeSeparator, $_REQUEST['page']);
		
		// Check the cache first
		if ($this -> cacheDir) {
			$cacheFile = $this -> cacheDir.$page.'-'.$locale[0].'-'.$locale[1].'.htm';
			if (\Xily\Config::get('opt.cache') == 1 && file_exists($cacheFile))
				return file_get_contents($cacheFile);
		}
		
		// Check, if there is a controller for the view
		if (file_exists($this -> controllerDir.$view.'.php'))
			include_once('controllers/'.$view.'.php');
		$controllerClass = $view.'Controller';
		if (class_exists($controllerClass)) {
			$controller = new $controllerClass();
			$xlyPage = $controller -> getView();
		} else {
			$file = 'views/'.$view.'.xml';
			if (!file_exists($file))
				$file = 'views/404.xml';
				
			$xlyPage = \Xily\Bean::create($file, 1);
		}
		
		$doc = $xmlDoc -> attribute('doc');
		$cache = \Xily\Config::get('opt.cache') == 1 ? true : !$xmlDoc -> isFalse('cache');
				
		// If no crump is a valid page identifier, something is fishy and the user should get the notfound page
		// Same, if the indexed document is not found
		if (!file_exists(\Xily\Config::get('app.dir').'views/'.$doc)
			|| is_dir(\Xily\Config::get('app.dir').'views/'.$doc)
			|| (isset($crumps) && (sizeof($crumps) == sizeof($arg)))) {
			$doc = 'notfound.xml';
			$arg = array();
			$cache = false;
		}

		$xlyPage = \Xily\Bean::create(\Xily\Config::get('app.dir').'views/'.$doc, 1);
		// Dynamic parameters do not matter for cached views

		$xlyPage -> setAttribute('lang', $locale[0]);
		$xlyPage -> setAttribute('region', $locale[1]);
		if (array_key_exists('page', $_REQUEST))
			$xlyPage -> setAttribute('page', $_REQUEST['page']);
			
		if ($this -> cacheDir) {
			$html = $xlyPage -> run();
			xilyBase::fileWrite($cacheFile, $html);
			return $html;
		} else {
			$xlyPage -> setDataset($arg, 'arg');
			$xlyPage -> setDataset($_GET, 'get');
			$xlyPage -> setDataset($_POST, 'post');
			$xlyPage -> setDataset($_REQUEST, 'request');
			return $xlyPage -> run();
		}
		
	}

	public function load_script() {
		$js = 'assets/js/'.$_REQUEST['script']; 
		echo file_exists($js) ? $locator -> insert(file_get_contents($js)) : '';
	}
	
	public function load_view() {
		$view = array_key_exists('view', $_REQUEST) ? $_REQUEST['view'] : 'login';
		
		if (!in_array($view, $noauth)
			&& !array_key_exists('user', $_SESSION)
			&& !(isset($_REQUEST['user']) && API::validateUser($_REQUEST['user'], isset($_REQUEST['password']) ? $_REQUEST['password'] : '')))
			$view = 'login';
		elseif ($view == 'login') // If logged-in go directly to the home screen
			$view = 'home';
			
		// Check, if there is a controller for the view
		if (file_exists('controllers/'.$view.'.php'))
			include 'controllers/'.$view.'.php';
		$controllerClass = $view.'Controller';
		if (class_exists($controllerClass)) {
			$controller = new $controllerClass($locator);
			$xlyPage = $controller -> getView();
		} else {
			$file = 'views/'.$view.'.xml';
			if (!file_exists($file))
				$file = 'views/404.xml';
			
			$xlyPage = \Xily\Bean::create($locator -> insert(file_get_contents($file)));
		}
	
		if (!$xlyPage -> hasAttribute('id'))
			$xlyPage -> setAttribute('id', $view);
		$xlyPage -> setDataset($_GET, 'get');
		$xlyPage -> setDataset(\Xily\Config::getDir('app.url'), 'url');
		$xlyPage -> setDataset($_POST, 'post');
		$xlyPage -> setDataset($_REQUEST, 'request');
		$xlyPage -> locator = $locator;
		
		echo $xlyPage -> run();
	}
}

?>