<?php



/**
 * Skeleton subclass for representing a row from the 'setting' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.tymio
 */
class Setting extends BaseSetting {
	public static $ADMIN_GROUP = "Human Resources";
	public static $NOTIFY_GROUP = "Human Resources";
	public static $OVERTIME = 120; // After X minutes of flexitime, it's probably overtime
	public static $FLEXITIME_LIMIT = 3000; // If the limit of X hours flexitime is reached, deny every excessive flexitime
	public static $CHANGE_LIMIT = 48; // After this time, any changes need approval
	public static $ADMIN_MODE = true;
} // Setting
