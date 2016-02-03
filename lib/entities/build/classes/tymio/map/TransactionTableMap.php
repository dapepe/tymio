<?php



/**
 * This class defines the structure of the 'transaction' table.
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
class TransactionTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'tymio.map.TransactionTableMap';

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
        $this->setName('transaction');
        $this->setPhpName('Transaction');
        $this->setClassname('Transaction');
        $this->setPackage('tymio');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('creator_id', 'CreatorId', 'INTEGER', 'user', 'id', false, null, null);
        $this->addForeignKey('user_id', 'UserId', 'INTEGER', 'user', 'id', true, null, null);
        $this->addColumn('deleted', 'Deleted', 'TINYINT', true, null, 0);
        $this->addColumn('start', 'Start', 'DATE', true, null, null);
        $this->addColumn('end', 'End', 'DATE', true, null, null);
        $this->addColumn('creationdate', 'Creationdate', 'INTEGER', true, null, null);
        $this->addColumn('comment', 'Comment', 'LONGVARCHAR', false, null, null);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('UserRelatedByCreatorId', 'User', RelationMap::MANY_TO_ONE, array('creator_id' => 'id', ), 'SET NULL', 'CASCADE');
        $this->addRelation('UserRelatedByUserId', 'User', RelationMap::MANY_TO_ONE, array('user_id' => 'id', ), 'CASCADE', 'CASCADE');
        $this->addRelation('Booking', 'Booking', RelationMap::ONE_TO_MANY, array('id' => 'transaction_id', ), 'CASCADE', 'CASCADE', 'Bookings');
        $this->addRelation('TransactionClocking', 'TransactionClocking', RelationMap::ONE_TO_MANY, array('id' => 'transaction_id', ), 'CASCADE', 'CASCADE', 'TransactionClockings');
    } // buildRelations()

} // TransactionTableMap
