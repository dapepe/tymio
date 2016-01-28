<?php

/**
 * User Controller
 *
 * Controls user details
 *
 * @author Peter-Christoph Haider (Project Leader) et al.
 * @version 1.0 (2010-09-07)
 * @package tymio
 * @copyright Copyright (c) 2012, Groupion GmbH & Co. KG
 */
class UsersController extends Controller {
	/**
	 * @param Localizer $locator
	 */
	public function __construct($locale) {
		$this->_view = \Xily\Bean::create(
			$locale->replace(
				file_get_contents(MODULE_DIR.'users'.DIRECTORY_SEPARATOR.'users.xml')
			)
		);

		$api = APIFactory::get('domain');
		$xmlSelectDomain = $this->_view->getNodeById('selDetailsDomain');
		$xmlFilterDomain = $this->_view->getNodeById('selFilterDomain');
		if ($xmlSelectDomain && $xmlFilterDomain) {
			$xmlSelectDomain->addChild(new \Xily\Bean('option', '--- '.$locale->get('field.pleaseselect').' ---'));
			$xmlFilterDomain->addChild(new \Xily\Bean('option', '--- '.ucfirst($locale->get('common.all')).' ---'));
			foreach ($api->do_list() as $arrDomain) {
				$xmlSelectDomain->addChild(new \Xily\Bean('option', $arrDomain['Name'], array('value' => $arrDomain['Id'])));
				$xmlFilterDomain->addChild(new \Xily\Bean('option', $arrDomain['Name'], array('value' => $arrDomain['Id'])));
			}
		}
	}
}

?>