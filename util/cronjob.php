<?php

require_once(dirname(__FILE__).'/../lib/tymio/common.php');

// Run timed plugins if either their last execution time is further in the past
// than their specified execution interval or where the time of day minus the
// start time offset is a divisor of the interval.

$now       = time();
$timeOfDay = $now - strtotime('0:00', $now);

$query     = new PluginQuery();
$plugins   = $query
	->filterByActive(0, Criteria::NOT_EQUAL)
	->filterByEntity(PluginPeer::ENTITY_SYSTEM)
	->filterByEvent(PluginPeer::EVENT_TIMED)
	->add($query->getNewCriterion(PluginPeer::LAST_EXECUTION_TIME, '('.$now.' - '.PluginPeer::LAST_EXECUTION_TIME.' > '.PluginPeer::INTERVAL.')', Criteria::CUSTOM)
		->addOr(
			$query->getNewCriterion(PluginPeer::START, '((86400 + '.$timeOfDay.' - '.PluginPeer::START.') % 86400 % '.PluginPeer::INTERVAL.' BETWEEN 0 AND 59)', Criteria::CUSTOM)
				->addAnd($query->getNewCriterion(PluginPeer::LAST_EXECUTION_TIME, '('.$now.' - '.PluginPeer::LAST_EXECUTION_TIME.' > 60)', Criteria::CUSTOM))
		)
	)
	->addAscendingOrderByColumn(PluginPeer::LAST_EXECUTION_TIME) // Earliest previous execution time first (might not have been run previously)
	->addAscendingOrderByColumn(PluginPeer::PRIORITY)
	->find();

$parameters = PluginPeer::buildParameters(
	PluginPeer::ENTITY_SYSTEM,
	PluginPeer::EVENT_TIMED
);

foreach ($plugins as $plugin) {
	try {
		error_log('Plugin #'.$plugin->getId().' '.$plugin->getIdentifier().', last exec time '.($now - $plugin->getLastExecutionTime()).', time of day '.$timeOfDay.', interval '.$plugin->getInterval().' => '.((86400 + $timeOfDay - $plugin->getStart()) % 86400 % $plugin->getInterval()));

		$sandbox    = $plugin->execute(null, $parameters);

		$plugin
			->setLastExecutionTime($now)
			->save();

		$exception  = $sandbox->getException();
		if ( $exception !== null )
			throw $exception;

	} catch (Exception $e) {
		echo 'Plugin #'.$plugin->getId().' '.$plugin->getIdentifier().': '.$e->getMessage()."\n";
	}
}

?>
