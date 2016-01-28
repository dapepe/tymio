<?php

class Settings {
	public static $ADMIN_GROUP = "Human Resources";
	public static $NOTIFY_GROUP = "Human Resources";
	public static $OVERTIME = 120; // After X minutes of flexitime, it's probably overtime
	public static $FLEXITIME_LIMIT = 3000; // If the limit of X hours flexitime is reached, deny every excessive flexitime
	public static $CHANGE_LIMIT = 48; // After this time, any changes need approval
	public static $ADMIN_MODE = true;
}

?>