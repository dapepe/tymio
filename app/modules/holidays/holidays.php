<?php

/**
 * User Holidays
 *
 * Controls user details
 *
 * @author Peter-Christoph Haider (Project Leader) et al.
 * @version 1.0 (2010-09-07)
 * @package tymio
 * @copyright Copyright (c) 2012, Groupion GmbH & Co. KG
 */
class HolidaysController extends Controller {
	/**
	 * @param Localizer $locator
	 */
	public function __construct($locale) {
		$this->_view = \Xily\Bean::create(
			$locale->replace(
				file_get_contents(MODULE_DIR.'holidays'.DIRECTORY_SEPARATOR.'holidays.xml')
			)
		);

		$api = APIFactory::get('domain');
		$xmlFilterDomain = $this->_view->getNodeById('selFilterDomain');
		if ($xmlFilterDomain) {
			$xmlFilterDomain->addChild(new \Xily\Bean('option', '--- '.ucfirst($locale->get('common.all')).' ---', array('value' => '')));
			foreach ($api->do_list() as $arrDomain) {
				$xmlFilterDomain->addChild(new \Xily\Bean('option', $arrDomain['Name'], array('value' => $arrDomain['Id'])));
			}
		}
	}
}

?>