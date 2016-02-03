<?php



/**
 * This class defines the structure of the 'property_value' table.
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
class PropertyValueTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'tymio.map.PropertyValueTableMap';

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
        $this->setName('property_value');
        $this->setPhpName('PropertyValue');
        $this->setClassname('PropertyValue');
        $this->setPackage('tymio');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('property_id', 'PropertyId', 'INTEGER', 'property', 'id', true, null, null);
        $this->addForeignKey('domain_id', 'DomainId', 'INTEGER', 'domain', 'id', false, null, null);
        $this->addForeignKey('user_id', 'UserId', 'INTEGER', 'user', 'id', false, null, null);
        $this->addColumn('value', 'Value', 'CLOB', true, null, null);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Domain', 'Domain', RelationMap::MANY_TO_ONE, array('domain_id' => 'id', ), 'CASCADE', 'CASCADE');
        $this->addRelation('Property', 'Property', RelationMap::MANY_TO_ONE, array('property_id' => 'id', ), 'CASCADE', 'CASCADE');
        $this->addRelation('User', 'User', RelationMap::MANY_TO_ONE, array('user_id' => 'id', ), 'CASCADE', 'CASCADE');
    } // buildRelations()

} // PropertyValueTableMap
