<?php

/**
 * Application Index
 *
 * - Initialized API service calls
 * - Loads additional JavaScript (and inserts language variables)
 * - Loads requested views and controller
 *
 * @author Peter-Christoph Haider (Project Leader) et al.
 * @package tymio
 * @version 1.3 (2012-02-25)
 * @copyright Copyright (c) 2012, Groupion GmbH & Co. KG
 */

require_once(dirname(__FILE__).'/lib/tymio/common.php');
Session::start();

\Xily\Bean::$BEAN_DIRS[] = LIB_DIR.'beans';

$pluginEvents = array(
	'menu'    => array(
		'clocking',
		'clockings',
		'offtime',
		'transaction',
		'transactions',
	),
);

function registerPluginEvents($pluginEvents) {
	foreach ($pluginEvents as $entity => $events) {
		foreach ($events as $event)
			PluginPeer::registerEvent($entity, $event);
	}
}

try {

	registerPluginEvents($pluginEvents);
	$userAuthenticated = APIFactory::getAuthenticator()->authUser(true, true, isset($_REQUEST['autologin']) && $_REQUEST['autologin'] == true);

	$user = APIFactory::getAuthenticator()->getUser();
	PluginPeer::setAuthenticatedUser($user);

	if ( isset($_REQUEST['do']) ) {

		/* PERFORM THE API CALL
		-------------------------------------------------------------------- */

		$api = null;

		try {
			$apiName = array_key_exists('api', $_REQUEST) ? strtolower($_REQUEST['api']) : API_DEFAULT;

			$api = APIFactory::get($apiName);
			$api->run();

		} catch (Exception $e) {
			if ( ($e instanceof APIPermissionDeniedException) and
			     !empty($_REQUEST['inline']) ) {
				HTTP::forwardTo(
					$_SERVER['SCRIPT_NAME'].
					'?view=login'.
					'&return='.urlencode($_SERVER['REQUEST_URI']).
					'&'.Form::getTokenName().'='.urlencode(Form::getToken('loginreturn'))
				);

			} else {
				header('Content-Type: application/json; charset=utf-8');

				if ( $api instanceof API ) {
					$res = $api->exceptionToResult($e);
				} else {
					$res = array('error' => $e->getMessage());
					if ( isset($_REQUEST['debug']) )
						$res['trace'] = $e->getTrace();
				}

				echo json_encode($res);

			}

		}

	} elseif ( isset($_REQUEST['_lang']) ) {

		/* RETURN THE LANGUAGE FILE AS A JAVASCRIPT OBJECT
		-------------------------------------------------------------------- */

		header('Content-type: text/javascript; charset=utf-8');
		echo 'var $LANG = '.$locale->getJSON(\Xily\Config::get('app.cache', 'bool', false) ? CACHE_DIR.'locales' : false).';';

	} elseif ( !empty($_POST['return']) and Form::verify('login') ) {

		HTTP::forwardTo(HTTP::readPOST('return').'?username='.(isset($_POST['username']) ? $_POST['username'] : ''));

	} else {

		/* LOAD THE VIEW/CONTROLLER/MODULE
		-------------------------------------------------------------------- */

		$view = ( isset($_REQUEST['view']) ? $_REQUEST['view'] : VIEW_DEFAULT );

		$xmlMeta = new \Xily\Xml(); // The meta object will be passed on when the bean is run

		try {
			if ( !preg_match('`^[a-z]{1,32}$`', $view) )
				throw new Exception('View "'.$view.'" not found.');

			if ( !$userAuthenticated ) {
				if (isset($_REQUEST['username']) && $_REQUEST['username'] != '') {
					$message = 'Wrong username or password!';
				} else {
					$message = 'Please login!';
				}

				throw new Exception($message);
			}

		} catch ( Exception $e ) {
			if ($e->getMessage() != '') {
				$xmlMeta->addChild(
					new \Xily\Xml(
						'message',
						$e->getMessage(),
						array('class' => 'alert alert-error')
					)
				);
			}

			$view = 'login';
		}

		if ( $view === 'login' ) {
			$returnUrl = HTTP::readGET('return', '');
			if ( !Form::verify('loginreturn', Form::METHOD_GET) )
				$returnUrl = '';

			$xmlMeta->addChildren(array(
				new \Xily\Xml(
					'tokenName',
					Form::getTokenName()
				),
				new \Xily\Xml(
					'tokenValue',
					Form::getToken('login')
				),
				new \Xily\Xml(
					'return',
					( empty($returnUrl) ? $_SERVER['SCRIPT_URI'] : $returnUrl )
				),
			));
		}

		// Check if there is a controller for the view
		if ( file_exists(CONTROLLER_DIR.$view.'.php') )
			include_once CONTROLLER_DIR.$view.'.php';

		$controllerClass = ucfirst($view).'Controller';

		if ( class_exists($controllerClass) ) {
			$controller = new $controllerClass($locale);
			$controller->enrichMeta($xmlMeta);
			$xlyPage = $controller->getView();
		} else {
			// Check if the module exists
			if ( is_dir(MODULE_DIR.$view) ) {
				// Check if the module has a controller
				if ( file_exists(MODULE_DIR.$view.'/'.$view.'.php') )
					include_once MODULE_DIR.$view.'/'.$view.'.php';
				if ( class_exists($controllerClass) ) {
					$controller = new $controllerClass($locale);
					$xlyPage = $controller->getView();
				} else {
					if ( file_exists(MODULE_DIR.$view.'/'.$view.'.xml') ) {
						$xlyPage = \Xily\Bean::create(
							$locale->replace(
								file_get_contents( MODULE_DIR.$view.'/'.$view.'.xml' )
							)
						);
					}
				}
			}

			if ( !isset($xlyPage) )
				$xlyPage = \Xily\Bean::create(
					$locale->replace(
						file_get_contents(VIEW_DIR . (file_exists(VIEW_DIR.$view.'.xml') ? $view : '404') .'.xml')
					)
				);
		}

		if ( !$xlyPage->id() )
			$xlyPage->setAttribute('id', $view);

		$xlyPage->setDataset($_GET, 'get');
		$xlyPage->setDataset(\Xily\Config::getDir('app.url'), 'url');
		$xlyPage->setDataset($_POST, 'post');
		$xlyPage->setDataset($_REQUEST, 'request');
		$xlyPage->setAttribute('lang', $locale->getCurrent());

		echo $xlyPage->run($xmlMeta);
	}

} catch(Exception $e) {
	$xlyError = \Xily\Bean::create(
		$locale->replace(file_get_contents(VIEW_DIR.'error.xml'))
	);
	echo $xlyError->run(array(
		'message' => $e->getMessage(),
		'file'    => $e->getFile(),
		'line'    => $e->getLine(),
		'code'    => $e->getCode(),
		'trace'   => str_replace("\n", '<br />', htmlentities(KickstartErrorTrace($e)))
	));
}

?>
