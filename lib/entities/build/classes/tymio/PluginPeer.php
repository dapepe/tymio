<?php



/**
 * Skeleton subclass for performing query and update operations on the 'plugin' table.
 *
 * @package    propel.generator.tymio
 */
class PluginPeer extends BasePluginPeer {

	const ENTITY_SYSTEM = 'system';

	const EVENT_CALL    = 'call';
	const EVENT_TIMED   = 'timed';
	const EVENT_INCLUDE = 'include';

	/** @var array Stores all entities and registered events */
	static private $entityEvents = array(
		self::ENTITY_SYSTEM      => array(
			self::EVENT_CALL     => true,
			self::EVENT_TIMED    => true,
			self::EVENT_INCLUDE  => true,
		),
	);

	/**
	 * @var array Associative array mapping events to arrays of temporary plugins.
	 */
	static private $plugins = array();

	/**
	 * @var User The user authenticated by the system.
	 * @see setAuthenticatedUser()
	 */
	static private $authUser = null;

	/**
	 * Registers an entity event
	 *
	 * @param string $entityName The event entity
	 * @param string $eventName The event name
	 */
	static public function registerEvent($entityName, $eventName) {
		if ( isset(self::$entityEvents[$entityName]) )
			self::$entityEvents[$entityName][$eventName] = true;
		else
			self::$entityEvents[$entityName] = array($eventName => true);
	}

	/**
	 * Returns a list of registered events
	 *
	 * @param string $entityName The event entity
	 * @return array List of event names
	 */
	static public function listEvents($entityName) {
		return ( isset(self::$entityEvents[$entityName]) ? array_keys(self::$entityEvents[$entityName]) : array() );
	}

	/**
	 * Returns a list of all registered entities
	 *
	 * @return array List of entities
	 */
	static public function listEntities() {
		return array_keys(self::$entityEvents);
	}

	/**
	 * Returns a list of plugins.
	 *
	 * @return array|PropelObjectCollection
	 */
	static public function getPlugins(User $user, $entityName, $eventName, PropelPDO $con = null) {
		return PluginQuery::create()
			->filterByAccount($user->getAccount($con))
			->filterByEntity($entityName)
			->filterByActive(0, Criteria::NOT_EQUAL)
			->addAscendingOrderByColumn(PluginPeer::PRIORITY)
			->addAscendingOrderByColumn(PluginPeer::IDENTIFIER)
			->findByEvent($eventName, $con);
	}

	/**
	 * Adds a non-persistent plugin.
	 *
	 * @param Plugin $plugin
	 * @return void
	 */
	static public function registerPlugin(Plugin $plugin) {
		$eventName = $plugin->getEvent();
		self::$plugins[$eventName][] = $plugin;
	}

	/**
	 * Sets the user that was authenticated by the system.
	 *
	 * @param User $user
	 * @return void
	 * @uses $authUser
	 */
	static public function setAuthenticatedUser(User $user = null) {
		self::$authUser = $user;
	}

	/**
	 * Creates and returns an associative array of plugin parameters.
	 *
	 * @param string $entityName The name of the entity causing the plugin to
	 *     be invoked.
	 * @param string $eventName The name of the event causing the plugin to
	 *     be invoked.
	 * @param User $user Optional. The user to execute the plugin on behalf of.
	 *     Default is NULL.
	 * @param mixed $data Optional. Additional data for the plugin.
	 *     Default is NULL.
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return array An array with the following keys:
	 *     - _ENTITY       The value of {@link $entityName}.
	 *     - _EVENT        The value of {@link $eventName}.
	 *     - _DOMAIN       The domain {@link $user} belongs to.
	 *     - _AUTH_USER    An associative array with properties of the authenticated user.
	 *     - _USER         An associative array with properties of {@link $user}.
	 *     - _DATA         Data specified in {@link $data}.
	 * @see fireEvent()
	 * @uses $authUser
	 */
	static public function buildParameters($entityName, $eventName, User $user = null, $data = null, PropelPDO $con = null) {
		if ( $user === null ) {
			$domainData = null;
			$userData   = null;
		} else {
			$domain     = $user->getDomain($con);
			$domainData = ( $domain === null ? null : $domain->toArray() );
			$userData   = $user->toArray();
			unset($userData['Password']);
		}

		if ( self::$authUser === null ) {
			$authUserData = null;
		} else {
			$authUserData = self::$authUser->toArray();
			unset($authUserData['Password']);
		}

		return array(
			'_ENTITY'    => $entityName,
			'_EVENT'     => $eventName,
			'_DOMAIN'    => $domainData,
			'_AUTH_USER' => $authUserData,
			'_USER'      => $userData,
			'_DATA'      => $data,
		);
	}

	/**
	 * Fires the specified event on an entity, executing matching plugins and returning their data.
	 *
	 * @param User $user The user to execute the plugin on behalf of.
	 * @param string $entityName The name of the entity causing the plugin to
	 *     be invoked.
	 * @param string $eventName The name of the event causing the plugin to
	 *     be invoked.
	 * @param mixed $data Optional. Additional data for the plugin.
	 *     Default is NULL.
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return mixed
	 * @see buildParameters()
	 */
	static public function fireEvent(User $user, $entityName, $eventName, $data = null, PropelPDO $con = null) {
		$databasePlugins = self::getPlugins($user, $entityName, $eventName, $con);
		$parameters      = self::buildParameters($entityName, $eventName, $user, $data, $con);

		foreach ($databasePlugins as $plugin) {
			$sandbox = $plugin->execute($user, $parameters, $con);

			$globals = $sandbox->getGlobals();
			$parameters['_DATA'] = ( isset($globals['_DATA']) ? $globals['_DATA'] : null );

			$debugOutput = $sandbox->getDebugOutput();
			if ( $debugOutput !== '' )
				error_log(__METHOD__.': Plugin "'.$plugin->getIdentifier().'" #'.$plugin->getId().': Debug output: '.$debugOutput);

			$debugLog    = $sandbox->getDebugLog();
			if ( $debugLog !== '' )
				error_log(__METHOD__.': Plugin "'.$plugin->getIdentifier().'" #'.$plugin->getId().': Debug log: '.$debugLog);

			$pluginException = $sandbox->getException();
			if ( $pluginException !== null ) {
				$callStackText = implode("\n", $sandbox->getCallStack());
				error_log(
					__METHOD__.': Plugin "'.$plugin->getIdentifier().'" #'.$plugin->getId().': '.
					$pluginException->__toString()."\n".
					'Call stack'."\n".$callStackText
				);
				throw new Exception($pluginException->getMessage().', call stack'."\n".$callStackText);
			}
		}

		return $parameters['_DATA'];
	}

} // PluginPeer
