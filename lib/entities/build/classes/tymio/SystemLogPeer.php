<?php



/**
 * Skeleton subclass for performing query and update operations on the 'system_log' table.
 *
 * @package    propel.generator.tymio
 */
class SystemLogPeer extends BaseSystemLogPeer {

	const CODE_SUCCESSFUL = 0x0000;
	const CODE_FAILED     = 0x0001;

	static public function add($serviceName, $object, $code = self::CODE_FAILED, $message = null, User $authUser = null, $data = null, PropelPDO $con = null) {
		if ( $object === null ) {
			$entityName = 'null';
			$index      = 0;
		} elseif ( is_object($object) ) {
			$entityName = get_class($object);
			$idMethod   = array($object, 'getId');
			$index      = ( is_callable($idMethod) ? call_user_func($idMethod) : null );
		} elseif ( is_array($object) and isset($object[0], $object[1]) ) {
			list($entityName, $index) = $object;
		} else {
			$entityName = 'data';
			$index      = json_encode($object);
		}

		if ( $authUser === null ) {
			$userName = '[none]';
		} else {
			try {
				$userName = $authUser->getFQN($con);
			} catch (Exception $doubleException) {
				$userName = '[#'.$authUser->getId().']';
			}
		}

		try {
			$log = new SystemLog();
			$log
				->setUser($authUser)
				->setService($serviceName)
				->setEntity($entityName)
				->setIndex($index)
				->setCode($code)
				->setMessage($message)
				->setData($data)
				->save($con);
		} catch (Exception $e) {
			error_log(__METHOD__.': Could not log: '.$entityName.' #'.$index.', code '.$code.', message: '.$message.', user "'.$userName.'", data: '.json_encode($data).', exception '.$e->__toString());
		}
	}

} // SystemLogPeer
