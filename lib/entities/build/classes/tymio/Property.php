<?php



/**
 * Skeleton subclass for representing a row from the 'property' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.tymio
 */
class Property extends BaseProperty {
	/**
	 * Sets the property value for a base object (User, Domain or Account)
	 * 
	 * @param User|Domain|Account $obj The reference object
	 * @param mixed $v The property value
	 * @return void
	 */
	public function setValue($obj, $v) {
		$this->getValueObject($obj)
		     ->setValue($v)
		     ->save();
	}
	
	/**
	 * Returns the property value for a base object (User, Domain or Account)
	 * 
	 * @param User|Domain|Account $obj The reference object
	 * @return mixed
	 */
	public function getValue($obj) {
		$this->getValueObject($obj)
		     ->getValue();
	}
	
	/**
	 * Returns a PropertyValue object for the specified base object (User, Domain or Account)
	 * 
	 * @param User|Domain|Account $obj The reference object
	 * @throws Exception
	 * @return PropertyValue
	 */
	private function getValueObject($obj) {
			$query = PropertyValueQuery::create();
		         
		if ($obj instanceof User)
			$query->filterByUser($obj);
		elseif ($obj instanceof Domain)
			$query->filterByDomain($obj);
		elseif ($obj instanceof Account)
			$query->filterByAccount($obj);
		else
			throw new Exception('Invalid base object applied. User, Domain or Account expected!');
		
		$property_value = $query->findOneByProperty($this);
		
		if (!$property_value) {
			$property_value = new PropertyValue();
			$property_value->setProperty($this);
			if ($obj instanceof User)
				$property_value->setUser($obj);
			elseif ($obj instanceof Domain)
				$property_value->setDomain($obj);
			elseif ($obj instanceof Account)
				$property_value->setAccount($obj);
		}
		
		return $property_value;
	}
} // Property
