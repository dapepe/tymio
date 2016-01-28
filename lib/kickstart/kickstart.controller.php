<?php

/**
 * Basic application controller class
 *
 * @author Peter-Christoph Haider (Project Leader) et al.
 * @version 1.0 (2010-09-07)
 * @copyright Copyright (c) 2011, Groupion GmbH & Co. KG
 * @package Kickstart
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
class KickstartController {
	/** @var {string} defaultTag The default tag for the controller's view */
	public $defaultTag = 'html';
	
	public function __construct() {
		$this -> _view = new \Xily\Bean($this -> defaultTag);
	}
	public function getView() {
		return isset($this -> _view) ? $this -> _view : new \Xily\Bean($this -> defaultTag);
	}
	
}

?>