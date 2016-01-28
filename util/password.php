<?php

require_once(dirname(__FILE__).'/../lib/tymio/common.php');

$passwordArg = 1;

if ( isset($argv[1]) and preg_match('`^(?:-u|--user:)(?:=(.+))?$`', $argv[1], $matches) ) {
	if ( isset($matches[1]) ) {
		$loginName = $matches[1];
		$passwordArg++;
	} elseif ( isset($argv[2]) ) {
		$loginName = $argv[2];
		$passwordArg += 2;
	} else {
		die('Missing login name argument for -u/--user option.');
	}

	$user = UserQuery::create()->findOneByFQN($loginName);
	if ( $user === null )
		die('Could not find user "'.$loginName.'".');

} else {
	$user = null;

}

if ( isset($argv[$passwordArg]) ) {
	$password = $argv[$passwordArg];
} else {
	echo
		'Usage: '.basename($argv[0]).' [-u|--user] [<password>]'."\n\n".
		'Please enter your password: ';
	$password = str_replace(array("\r", "\n"), array('', ''), fgets(STDIN));
}

if ( $user === null ) {
	echo 'Password hash:'."\n".
		UserPeer::getPasswordHash($password)."\n\n";
	exit(0);
}

$user
	->setPassword($password)
	->save();

echo 'Updated password for user "'.$loginName.'".'."\n";
