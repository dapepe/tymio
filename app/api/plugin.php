<?php

/**
 * API functions to manage PLUGINS
 *
 * @author Peter-Christoph Haider (Project Leader) et al.
 * @package ZfxSupport
 * @version 1.1 (2012-01-17)
 * @copyright Copyright (c) 2012, Groupion GmbH & Co. KG
 */
class PluginAPI extends API {

	static private $pluginNonAdminWhitelist = array(
		'menu'                     => true,
		PluginPeer::ENTITY_SYSTEM  => array(
			PluginPeer::EVENT_CALL => true,
		),
	);

	public $actions = array(
		/*!
		 * @cmd list
		 * @method any
		 * @description Lists all plugins of an account
		 * @param {string} search Search query
		 * @param {bool} showinactive
		 * @param {string} entity
		 * @param {string} event
		 * @param {string} orderby Order result by: "Date" (default), "Identifier", "Name", "Priority"
		 * @param {string} ordermode Order mode "asc" (default) or "desc"
		 * @return {array} List of vacations [{id, name}, ...]
		 * @demo
		 */
		'list' => array('ANY', 'list', array(
			array('search', 'string', null, false),
			array('showinactive', 'bool', false, false),
			array('entity', 'string', null, false),
			array('event', 'string', null, false),
			array('orderby', 'string', false, false),
			array('ordermode', 'string', false, false),

		)),
		/*!
		 * @cmd details
		 * @method any
		 * @description Display the plugin details
		 * @param {int} id* The plugin ID
		 * @return {array} [Name:{string}, Identifier:{string}, Code:{string}, Entity:{string}, Event:{string}]
		 * @demo
		 */
		'details' => array('ANY', 'details', array(
			array('id', 'int', false),
		)),
		/*!
		 * @cmd add
		 * @method any
		 * @description Adds a new plugin
		 * @param {array} data* The plugin details (Name:{string}, Identifier:{string}, Code:{string}, Entity:{string}, Event:{string})
		 * @return {int} The user database ID
		 * @demo
		 */
		'add' => array('POST', 'add', array(
			array('data', 'array', false),
		)),
		/*!
		 * @cmd update
		 * @method any
		 * @description Updates a plugin
		 * @param {int} id* The plugin ID
		 * @param {array} data* The plugin details (Name:{string}, Identifier:{string}, Code:{string}, Entity:{string}, Event:{string})
		 * @return {bool}
		 * @demo
		 */
		'update' => array('POST', 'update', array(
			array('id', 'int', false),
			array('data', 'array', false),
		)),
		/*!
		 * @cmd activate
		 * @method any
		 * @description Activates a plugin
		 * @param {int} id* The plugin ID
		 * @return {array}
		 * @demo
		 */
		'activate' => array('POST', 'activate', array(
			array('id', 'int', false),
		)),
		/*!
		 * @cmd deactivate
		 * @method any
		 * @description Deactivates a plugin
		 * @param {int} id* The plugin ID
		 * @return {array}
		 * @demo
		 */
		'deactivate' => array('POST', 'deactivate', array(
			array('id', 'int', false),
		)),
		/*!
		 * @cmd erase
		 * @method any
		 * @description Deletes a single plugin permanently
		 * @param {int} id* The plugin ID
		 * @return {array}
		 * @demo
		 */
		'erase' => array('POST', 'erase', array(
			array('id', 'int', false),
		)),
		/*!
		 * @cmd execute
		 * @method post
		 * @description Executes a plugin
		 * @param {int} id* The plugin ID
		 * @param {string} parameters Additional parameters and variables encoded as a JSON string (for POST requests only)
		 * @param {bool} debug If true, the plugin's inputs and outputs will be encoded in a JSON object. If false, the plugin's raw output will be printed.
		 * @return {array}
		 * @demo
		 */
		'execute' => array('ANY', 'execute', array(
			array('id', 'string', false),
			array('parameters', 'string', null, false),
			array('debug', 'bool', null, false),
		)),
		/*!
		 * @cmd list_entities
		 * @method any
		 * @description List all registered entities
		 * @param {string} search
		 * @return {array}
		 */
		'list_entities' => array('ANY', 'list_entities', array(
			array('search', 'string', false, false),
		)),
		/*!
		 * @cmd list_events
		 * @method any
		 * @description List all events registered for an entity
		 * @param {string} entity
		 * @param {string} search
		 * @return {array}
		 */
		'list_events' => array('ANY', 'list_events', array(
			array('entity', 'string', false, false),
			array('search', 'string', false, false),
		)),
	);

	public $auth_exceptions = array();

	/** @var array Basic filter settings */
	public $filter_basic = array(
		'Name' => array(
			'filter' => FILTER_VALIDATE_MIN_LENGTH,
			'len'    => 2,
			'field'  => 'field.name'
		),
		'Identifier' => array(
			'filter'  => FILTER_VALIDATE_RESOURCE,
			'field'  => 'field.identifier'
		),
	);

	/**
	 * Checks if explicit plugin invocation is allowed.
	 *
	 * @return bool
	 */
	private function executionAllowed(User $user, Plugin $plugin) {
		if ( $user->isAdmin() )
			return true;

		$entity = $plugin->getEntity();
		$event  = $plugin->getEvent();

		if ( ((string)$entity === '') or ((string)$event === '') )
			return false;

		// Check if entire entity is white-listed
		if ( !empty(self::$pluginNonAdminWhitelist[$entity]) and
		     (self::$pluginNonAdminWhitelist[$entity] === true) )
			return true;

		// Check if entity / event combination is white-listed
		return ( !empty(self::$pluginNonAdminWhitelist[$entity][$event]) );
	}

	/**
	 * User or token authentication
	 *
	 * @see API::auth()
	 * @return bool
	 */
	public function auth() {
		if ( $this->authUser() )
			return true;

		return false;
	}

	/**
	 * Lists the plugins
	 *
	 * @param string $search Search query
	 * @param bool $showDeleted Show deleted domains
	 * @param string $strOrderby OrderBy Column
	 * @param string $strOrderMode Order mode (asc or desc)
	 * @return array
	 */
	public function do_list($search = null, $showInactive = false, $entityName = null, $eventName = null, $strOrderby = 'Name', $strOrderMode = 'asc') {
		$user = $this->requireUser();

		$account = $user->getAccount();

		$query = new PluginQuery();
		$query->filterByAccount($account);

		if ( $strOrderMode !== 'asc' )
			$strOrderMode = 'desc';

		switch ($strOrderby) {
			case 'Identifier':
				break;
			case 'Entity':
				$query->orderByEntity($strOrderMode);
				break;
			case 'Event':
				$query->orderByEvent($strOrderMode);
				break;
			case 'Priority':
				$query->orderByPriority($strOrderMode);
				break;
			default: // Name
				$query->orderByName($strOrderMode);
				break;
		}

		$query->orderByIdentifier($strOrderMode);

		if ( (string)$search !== '' ) {
			$query
				->filterByName('%'.$search.'%', Criteria::LIKE)
				->_or()
				->filterByIdentifier('%'.$search.'%', Criteria::LIKE);
		}

		if ( !$showInactive )
			$query->filterByActive(0, Criteria::NOT_EQUAL);

		if ( (string)$entityName !== '' )
			$query->filterByEntity($entityName);

		if ( (string)$eventName !== '' )
			$query->filterByEvent($eventName);

		$result = array();

		foreach ($query->find() as $plugin) { /* @var $plugin Plugin */
			$result[] = array(
				'Id'         => $plugin->getId(),
				'AccountId'  => $plugin->getAccountId(),
				'Entity'     => $plugin->getEntity(),
				'Event'      => $plugin->getEvent(),
				'Priority'   => $plugin->getPriority(),
				'Identifier' => $plugin->getIdentifier(),
				'Name'       => $plugin->getName(),
				'Active'     => $plugin->getActive(),
			);
		}

		return $result;
	}

	/**
	 * Display the plugin details
	 *
	 * @param int $id The plugin ID
	 * @return array
	 */
	public function do_details($id) {
		$plugin = $this->getPluginById($id);
		return $plugin->toArray();
	}

	/**
	 * Adds a new plugin
	 *
	 * @param array $data
	 * @return int The vacation ID
	 */
	public function do_add($data) {
		return $this->do_update(false, $data);
	}

	/**
	 * Updates a plugin
	 *
	 * @param int $id The vacation ID
	 * @param array $data
	 * @return int The vacation ID
	 */
	public function do_update($id, $data) {
		$user = $this->requireUser();
		if ( !$user->isAdmin() )
			throw new Exception('Non-administrative user "'.$user->getFQN().'" cannot modify plugins.');

		// Validate input data
		$validator = new KickstartValidator();
		$locale = Localizer::getInstance();
		$warnings = $validator->filterErrors($data, $this->initFilter($this->filter_basic, $locale));
		if ( $warnings )
			return array('result' => false, 'warnings' => $warnings);

		$query = PluginQuery::create()->filterByAccount($user->getAccount());

		if ( $id ) {
			$query->filterById($id, Criteria::NOT_EQUAL);

			$plugin = PluginQuery::create()->filterByAccount($user->getAccount())
			                               ->findOneById($id);

			if ( !$plugin )
				throw new Exception('Plugin not found; ID: '.$id);
		} else {
			$plugin = new Plugin();
		}

		// Check for duplicates
		if ( $query->findOneByIdentifier($data['Name']) )
			throw new Exception($locale->insert('error.taken', array('value' => '"'.$data['Name'].'"')));

		if ( isset($data['Start']) ) {
			$plugin->setStart(strtotime($data['Start'].'Z', 0));
			unset($data['Start']);
		}

		$plugin->fromArray($data);
		$plugin->setAccount($user->getAccount());
		$plugin->save();

		return $plugin->getId();
	}

	/**
	 * Activates a plugin
	 *
	 * @param int $id The plugin ID
	 * @return bool
	 */
	public function do_deactivate($id) {
		$plugin = $this->getPluginById($id);
		$plugin->setActive(0)
		       ->save();

		return true;
	}

	/**
	 * Deactivates a plugin
	 *
	 * @param int $id The plugin ID
	 * @return bool
	 */
	public function do_activate($id) {
		$plugin = $this->getPluginById($id);
		$plugin
			->setActive(1)
			->save();

		return true;
	}

	/**
	 * Deletes a plugin permanently.
	 *
	 * @param int $id The plugin ID
	 * @return bool
	 */
	public function do_erase($id) {
		$plugin = $this->getPluginById($id);
		$plugin->delete();

		return true;
	}

	/**
	 * Executes an iXML script.
	 *
	 * @param string $id The plugin ID or the code to execute.
	 * @param array $parametersJson Optional. Additonal parameters. Default is NULL.
	 * @param bool $debug Optional. If TRUE, the plugin's inputs and outputs
	 *     will be encoded in a JSON object. If FALSE, the plugin's raw output
	 *     will be printed, and the return value will be {@link RESTvoidResult}.
	 *     Default is FALSE.
	 * @return RESTvoidResult|array
	 */
	public function do_execute($id, $parametersJson = null, $debug = false) {
		$user       = $this->requireUser();
		$plugin     = $this->getPluginById($id);

		if ( !$this->executionAllowed($user, $plugin) )
			throw new Exception('Non-administrative user "'.$user->getFQN().'" cannot execute plugin directly.');

		$data = (
			( empty($_POST) or ((string)$parametersJson === '') ) // Ignore parameters on GET requests
			? array(null)
			: json_decode('['.$parametersJson.']', true)
		);

		if ( !is_array($data) )
			throw new Exception('Invalid parameters JSON string.');

		$parameters = PluginPeer::buildParameters(
			$plugin->getEntity(),
			$plugin->getEvent(),
			$user,
			$data[0]
		);

		$sandbox    = $plugin->execute($user, $parameters);
		$exception  = $sandbox->getException();

		if ( $debug ) {
			return array(
				'data'   => $sandbox->getGlobals(),
				'output' => $sandbox->getOutput(),
				'debug'  => $sandbox->getDebugOutput(),
				'log'    => $sandbox->getDebugLog(),
				'calls'  => $sandbox->getCallStack(),
				'error'  => ( $exception === null ? null : $exception->getMessage() ),
			);

		} elseif ( $exception === null ) {
			echo $sandbox->getOutput();
			return new RESTvoidResult();

		} else {
			header('Content-Type: text/plain');
			echo $exception->getMessage()."\n\nCall stack:\n".implode("\n", $sandbox->getCallStack());
			return new RESTvoidResult();

		}
	}

	/**
	 * List all known entities
	 *
	 * @param string $search Search string
	 */
	public function do_list_entities($search = false) {
		return PluginPeer::listEntities();
	}

	/**
	 * List all events for an entity
	 *
	 * @param string $entityName Filter by event
	 * @param string $search Search string
	 */
	public function do_list_events($entityName = false, $search = false) {
		return PluginPeer::listEvents($entityName);
	}

	/**
	 * Loads a single plugin and checks the user's permissions.
	 *
	 * @param int|string $idOrCode
	 * @throws Exception
	 * @return Plugin
	 */
	private function getPluginById($idOrCode, PropelPDO $con = null) {
		$user = $this->requireUser();
		if ( !$user->isAdmin() )
			throw new Exception('Non-administrative user "'.$user->getFQN().'" cannot access plugins directly.');

		$idOrCode = trim($idOrCode);

		if ( substr($idOrCode, 0, 1) === '<' ) {
			// Protect against CSRF attacks
			if ( !Form::verifyPersist('plugin.execute') )
				throw new Exception('Plugin execution authentication failed.');

			$plugin = new Plugin();
			$plugin->setCode($idOrCode);
			return $plugin;
		} elseif ( is_numeric($idOrCode) and preg_match('`^\d+$`', $idOrCode) ) {
			$plugin = PluginQuery::create()->findOneById($idOrCode, $con);
		} else {
			$plugin = PluginQuery::create()->findOneByIdentifier($idOrCode, $con);
		}

		if ( $plugin === null )
			throw new Exception('Plugin with ID '.$idOrCode.' not found!');

		// Check if the vacation belongs to the user's account
		if ( $plugin->getAccountId() != $user->getAccount($con)->getId() )
			throw new Exception('The selected plugin belongs to a different account!');

		return $plugin;
	}

}
