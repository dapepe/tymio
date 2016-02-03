<?php



/**
 * This class defines the structure of the 'clocking_type' table.
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
class ClockingTypeTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'tymio.map.ClockingTypeTableMap';

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
        $this->setName('clocking_type');
        $this->setPhpName('ClockingType');
        $this->setClassname('ClockingType');
        $this->setPackage('tymio');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addForeignPrimaryKey('account_id', 'AccountId', 'INTEGER' , 'account', 'id', true, null, null);
        $this->addColumn('identifier', 'Identifier', 'VARCHAR', true, 255, null);
        $this->addColumn('label', 'Label', 'VARCHAR', true, 255, null);
        $this->addColumn('whole_day', 'WholeDay', 'BOOLEAN', true, 1, false);
        $this->addColumn('future_grace_time', 'FutureGraceTime', 'BIGINT', false, 19, null);
        $this->addColumn('past_grace_time', 'PastGraceTime', 'BIGINT', false, 19, null);
        $this->addColumn('approval_required', 'ApprovalRequired', 'BOOLEAN', true, 1, false);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Account', 'Account', RelationMap::MANY_TO_ONE, array('account_id' => 'id', ), 'CASCADE', 'CASCADE');
        $this->addRelation('Clocking', 'Clocking', RelationMap::ONE_TO_MANY, array('id' => 'type_id', ), 'CASCADE', 'CASCADE', 'Clockings');
    } // buildRelations()

} // ClockingTypeTableMap
