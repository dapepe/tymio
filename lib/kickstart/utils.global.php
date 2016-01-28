<?php

/**
 * Sorts an array by key
 *
 * @param array $arrData
 * @param string $strKey
 * @param bool $bolAsc
 */
function sortArray($arrData, $strKey, $bolAsc=true) {
	if(!is_array($strKey))
		$strKey = array($strKey);
	usort($arrData, function($a, $b) use($strKey) {
		$retval = 0;
		foreach($strKey as $strKeyname)
			if($retval == 0) $retval = strnatcmp($a[$strKeyname],$b[$strKeyname]);

		return $retval;
	});
	return $bolAsc ? $arrData : array_reverse($arrData);
}

/**
 * Checks if an array is associative or numeric
 *
 * @param array $arr
 */
function isAssoc($arr) {
	return is_array($arr) ? (array_keys($arr) !== range(0, count($arr) - 1)) : false;
}

?>
