<?php



/**
 * This class defines the structure of the 'clocking' table.
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
class ClockingTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'tymio.map.ClockingTableMap';

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
        $this->setName('clocking');
        $this->setPhpName('Clocking');
        $this->setClassname('Clocking');
        $this->setPackage('tymio');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('creator_id', 'CreatorId', 'INTEGER', 'user', 'id', false, null, null);
        $this->addForeignKey('user_id', 'UserId', 'INTEGER', 'user', 'id', true, null, null);
        $this->addForeignKey('type_id', 'TypeId', 'INTEGER', 'clocking_type', 'id', true, null, null);
        $this->addColumn('start', 'Start', 'TIMESTAMP', true, null, null);
        $this->addColumn('end', 'End', 'TIMESTAMP', true, null, null);
        $this->addColumn('breaktime', 'Breaktime', 'INTEGER', true, null, 0);
        $this->addColumn('comment', 'Comment', 'LONGVARCHAR', false, null, null);
        $this->addColumn('approval_status', 'ApprovalStatus', 'SMALLINT', true, 5, 0);
        $this->addColumn('deleted', 'Deleted', 'BOOLEAN', true, 1, false);
        $this->addColumn('frozen', 'Frozen', 'BOOLEAN', true, 1, false);
        $this->addColumn('creationdate', 'Creationdate', 'INTEGER', true, null, null);
        $this->addColumn('last_changed', 'LastChanged', 'INTEGER', true, null, null);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('UserRelatedByCreatorId', 'User', RelationMap::MANY_TO_ONE, array('creator_id' => 'id', ), 'SET NULL', 'CASCADE');
        $this->addRelation('ClockingType', 'ClockingType', RelationMap::MANY_TO_ONE, array('type_id' => 'id', ), 'CASCADE', 'CASCADE');
        $this->addRelation('UserRelatedByUserId', 'User', RelationMap::MANY_TO_ONE, array('user_id' => 'id', ), 'CASCADE', 'CASCADE');
        $this->addRelation('TransactionClocking', 'TransactionClocking', RelationMap::ONE_TO_MANY, array('id' => 'clocking_id', ), 'CASCADE', 'CASCADE', 'TransactionClockings');
    } // buildRelations()

} // ClockingTableMap
