<?php



/**
 * Skeleton subclass for representing a row from the 'system_log' table.
 *
 * @package    propel.generator.tymio
 */
class SystemLog extends BaseSystemLog {

	public function getData() {
		return json_decode(parent::getData(), true);
	}

	public function setData($data) {
		$toArray = array($data, 'toArray');
		if ( is_object($data) and is_callable($toArray) )
			$data = call_user_func($toArray);

		return parent::setData(json_encode($data));
	}

} // SystemLog
