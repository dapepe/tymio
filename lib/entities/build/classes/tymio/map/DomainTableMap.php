<?php



/**
 * This class defines the structure of the 'domain' table.
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
class DomainTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'tymio.map.DomainTableMap';

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
        $this->setName('domain');
        $this->setPhpName('Domain');
        $this->setClassname('Domain');
        $this->setPackage('tymio');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('account_id', 'AccountId', 'INTEGER', 'account', 'id', true, null, null);
        $this->addForeignKey('address_id', 'AddressId', 'INTEGER', 'address', 'id', false, null, null);
        $this->addColumn('name', 'Name', 'VARCHAR', true, 45, null);
        $this->addColumn('valid', 'Valid', 'BOOLEAN', false, 1, null);
        $this->addColumn('description', 'Description', 'VARCHAR', false, 255, null);
        $this->addColumn('number', 'Number', 'VARCHAR', false, 45, null);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Account', 'Account', RelationMap::MANY_TO_ONE, array('account_id' => 'id', ), 'CASCADE', 'CASCADE');
        $this->addRelation('Address', 'Address', RelationMap::MANY_TO_ONE, array('address_id' => 'id', ), null, 'CASCADE');
        $this->addRelation('HolidayDomain', 'HolidayDomain', RelationMap::ONE_TO_MANY, array('id' => 'domain_id', ), 'CASCADE', 'CASCADE', 'HolidayDomains');
        $this->addRelation('PropertyValue', 'PropertyValue', RelationMap::ONE_TO_MANY, array('id' => 'domain_id', ), 'CASCADE', 'CASCADE', 'PropertyValues');
        $this->addRelation('User', 'User', RelationMap::ONE_TO_MANY, array('id' => 'domain_id', ), 'CASCADE', 'CASCADE', 'Users');
    } // buildRelations()

} // DomainTableMap
