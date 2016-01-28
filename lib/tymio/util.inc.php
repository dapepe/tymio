<?php

/**
 * Creates a link to an application view
 *
 * @param sting $view
 * @param string $index
 * @return string
 */
function buildLink($view, $index=false) {
	if (\Xily\Config::get('app.seo') == 1)
		return \Xily\Config::getDir('app.url').$view.($index ? '/'.$index : '');
	else
		return 'index.php?view='.$view.($index ? '&index='.$index : '');
}

/**
 * Creates a DateTime object for the given timestamp.
 *  
 * @param int $timestamp
 * @return DateTime 
 */
function createDate($timestamp) {
	$date = new DateTime();
	$date->setTimestamp($timestamp);
	return $date;
}

/**
 * Creates an dmY representation of a timestamp or a DateTime object.
 * 
 * @param object $time
 * @return string dmY representation
 */
function dayKey($time) {
	$timestamp = $time;
	if ($time instanceof DateTime) {
		$timestamp = $timestamp->getTimestamp();
	}
	return date('dmY', $timestamp);
}

?>