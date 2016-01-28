<?php

include_once 'lib/tymio/util.inc.php';


/**
 * Skeleton subclass for representing a row from the 'transaction' table.
 *
 * @package    propel.generator.tymio
 */
class Transaction extends BaseTransaction {

	public function preSave(PropelPDO $con = null) {
		if ( $this->creationdate === null )
			$this->setCreationdate(time());

		return parent::preSave($con);
	}

} // Transaction
