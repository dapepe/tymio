<?php

namespace Xily;

/**
 * view:collect xilyBean class
 *
 * Collects code for the view:page element
 * <code>
 * 	<view:collect target="css">
 * 		#myCSSelem {
 * 			font-size: 14px;
 * 		}
 * 	</view:collect>
 * </code>
 *
 * @param target Name of the section inside the main HTML frame
 *
 * @author Peter-Christoph Haider (Project Leader) et al. <info@xily.info>
 * @version 2.1 (2009-06-26)
 * @package xilyBeans
 * @copyright Copyright (c) 2009-2010, Peter-Christoph Haider
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @license http://www.xily.info/ Commercial License
 */
class ViewCollect extends Bean {
	public function result($mxtData, $intLevel=0) {
		$this -> collect($this -> hasAttribute('target') ? $this -> attribute('target') : 'javainit', $this -> dump());
	}
}

?>
