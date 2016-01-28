<?php

namespace Xily;

/**
 * view:include
 *
 * Includes a CSS or JS file in the ria:page collector
 * <code>
 * 	<view:include type="[css/js]" file="{STRING}" />
 * </code>
 *
 * @param type File type (css or js; default is css)
 * @param file File name
 *
 * @author Peter-Christoph Haider (Project Leader) et al. <info@xily.info>
 * @version 1.0 (2009-05-20)
 * @package xilyBeans
 * @copyright Copyright (c) 2009-2010, Peter-Christoph Haider
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @license http://www.xily.info/ Commercial License
 */
class ViewInclude extends Bean {
	public function result($mxtData, $intLevel=0) {
		if ($this -> attribute('type') == 'js')
			$this -> collect('include', '<script language="JavaScript" type="text/javascript" src="'.($this -> hasAttribute('file') ? $this -> attribute('file') : $this -> attribute('src')).'"></script>');
		else
			$this -> collect('include', '<link rel="stylesheet" type="text/css" href="'.($this -> hasAttribute('file') ? $this -> attribute('file') : $this -> attribute('src')).'" />');
	}
}

?>
