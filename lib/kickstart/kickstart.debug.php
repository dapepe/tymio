<?php

/**
 * Default debugging function
 * Also sets the default error handler to KickstartErrorHandle
 *
 * @author Peter-Christoph Haider (Project Leader) et al.
 * @version 1.0 (2012-02-12)
 * @package Kickstart
 * @copyright Copyright (c) 2012, Groupion GmbH & Co. KG
 */

/**
 * Returns an error trace
 * 
 * @param Exception $e
 * @return string
 */
function KickstartErrorTrace($e) {
	$strTrace = '#0: '.$e -> getMessage().'; File: '.$e -> getFile().'; Line: '.$e -> getLine()."\n";
	$i = 1;
	foreach ($e -> getTrace() as $v) {
		if (!(isset($v['function']) && $v['function'] == 'errorHandle')) {
			if (isset($v['class']))
				$strTrace .= "#$i: ".$v['class'].$v['type'].$v['function'].'(';
			elseif (isset($v['function']))
				$strTrace .= "#$i: ".$v['function'].'(';
			else
				$strTrace .= "#$i: ";
			
			if (isset($v['args']) && isset($v['function'])) {
				$parts = array();
				foreach($v['args'] as $arg)
					$parts[] = KickstartErrorArg($arg);
				$strTrace .= implode(',', $parts).') ';
			}
			if (isset($v['file']) && isset($v['line']))
				$strTrace .= '; File: '.$v['file'].'; Line: '.$v['line']."\n";
			$i++;
		}
	}
	return $strTrace;
}

/**
 * Converts any function arguement into a string
 * 
 * @param mixed $arg
 * @return string
 */
function KickstartErrorArg($arg, $depth=true) {
	if (is_string($arg))
		return('"'.str_replace("\n", '', $arg ).'"');
	elseif (is_bool($arg))
		return $arg ? 'true' : 'false';
	elseif (is_object($arg))
		return 'object('.get_class($arg).')';
	elseif (is_resource($arg))
		return 'resource('.get_resource_type($arg).')';
	elseif (is_array($arg)) {
		$parts = array();
		if ($depth)
			foreach ($arg as $k => $v)
				$parts[] = $k.' => '.KickstartErrorArg($v, false);
		return 'array('.implode(', ', $parts).')';
	} elseif ($depth)
		return var_export($arg, true);
}

/**
 * Converts an error into an exception
 * @param $errno
 * @param $errstr
 * @param $errfile
 * @param $errline
 */
function KickstartErrorHandle($errno, $errstr, $errfile, $errline) {
	if ($errno == E_STRICT)
		return true;

	throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}

set_error_handler('KickstartErrorHandle');

?>