<?php



/**
 * This class defines the structure of the 'holiday_domain' table.
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
class HolidayDomainTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'tymio.map.HolidayDomainTableMap';

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
        $this->setName('holiday_domain');
        $this->setPhpName('HolidayDomain');
        $this->setClassname('HolidayDomain');
        $this->setPackage('tymio');
        $this->setUseIdGenerator(false);
        // columns
        $this->addForeignPrimaryKey('holiday_id', 'HolidayId', 'INTEGER' , 'holiday', 'id', true, null, null);
        $this->addForeignPrimaryKey('domain_id', 'DomainId', 'INTEGER' , 'domain', 'id', true, null, null);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Domain', 'Domain', RelationMap::MANY_TO_ONE, array('domain_id' => 'id', ), 'CASCADE', 'CASCADE');
        $this->addRelation('Holiday', 'Holiday', RelationMap::MANY_TO_ONE, array('holiday_id' => 'id', ), 'CASCADE', 'CASCADE');
    } // buildRelations()

} // HolidayDomainTableMap
