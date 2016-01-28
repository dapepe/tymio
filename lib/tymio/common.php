<?php

/**
 * Common initialization and library loading code.
 *
 * @author Huy Hoang Nguyen
 * @package tymio
 * @copyright Copyright (c) 2012, Groupion GmbH & Co. KG
 */

define('BASE_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..');
define('LIB_DIR', BASE_DIR.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR);
define('ENTITIES_DIR', LIB_DIR.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR);

set_include_path(
	ENTITIES_DIR.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.'classes'.PATH_SEPARATOR.
	LIB_DIR.PATH_SEPARATOR.
	BASE_DIR.PATH_SEPARATOR
);

/* Specify application constants
--------------------------------------------------- */
define('APP_DIR', BASE_DIR.'/app'.DIRECTORY_SEPARATOR);
define('VIEW_DIR', APP_DIR.'views'.DIRECTORY_SEPARATOR);
define('MODULE_DIR', APP_DIR.'modules'.DIRECTORY_SEPARATOR);
define('CONTROLLER_DIR', APP_DIR.'controllers'.DIRECTORY_SEPARATOR);
define('ASSET_DIR', 'assets'.DIRECTORY_SEPARATOR);
define('API_DIR', APP_DIR.'api'.DIRECTORY_SEPARATOR);
define('CACHE_DIR', 'cache'.DIRECTORY_SEPARATOR);

define('API_DEFAULT', 'user');
define('VIEW_DEFAULT', 'clockings');

/* Load the settings
--------------------------------------------------- */
require LIB_DIR.'/xily/src/config.php';
\Xily\Config::load(BASE_DIR.'/config.ini');
$langAccepted = array('de', 'en');
$langDefault  = 'de';

/* require the libraries
--------------------------------------------------- */
require_once LIB_DIR.'/propel/runtime/lib/Propel.php';
Propel::init(LIB_DIR.'/entities/build/conf/config.php');
Propel::getDB()->setCharset(Propel::getConnection(), 'UTF8');

require LIB_DIR.'/xily/src/base.php';
require LIB_DIR.'/xily/src/dict.php';
require LIB_DIR.'/xily/src/xml.php';
require LIB_DIR.'/xily/src/bean.php';

require LIB_DIR.'/rest/src/server.php';
require LIB_DIR.'/rest/src/client.php';

require LIB_DIR.'/kickstart/kickstart.debug.php';
require LIB_DIR.'/kickstart/kickstart.api.php';
require LIB_DIR.'/kickstart/kickstart.router.php';
require LIB_DIR.'/kickstart/kickstart.controller.php';
require LIB_DIR.'/kickstart/kickstart.localizer.php';
require LIB_DIR.'/kickstart/kickstart.validator.php';
require LIB_DIR.'/kickstart/utils.excel.php';
require LIB_DIR.'/kickstart/compatibility.php';
require LIB_DIR.'/kickstart/form.php';
require LIB_DIR.'/kickstart/html.php';
require LIB_DIR.'/kickstart/http.php';
require LIB_DIR.'/kickstart/keyreplace.php';
require LIB_DIR.'/kickstart/ldap.php';
require LIB_DIR.'/kickstart/recentlist.php';
require LIB_DIR.'/kickstart/session.php';
require LIB_DIR.'/kickstart/util.php';

require LIB_DIR.'/cryptastic/cryptastic.class.php';

require LIB_DIR.'/spyc/spyc.php';

require LIB_DIR.'/tymio/controller.class.php';
require LIB_DIR.'/tymio/localizer.class.php';
require LIB_DIR.'/tymio/api.class.php';
require LIB_DIR.'/tymio/util.inc.php';
require LIB_DIR.'/tymio/ixml.inc.php';
require LIB_DIR.'/tymio/entityarray.php';
require LIB_DIR.'/tymio/search.php';

require APP_DIR.'/api/account.php';
require APP_DIR.'/api/clocking.php';
require APP_DIR.'/api/domain.php';
require APP_DIR.'/api/holiday.php';
require APP_DIR.'/api/plugin.php';
require APP_DIR.'/api/transaction.php';
require APP_DIR.'/api/user.php';

$locale = Localizer::getInstance($langAccepted, $langDefault);
$locale->load(APP_DIR.'locales', \Xily\Config::get('app.cache', 'bool', false) ? CACHE_DIR.'locales' : false);

?>
