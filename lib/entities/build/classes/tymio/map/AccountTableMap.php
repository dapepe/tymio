<?php



/**
 * This class defines the structure of the 'account' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    propel.generator.tymio.map
 */
class AccountTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'tymio.map.AccountTableMap';

    /**
     * Initialize the table attributes, columns and validators
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('account');
        $this->setPhpName('Account');
        $this->setClassname('Account');
        $this->setPackage('tymio');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('address_id', 'AddressId', 'INTEGER', 'address', 'id', true, null, null);
        $this->addColumn('identifier', 'Identifier', 'VARCHAR', true, 45, null);
        $this->addColumn('name', 'Name', 'VARCHAR', true, 255, null);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Address', 'Address', RelationMap::MANY_TO_ONE, array('address_id' => 'id', ), null, 'CASCADE');
        $this->addRelation('BookingType', 'BookingType', RelationMap::ONE_TO_MANY, array('id' => 'account_id', ), 'CASCADE', 'CASCADE', 'BookingTypes');
        $this->addRelation('ClockingType', 'ClockingType', RelationMap::ONE_TO_MANY, array('id' => 'account_id', ), 'CASCADE', 'CASCADE', 'ClockingTypes');
        $this->addRelation('Domain', 'Domain', RelationMap::ONE_TO_MANY, array('id' => 'account_id', ), 'CASCADE', 'CASCADE', 'Domains');
        $this->addRelation('Holiday', 'Holiday', RelationMap::ONE_TO_MANY, array('id' => 'account_id', ), 'CASCADE', 'CASCADE', 'Holidays');
        $this->addRelation('Plugin', 'Plugin', RelationMap::ONE_TO_MANY, array('id' => 'account_id', ), 'CASCADE', 'CASCADE', 'Plugins');
        $this->addRelation('Property', 'Property', RelationMap::ONE_TO_MANY, array('id' => 'account_id', ), 'CASCADE', 'CASCADE', 'Propertys');
        $this->addRelation('User', 'User', RelationMap::ONE_TO_MANY, array('id' => 'account_id', ), 'CASCADE', 'CASCADE', 'Users');
    } // buildRelations()

} // AccountTableMap
