<?php

/**
 * Logout Controller
 *
 * Terminates the current session and resets the login cookie
 *
 * @author Peter-Christoph Haider (Project Leader) et al.
 * @version 1.0 (2010-09-07)
 * @package tymio
 * @copyright Copyright (c) 2012, Groupion GmbH & Co. KG
 */
class LogoutController extends Controller {
	/**
	 * @param locator $locator
	 */
	public function __construct($locale) {
		APIFactory::getAuthenticator()->setUserSession();

		$this->_view = \Xily\Bean::create(
			$locale->replace(
				file_get_contents(VIEW_DIR.'login.xml')
			)
		);
	}

	/**
	 * Enriches and returns the meta XML
	 *
	 * @param \Xily\Xml $xmlMeta
	 * @return \Xily\Xml
	 */
	public function enrichMeta(&$xmlMeta) {
		$xmlMeta->addChild(new \Xily\Xml('message', 'You are logged out now!', array('class' => 'alert alert-success')));
	}
}

?>
