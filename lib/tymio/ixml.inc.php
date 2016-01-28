<?php

require dirname(__FILE__).'/../ixml/lib/ixml/base.php';

//set_time_limit(0);
//ini_set('default_mimetype', 'text/plain');

class PluginException extends Exception {

	/**
	 * @var array
	 */
	private $callStack;

	public function __construct(Exception $e, array $callStack) {
		parent::__construct($e->getMessage(), $e->getCode());
		$this->callStack = $callStack;
	}

	/**
	 * @return array
	 */
	public function getCallStack() {
		return $this->callStack;
	}

}

class PluginSandbox extends iXml\Sandbox implements IPluginDebugger {
	/**
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * @var array
	 */
	private $parameters;

	/**
	 * @var PluginIXml
	 */
	private $ixml = null;

	private $t1 = null;
	private $t2 = null;
	private $t3 = null;

	/**
	 * @var string
	 */
	private $output = null;

	/**
	 * @var string
	 */
	private $debugOutput = '';

	/**
	 * @var string
	 */
	private $debugLog = '';

	private $callStack = array();

	private $exception = null;

	public function __construct(Plugin $plugin, Account $account, Domain $domain = null, User $user = null, array $parameters = null) {
		$this->plugin     = $plugin;
		$this->parameters = ( isset($parameters) ? $parameters : array() );
		$this->account    = $account;
		$this->domain     = $domain;
		$this->user       = $user;

		$this->run();
	}

	public function getMetrics() {
		return array(
			'metrics' => array(
				'compile_time'   => number_format($this->t2 - $this->t1, 3),
				'execution_time' => number_format($this->t3 - $this->t2, 3),
				'total_time'     => number_format($this->t3 - $this->t1, 3),
				'memory_usage'   => number_format(memory_get_peak_usage(true) / 1024 / 1024, 1)
			),
			'plugin' => array(
				'Id'             => $this->plugin->getId(),
				'Name'           => $this->plugin->getName(),
				'Identifier'     => $this->plugin->getIdentifier(),
				'Event'          => $this->plugin->getEvent(),
				'Entity'         => $this->plugin->getEntity(),
			),
			'output' => $this->output,
		);
	}

	public function getOutput() {
		return $this->output;
	}

	public function getDebugOutput() {
		return $this->debugOutput;
	}

	public function getDebugLog() {
		return $this->debugLog;
	}

	public function getCallStack() {
		return $this->callStack;
	}

	public function getException() {
		return $this->exception;
	}

	public function getGlobals() {
		return ( $this->ixml === null ? null : $this->ixml->getGlobals() );
	}

	protected function sandbox() {
		$this->exception = null;
		$this->callStack = array();

		ob_start();

		try {
			$this->t1   = microtime(true);
			$this->ixml = $this->createInstance($this->account, $this->domain, $this->user);
			$this->t2   = microtime(true);

			if ( $this->ixml->root )
				$this->ixml->exec($this->parameters);
		} catch (Exception $e) {
			$this->exception = $e;
			$this->callStack = ( $this->ixml === null ? array() : $this->ixml->getCallStack() );
		}

		$this->output = ob_get_clean();

		$this->t3 = microtime(true);
	}

	private function createInstance(Account $account, Domain $domain = null, User $user = null, $failOnWarning = true) {
		$ixml = new PluginIXml($this->plugin->getCode(), $account, $domain, $user, $this);
		$ixml->setFailOnWarning($failOnWarning);
		return $ixml;
	}

	public function write($text) {
		$this->debugLog .= 'Line '.$this->ixml->getCurrentDebugLine().': '.$text."\n";
	}

	public function log($text) {
		$this->debugLog .= 'Line '.$this->ixml->getCurrentDebugLine().': '.$text."\n";
	}

}

interface IPluginDebugger {

	public function write($text);
	public function log($text);

}

class DateFormatReplacer {

	private $timestamp;

	public function __construct($timestamp) {
		$this->timestamp = ( (string)$timestamp === '' ? time() : $timestamp );
	}

	public function getCallback() {
		return array($this, 'callback');
	}

	public function callback($matches) {
		$replacement = $matches[2];

		switch ( $replacement ) {
			case 'D':
				$replacement = Localizer::getInstance()->get('date.abbr_day_names.'.date('w', $this->timestamp));
				break;

			case 'l':
				$replacement = Localizer::getInstance()->get('date.day_names.'.date('w', $this->timestamp));
				break;

			case 'F':
				$replacement = Localizer::getInstance()->get('date.month_names.'.(date('n', $this->timestamp)));
				break;

			case 'M':
				$replacement = Localizer::getInstance()->get('date.abbr_month_names.'.(date('n', $this->timestamp)));
				break;

			case '\\':
			default:
				return $matches[0];
		}

		$result = array();

		$length = strlen($replacement);
		for ($i = 0; $i < $length; $i++)
			$result[] = $replacement[$i];

		return '\\'.implode('\\', $result);
	}

}

class PluginIXml extends iXml\iXml {

	/**
	 * @var array An associative array logging API calls to prevent infinite recursion.
	 */
	static private $apiCallStack = array();

	/**
	 * Groups specify how the duration is grouped (e.g. hh:mm:ss), with the
	 * first number being the divisor and the second figure specifying the
	 * number of digits to display.
	 *
	 * @var array
	 */
	private $durationDivisors = array(
		'seconds'  => array('initialDivisor' =>     1, 'groups' => array([60, 2], [60, 2], [24, 2]) ),
		'minutes'  => array('initialDivisor' =>    60, 'groups' => array([60, 2], [24, 2]) ),
		'hours'    => array('initialDivisor' =>  3600, 'groups' => array([24, 2]) ),
		'halfdays' => array('initialDivisor' => 43200, 'groups' => array() ),
		'days'     => array('initialDivisor' => 86400, 'groups' => array() ),
	);

	private $durationDivisors2 = array(
		'seconds'  => array('initialDivisor' =>     1, 'groups' => array([60, 2], [60, 2]) ),
		'minutes'  => array('initialDivisor' =>    60, 'groups' => array([60, 2]) ),
		'hours'    => array('initialDivisor' =>  3600, 'groups' => array() ),
		'halfdays' => array('initialDivisor' => 43200, 'groups' => array() ),
		'days'     => array('initialDivisor' => 86400, 'groups' => array() ),
	);

	private $callStack = array();

	/**
	 * @var IPluginDebugger
	 */
	private $debugger;

	/**
	 * @var int
	 */
	private $currentDebugLine;

	/**
	 * @var Account
	 */
	private $account;

	/**
	 * @var Domain
	 */
	private $domain;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var bool Set to TRUE if "<warn />" should behave the same as "<error />".
	 *     Use FALSE to ignore warnings. Default is FALSE.
	 */
	private $failOnWarning = true;

	static public function getIncludeFileName($fileName) {
		return mb_strtolower($fileName, 'UTF-8');
	}

	/**
	 * Checks if execution is currently from within a plugin.
	 *
	 * @return bool
	 * @uses $apiCallStack
	 */
	static public function inPlugin() {
		return !empty(self::$apiCallStack);
	}

	public function __construct($code, Account $account, Domain $domain = null, User $user = null, IPluginDebugger $debugger = null) {
		$this->setDebugger($debugger);

		$this->account  = $account;
		$this->domain   = $domain;
		$this->user     = $user;

		parent::__construct($code);
	}

	protected function initSchema() {
		parent::initSchema();

		$this->schema = array(
			'API'                 => array(
				\Zeyon\SIGN_VAR   => array('VAR', 'VAR_PARAMS'),
				\Zeyon\SIGN_ATTR  => array(
					'NAME'        => \Zeyon\TYPE_STRING,
					'DO'          => \Zeyon\TYPE_STRING,
				),
				// No "\Zeyon\SIGN_STORE" here to prevent "run()" from overwriting our data
			),
			'ARRAY:GROUP'         => array(
				\Zeyon\SIGN_VAR   => array('VAR', 'VAR_PARAMS', 'KEYFUNC', 'VAR_RESULT'),
				\Zeyon\SIGN_ATTR  => array(
					'KEY'         => false,
				),
				// No "\Zeyon\SIGN_STORE" here to prevent "run()" from overwriting our data
			),
			'BOOKINGTIME'         => array(
				\Zeyon\SIGN_ATTR  => array(
					'START'       => \Zeyon\TYPE_INT,
					'END'         => \Zeyon\TYPE_INT,
					'BREAK'       => array(\Zeyon\TYPE_INT, 0),
					'UNIT'        => \Zeyon\TYPE_STRING,
					'ROUND'       => \Zeyon\TYPE_STRING,
				),
				\Zeyon\SIGN_CDATA => \Zeyon\TYPE_STRING,
				\Zeyon\SIGN_STORE => 'VAR',
			),
			'COMMENT'             => array(),
			'DATE:DAYS'           => array(
				\Zeyon\SIGN_ATTR  => array(
					'START'       => \Zeyon\TYPE_INT,
					'END'         => \Zeyon\TYPE_INT,
					'TIMEZONE'    => \Zeyon\TYPE_STRING,
				),
				\Zeyon\SIGN_STORE => 'VAR',
			),
			'DATE:FORMAT'         => array(
				\Zeyon\SIGN_ATTR  => array(
					'FORMAT'      => array(\Zeyon\TYPE_STRING, 'c'),
					'LOCALIZE'    => array(\Zeyon\TYPE_BOOL, false),
					'TIMEZONE'    => false,
				),
				\Zeyon\SIGN_CDATA => array(\Zeyon\TYPE_INT, null),
				\Zeyon\SIGN_STORE => 'VAR',
			),
			'DATE:PARSE2'         => array(
				\Zeyon\SIGN_ATTR  => array(
					'DEFAULT'     => array(\Zeyon\TYPE_INT, null),
					'REF'         => \Zeyon\TYPE_STRING,
					'TIMEZONE'    => \Zeyon\TYPE_STRING,
				),
				\Zeyon\SIGN_CDATA => \Zeyon\TYPE_STRING,
				\Zeyon\SIGN_STORE => 'VAR',
			),
			'DATE:RANGE'          => array(
				\Zeyon\SIGN_CDATA => \Zeyon\TYPE_STRING,
				\Zeyon\SIGN_VAR   => 'VAR',
				\Zeyon\SIGN_STORE => 'VAR_RESULT',
			),
			'DURATION'            => array(
				\Zeyon\SIGN_ATTR  => array(
					'CONVERT'     => array(\Zeyon\TYPE_BOOL, true),
					'UNIT'        => array(\Zeyon\TYPE_STRING, null),
					'FROM'        => array(\Zeyon\TYPE_STRING, null),
					'MODE'        => array(\Zeyon\TYPE_STRING, null),
					'DECIMALS'    => array(\Zeyon\TYPE_INT, null),
				),
				\Zeyon\SIGN_CDATA => \Zeyon\TYPE_STRING,
				\Zeyon\SIGN_STORE => 'VAR',
			),
			'ENV'                 => array(
				\Zeyon\SIGN_ATTR  => array(
					'KEY'         => array(\Zeyon\TYPE_STRING, null),
					'USER'        => array(\Zeyon\TYPE_INT, null),
					'TYPE'        => array(\Zeyon\TYPE_STRING, null),
				),
				\Zeyon\SIGN_STORE => 'VAR',
			),
			'ERROR'               => array(
				\Zeyon\SIGN_CDATA => \Zeyon\TYPE_STRING,
				\Zeyon\SIGN_VAR   => 'VAR',
			),
			'HTML:ENTITIES'       => array(
				\Zeyon\SIGN_CDATA => \Zeyon\TYPE_STRING,
				\Zeyon\SIGN_STORE => 'VAR',
			),
			'T'                   => array( // Node for text - iXML does not allow text nodes to be combined with non-text nodes on the same level
				\Zeyon\SIGN_CDATA => \Zeyon\TYPE_STRING,
			),
		) + $this->schema;
	}

	public function getFailOnWarning($fail) {
		return $this->failOnWarning;
	}

	public function setFailOnWarning($fail) {
		$this->failOnWarning = $fail;
		return $this;
	}

	public function setDebugger(IPluginDebugger $debugger = null) {
		$this->debugger = $debugger;
		return $this;
	}

	/**
	 * Returns all defined global variables in unboxed form (i.e. {@link \Zeyon\iXmlArray} instances converted to arrays etc.).
	 *
	 * @return array
	 */
	public function getGlobals() {
		return \Zeyon\iXml::export($this->global);
	}

	public function getMyVar($name) {
		return $this->getVar(array($name));
	}

	private function getVarFunction($func) {
		$vars = $this->local;

		if ( !isset($vars[ $name = $func[0] ]) ) {
			$vars = $this->global;

			if ( !isset($vars[$name]) or array_key_exists($name, $this->local) )
				goto undefined;
		}

		if ( isset($func[1]) ) {
			foreach ($func[1] as $key) {
				if (!( $that = $vars[$name] ) instanceof \Zeyon\iXmlArray)
					goto undefined;

				$vars = $that->array;

				if (!isset($vars[ $name = $key ]))
					goto undefined;
			}
		} else {
			$that = null;
		}

		if (( $function = $vars[$name] ) instanceof \Zeyon\iXmlFunction)
			return array($function, $that);

	undefined:
		throw new \Zeyon\iXmlException("Undefined or invalid function '".$this->getVarDebug($func)."'");
	}

	protected function debugOutput($data) {
		if ( $this->debugger !== null )
			$this->debugger->write($data);
	}

	protected function debugLog($message) {
		if ( $this->debugger !== null )
			$this->debugger->log($message);
	}

	protected function getFileContents($fileName) {
		$plugin = PluginQuery::create()
			->findOneByIdentifier($fileName);

		if ( $plugin === null )
			throw new \Zeyon\iXmlException('Could not find plugin "'.$fileName.'".');

		return $plugin->getCode();
	}

	/**
	 * Converts an iXML parameter array to an associative parameter array.
	 *
	 * @param array $parameters An array of parameter data as returned by
	 *     {@link self::runStruct()}.
	 * @return array An associative array mapping parameter names to values.
	 */
	private function toApiParameters(array $parameters = null) {
		$result = array();

		foreach ($parameters as $parameter)
			$result[$parameter[0][0]] = $parameter[1];

		return $result;
	}

	public function getCallStack() {
		return $this->callStack;
	}

	public function getCurrentDebugLine() {
		return $this->currentDebugLine;
	}

	protected function varToString($variable) {
		if ( !is_array($variable) )
			return $variable;

		$result = array();

		foreach ($variable as $part) {
			if ( $part === null )
				continue;
			else
				$result[] = ( is_array($part) ? $this->varToString($part) : $part );
		}

		return implode('.', $result);
	}

	private function toTagString($node) {
		$attributes = array();

		$elements = $node;
		if ( !empty($node['attr_t']) )
			$elements += $node['attr_t'];

		foreach ($elements as $key => $values) {
			if ( preg_match('`[a-z]`', $key) or is_int($key) ) // Attributes were upper-cased; skip lower-case items
				continue;

			if ( $values === null )
				continue;

			$prefix = strtolower($key).'="';

			if ( !is_array($values) ) {
				$attributes[] = $prefix.htmlentities($this->varToString($values)).'"';
				continue;
			}

			switch ( count($values) ) {
				case 1:
					$attributes[] = $prefix.htmlentities($this->varToString($values[0])).'"';
					break;
				case 2:
					$attributes[] = $prefix.htmlentities($this->varToString($values[1])).'"';
					break;
				default:
					$attributes[] = $prefix.htmlentities(json_encode($values)).'"';
					break;
			}
		}

		return '<'.strtolower(substr($node[\Zeyon\SIGN_FUNC], 1)).( empty($attributes) ? '' : ' '.implode(' ', $attributes) ).' />, line '.$node['__line'];
	}

	/**
	 * Converts and formats a duration.
	 *
	 * @param double $duration The duration to convert or format.
	 * @param string $unit The output (display) unit.
	 * @param bool $omitInitialDivisor Optional. If TRUE, the duration will be
	 *     taken as is, otherwise it will be converted from seconds to the
	 *     output unit. Default is FALSE.
	 * @param string $fromUnit Optional. The unit to convert from. Default is NULL.
	 * @param int $decimals Optional. Default is NULL.
	 * @param array $durationDivisors An array of unit conversion data.
	 * @return string|double
	 */
	private function formatDuration($duration, $unit, $omitInitialDivisor = false, $fromUnit = null, $decimals = null, array $durationDivisors = null) {
		if ( !isset($durationDivisors[$unit]) )
			throw new Exception('Invalid unit "'.$unit.'".');

		if ( $duration < 0 ) {
			$sign     = '-';
			$duration = -$duration;
		} else {
			$sign     = '';
		}

		$data     = $durationDivisors[$unit];

		if ( $omitInitialDivisor ) {
			$value = round($duration, (int)$decimals);
		} else {
			$divisor = $data['initialDivisor'];
			if ( (string)$fromUnit === '' )
				; // No unit conversion
			elseif ( !isset($durationDivisors[$fromUnit]) )
				throw new Exception('Invalid unit "'.$fromUnit.'".');
			else
				$divisor = $divisor / $durationDivisors[$fromUnit]['initialDivisor'];

			$value = round($duration / $divisor, (int)$decimals);
		}

		$result   = array();
		$divisors = $data['groups'];

		foreach ($divisors as $divisorData){
			list($divisor, $digits) = $divisorData;
			$remainder = $value % $divisor;
			$value     = floor($value / $divisor);

			array_unshift($result, str_pad($remainder, $digits, '0', STR_PAD_LEFT));

			if ( $value === 0 )
				break;
		}

		if ( ($value !== 0) or !count($result) )
			array_unshift($result, $value);

		return $sign.implode(':', $result);
	}

	protected function startTagCallback($parser, $name, $attr) {
		$result = parent::startTagCallback($parser, $name, $attr);
		$this->stack[$this->index]['__line'] = xml_get_current_line_number($parser);
		return $result;
	}

	/**
	 * Patches the original {@link run()} to support output from multiple non-text nodes to be combined.
	 *
	 * @param array $children
	 * @return mixed
	 */
	protected function run($elems) {
		$result = null;

		foreach ($elems as $elem) {
			$this->callStack[] = $this->toTagString($elem);

			isset($elem[\Zeyon\SIGN_MAP]) AND $elem = $this->map($elem);

			$func = $elem[\Zeyon\SIGN_FUNC];
			$return = ( $func[0] === '_' ? $this->{$func}($elem) : $func($elem[\Zeyon\SIGN_CDATA]) );

			if ( $return !== null )
				$result = ( $result === null ? $return : $result.$return );

			if ( isset($elem[\Zeyon\SIGN_STORE]) )
				$this->setVar($elem[$elem[\Zeyon\SIGN_STORE]], $return);

			array_pop($this->callStack);
		}

		return $result;
	}

	private function parseTime2($timeString, $reference, $default = false) {
		$timeString = preg_replace('/(\d{1,2})\.\s*(\d{1,2})\.\s*(\d{2,4})/', '$2/$1/$3', trim($timeString), 1);

		if ( $timeString == '' )
			return ( $default === false ? time() : $default );

		if ( !is_numeric($reference) or ((float)(int)$reference !== (float)$reference) )
			throw new Exception('Expected integer in reference value but got "'.$reference.'".');

		$time = strtotime($timeString, (int)$reference);
		return ( $time === false ? ($default === false ? time() : $default) : $time );
	}

	protected function getUser(User $queryUser, $userId = null) {
		if ( ((string)$userId === '') or ((int)$queryUser->getId() === (int)$userId) )
			return $queryUser;

		$query = UserQuery::create()
			->filterByAccountId($queryUser->getAccountId());

		if ( $queryUser->isAdmin() ) {
		} elseif ( $queryUser->getManagerOf() ) {
			$query->filterByDomainId($queryUser->getDomainId());
		} else {
			throw new Exception('User #'.$userId.' not found.');
		}

		$user = $query->findOneById($userId);
		if ( $user === null )
			throw new Exception('User #'.$userId.' not found.');

		return $user;
	}

	protected function getApiUrl() {
		$ssl  = ( !empty($_SERVER['HTTPS']) and ($_SERVER['HTTPS'] === 'on') );
		$port = ':'.$_SERVER['SERVER_PORT'];

		switch ( $port ) {
			case ':80':
				if ( !$ssl )
					$port = '';

				break;

			case ':443':
				if ( $ssl )
					$port = '';

				break;
		}

		return ( $ssl ? 'https://' : 'http://' ).$_SERVER['SERVER_NAME'].$port.$_SERVER['SCRIPT_NAME'];
	}

	protected function runSub($elems, $vars) {
		$preserve = $local =& $this->local;
		$local    = $vars;

		$e        = null;

		$result   = null;

		try {
			$result = $this->run($elems);
		} catch (Exception $e) {
		}

		$local    = $preserve;

		if ( $e and !($e instanceof \Zeyon\iXmlReturn) )
			throw $e;

		return $result;
	}

	protected function _API($elem) {
		$name    = strtolower($elem['NAME']);
		$command = strtolower($elem['DO']);
		$callId  = $name.'.'.$command;

		if ( ((string)$name === '') or ((string)$command === '') )
			throw new Exception('iXML: <api> tag must specify non-empty "name" and "do" attributes.');
		elseif ( isset(self::$apiCallStack[$callId]) )
			throw new Exception('iXML: Cannot recursively call API function "'.$callId.'".');

		$result = null;

		// Push API call
		self::$apiCallStack[$callId] = true;

		try {
			//$parameters = $this->toApiParameters($this->runParams($elem));
			$parameters = array();
			foreach ($this->runParams($elem) as $paramName => $paramData)
				$parameters[$paramName] = \Zeyon\iXml::export($paramData);

			$result = APIFactory::get($name)->dispatch($command, $parameters);

			if ( $elem['VAR'] )
				$this->setVar($elem['VAR'], \Zeyon\iXml::import($result));

		} catch (Exception $e) {
			unset(self::$apiCallStack[$callId]);
			throw $e;
		}

		unset(self::$apiCallStack[$callId]);

		return json_encode($result);
	}

	protected function _ARRAY_GROUP($elem) {
		if ( ((string)$elem['KEY'] !== '') or ((string)$elem['KEYFUNC'] === '') )
			return parent::_ARRAY_GROUP($elem);

		$result      = array();

		$items       = $this->getVarArray($elem['VAR']);
		$baseParams  = $this->runParams($elem);

		$keyFunction = $this->getVarFunction($elem['KEYFUNC']);
		if ( $keyFunction === null )
			throw new Exception('Undefined key function.');

		$function   = $keyFunction[0];

		foreach ($items as $index => $item) {
			$parameters = $baseParams;
			$parameters['item'] = $item;

			if ( $function instanceof \Zeyon\iXmlBind ) {
				$groups = \Zeyon\iXml::export($function->call($parameters));

			} else {
				// "runSub()" yields the function's value in "$arguments['return']"
				$return = null;

				$arguments = array(
					'arguments' => new \Zeyon\iXmlArray($parameters),
				) + $parameters;
				$arguments['return'] =& $return;

				$this->runSub($function->elems, $arguments);
				$groups = \Zeyon\iXml::export($return);

			}

			if ( $groups === null )
				continue;
			elseif ( !is_array($groups) )
				$groups = array($groups);

			foreach ($groups as $intermediateGroup) {
				if ( isset($result[$intermediateGroup]) )
					$result[$intermediateGroup]->array[] = $item;
				else
					$result[$intermediateGroup] = new \Zeyon\iXmlArray(array($item));
			}
		}

		if ( $elem['VAR_RESULT'] )
			$this->setVarArray($elem['VAR_RESULT'], $result);
	}

	protected function _BOOKINGTIME($elem) {
		$unit    = $elem['UNIT'];
		$start   = $elem['START'];
		$end     = $elem['END'];
		$break   = $elem['BREAK'];

		$round   = $elem['ROUND'];
		if ( (string)$round === '' )
			$round = BookingTypePeer::ROUND_CEIL;

		return BookingTypePeer::timeToDuration($start, $end, $break, $unit, $round);
	}

	protected function _CALL($elem) {
		$this->currentDebugLine = $elem['__line'];
		$this->debugOutput('CALL '.$this->varToString($elem['FUNC']));

		try {
			$r = parent::_CALL($elem);
		} catch (Exception $e) {
			$this->debugOutput(__METHOD__.': '.$e->__toString());
			throw $e;
		}

		return $r;
	}

	protected function _COMMENT($elem) {
		return null;
	}

	protected function _DATE_DAYS($elem, $timezone = true) {
		if ( $timezone )
			return $this->runTimeZone($elem);

		$tz    = new DateTimeZone(date_default_timezone_get());

		$start = new DateTime('@'.$elem['START'], $tz);
		$start->setTimezone($tz);

		$end   = new DateTime('@'.$elem['END'], $tz);
		$end->setTimezone($tz);

		return $start->diff($end)->days;
	}

	protected function _DATE_FORMAT($elem, $timezone = true) {
		if ( $timezone )
			return $this->runTimeZone($elem);

		$format    = $elem['FORMAT'];
		$timestamp = $elem[\Zeyon\SIGN_CDATA];

		if ( (bool)$elem['LOCALIZE'] ) {
			$replacer = new DateFormatReplacer($timestamp);
			$format   = preg_replace_callback(
				'`(\\\\?)(.)`',
				$replacer->getCallback(),
				$format
			);
		}

		return ( $timestamp ) === null ? date($format) : date($format, $timestamp);
	}

	protected function _DATE_PARSE2($elem, $timezone = true) {
		return ( $timezone ? $this->runTimeZone($elem) : $this->parseTime2($elem[\Zeyon\SIGN_CDATA], $elem['REF'], $elem['DEFAULT']) );
	}

	protected function _DATE_RANGE($elem) {
		$start = null;
		$end   = null;

		foreach (\Zeyon\iXml::export($this->getVar($elem['VAR'])) as $item) {
			if ( isset($item['Start']) ) {
				if ( ($item['Start'] < $start) or ($start === null) )
					$start = $item['Start'];

				if ( ($item['Start'] > $end) or ($end === null) )
					$end = $item['Start'];
			}

			if ( isset($item['End']) ) {
				if ( ($item['End'] < $start) or ($start === null) )
					$start = $item['End'];

				if ( ($item['End'] > $end) or ($end === null) )
					$end = $item['End'];
			}
		}

		return new \Zeyon\iXmlArray(array(
			'Start' => $start,
			'End'   => $end,
		));
	}

	protected function _DB_CONNECTION($elem) {
		throw new Exception('Database connections are disabled.');
	}

	protected function _DB_TRANSACTION($elem) {
		if ( !isset($elem[\Zeyon\SIGN_CHILD]) )
			return null;

		$con = Propel::getConnection();

		if ( !$con->beginTransaction() )
			throw new Exception('Could not start transaction.');

		try {
			$result = $this->run($elem[\Zeyon\SIGN_CHILD]);

		} catch (iXmlExit $e) {
			if ( !$con->commit() )
				throw new Exception('Could not commit transaction.');

			throw $e;

		} catch (\Exception $e) {
			$con->rollBack();
			throw $e;
		}

		if ( !$con->commit() )
			throw new Exception('Could not commit transaction.');

		return $result;
	}

	protected function _DEBUG_OUTPUT($elem) {
		$this->currentDebugLine = $elem['__line'];
		parent::_DEBUG_OUTPUT($elem);
	}

	protected function _DEBUG_DUMP($elem) {
		$this->currentDebugLine = $elem['__line'];
		parent::_DEBUG_DUMP($elem);
	}

	protected function _DEBUG_LOG($elem) {
		$this->currentDebugLine = $elem['__line'];
		parent::_DEBUG_LOG($elem);
	}

	protected function _DURATION($elem) {
		return $this->formatDuration(
			$elem[\Zeyon\SIGN_CDATA],
			( (string)$elem['UNIT'] === '' ? 'seconds' : $elem['UNIT'] ),
			!(bool)$elem['CONVERT'],
			$elem['FROM'],
			$elem['DECIMALS'],
			( $elem['MODE'] === 'simple' ? $this->durationDivisors2 : $this->durationDivisors )
		);
	}

	/**
	 * Returns a JSON-encoded string for the specified data.
	 *
	 * Overrides the parent to return null if the supplied variable is undefined.
	 *
	 * @param array $elem
	 * @return string
	 */
	protected function _ENCODE_JSON($elem) {
		$result = parent::_ENCODE_JSON($elem);
		return ( $result === '' ? 'null' : $result );
	}

	protected function _ENV($elem) {
		$key    = (string)$elem['KEY'];

		switch ( $elem['TYPE'] ) {
			case 'tymio':
				switch ( $key ) {
					case 'API_URL':
						return $this->getApiUrl();

					default:
						return null;
				}

			case 'server':
				return ( isset($_SERVER[$key]) ? $_SERVER[$key] : null );

			case 'property':
			default:
				$userId = (string)$elem['USER'];
				$user   = $this->getUser($this->user, $userId);

				return (
					$key === ''
					? PropertyPeer::getAll($this->account, null, $user)
					: PropertyPeer::get($key, $this->account, null, $user)
				);
		}
	}

	protected function _ERROR($elem) {
		$var = $elem['VAR'];
		throw new \Zeyon\iXmlUserException($elem[\Zeyon\SIGN_CDATA].( $var == null ? '' : ': '.json_encode(\Zeyon\iXml::export($this->getVar($var))) ));
	}

	protected function _HTML_ENTITIES($elem) {
		return HTML::entities($elem[\Zeyon\SIGN_CDATA]);
	}

	protected function _INCLUDE($elem) {
		$params = $this->runParams($elem);

		$filename = self::getIncludeFileName($elem['FILENAME']);

		if ( $elem['ONCE'] ) {
			if ( $included =& $this->includeonce[$filename] )
				return;

			$included = true;
		}

		try {
			$root = $this->parse($this->getFileContents($filename));
		} catch (\Zeyon\iXmlParserException $e) {
			throw new \Zeyon\iXmlException("Unable to parse '$filename'->".$e->getMessage());
		}

		if ( isset($root[\Zeyon\SIGN_CHILD]) ) {
			$vars = $params;
			$vars['return'] =& $return;
			$params and $vars['arguments'] = new \Zeyon\iXmlArray($params);

			$this->runSub($root[\Zeyon\SIGN_CHILD], $vars);

			return $return;
		}
	}

	protected function _T($elem) {
		return $elem[\Zeyon\SIGN_CDATA];
	}

}
