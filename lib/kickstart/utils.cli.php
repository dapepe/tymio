<?php

/**
 * Utility functions to work with Command Line Interfaces
 *
 * @author Peter-Christoph Haider (Project Leader) et al.
 * @version 1.0 (2012-02-12)
 * @package Kickstart
 * @copyright Copyright (c) 2012, Groupion GmbH & Co. KG
 */

/**
 * Initilizes CLI arguements
 * 
 * Based on the comments from http://php.net/manual/en/features.commandline.php
 * 
 * Example Input:
 * ./script.php -a arg1 --opt1 arg2 -bcde --opt2=val2 arg3 arg4 arg5 -fg --opt3
 * 
 * Output:
 * Array (
 * 	[exec] => ./script.php
 * 	[options] => Array (
 * 		[0] => opt1
 * 		[1] => Array (
 * 			[0] => opt2
 * 			[1] => val2
 *		)
 *		[2] => opt3
 * 	)
 * 	[flags] => Array (
 * 		[0] => a
 * 		[1] => b
 * 		[2] => c
 * 		[3] => d
 * 		[4] => e
 * 		[5] => f
 * 		[6] => g
 * 	)
 * 	[arguments] => Array (
 * 		[0] => arg1
 * 		[1] => arg2
 * 		[2] => arg3
 * 		[3] => arg4
 * 		[4] => arg5
 * 	)
 * )
 * 
 * 
 * @license None specified; Share at will
 * @version 1.0 (2010-08-11)
 */
function CLIarguments($args) {
    $ret = array(
        'exec'      => '',
        'options'   => array(),
        'flags'     => array(),
        'arguments' => array()
    );

    $ret['exec'] = array_shift($args);

    while (($arg = array_shift($args)) != NULL) {
        // Is it a option? (prefixed with --)
        if ( substr($arg, 0, 2) === '--' ) {
            $option = substr($arg, 2);

            // is it the syntax '--option=argument'?
            if (strpos($option,'=') !== FALSE)
                array_push( $ret['options'], explode('=', $option, 2) );
            else
                array_push( $ret['options'], $option );
           
            continue;
        }

        // Is it a flag or a serial of flags? (prefixed with -)
        if ( substr( $arg, 0, 1 ) === '-' ) {
            for ($i = 1; isset($arg[$i]) ; $i++)
                $ret['flags'][] = $arg[$i];

            continue;
        }

        // finally, it is not option, nor flag
        $ret['arguments'][] = $arg;
        continue;
    }
    return $ret;
}

?>