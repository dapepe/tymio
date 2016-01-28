<?php

/**
 * LDAP wrapper.
 */
class LDAP {

	private $link = null;

	static public function escape($text) {
		return preg_replace('`([\\#+<>,;"=\s])`', '\\\\$1', $text);
	}

	public function __construct($host, $userName = null, $password = null, array $options = null, $port = null) {
		if ( !is_callable('ldap_connect') or !is_callable('ldap_bind') )
			throw new Exception('PHP LDAP extension not loaded.');

		$this->link = ( $port === null ? @ldap_connect($host) : @ldap_connect($host, $port) );

		$connectParameters = array($this->link);
		if ( $userName !== null ) {
			$connectParameters[] = $userName;
			if ( $password !== null )
				$connectParameters[] = $password;
		}

		if ( $options !== null )
			$this->setOptions($options);

		if ( !@call_user_func_array('ldap_bind', $connectParameters) )
			throw new Exception('Cannot connect to host "'.$host.'" ('.ldap_error($this->link).').');
	}

	public function __destruct() {
		$this->close();
	}

	public function close() {
		if ( is_resource($this->link) ) {
			@ldap_unbind($this->link);
			$this->link = null;
		}
	}

	protected function setOptions(array $options) {
		foreach ($options as $name => $value)
			@ldap_set_option($this->link, $name, $value);
	}

	public function search($baseDN, $filter, $attributeFilter = null) {
		$parameters = func_get_args();
		array_unshift($parameters, $this->link);

		$search = call_user_func_array('ldap_search', $parameters);
		if ( !is_resource($search) )
			return false;

		return ldap_get_entries($this->link, $search);
	}

}

?>
