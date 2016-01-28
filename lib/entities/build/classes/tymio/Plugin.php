<?php



/**
 * Skeleton subclass for representing a row from the 'plugin' table.
 *
 * @package    propel.generator.tymio
 */
class Plugin extends BasePlugin {

	public function setIdentifier($identifier) {
		return parent::setIdentifier(PluginIXml::getIncludeFileName($identifier));
	}

	/**
	 * @param User $user Optional. Default is NULL.
	 * @param array $parameters Optional. An associative array of parameters.
	 *     See also {@link PluginPeer::buildParameters()}. Default is NULL.
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return PluginSandbox
	 */
	public function execute(User $user = null, array $parameters = null, PropelPDO $con = null) {
		if ( $user === null ) {
			$account = $this->getAccount($con);
			$domain  = null;
		} else {
			$account = $user->getAccount($con);
			$domain  = $user->getDomain($con);
		}

		return new PluginSandbox($this, $account, $domain, $user, $parameters);
	}

	public function getAutoPriority(PropelPDO $con = null) {
		if ( $this->isNew() ) {
			$priority = null;
		} else {
			$priority = PluginQuery::create()
				->select('Priority')
				->filterById($this->getId())
				->findOne($con);
		}

		if ( $priority === null ) {
			$priority = PluginQuery::create()
				->withColumn('MAX(Priority)+1', 'NextPriority')
				->select('NextPriority')
				->filterByAccountId($this->getAccountId())
				->filterByEntity($this->getEntity())
				->filterByEvent($this->getEvent())
				->findOne($con);
		}

		return ( $priority === null ? 1 : $priority );
	}

	public function preSave(PropelPDO $con = null) {
		if ( (string)$this->getPriority() === '' )
			$this->setPriority($this->getAutoPriority($con));

		return parent::preSave($con);
	}

} // Plugin