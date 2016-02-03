<?php



/**
 * This class defines the structure of the 'transaction_clocking' table.
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
class TransactionClockingTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'tymio.map.TransactionClockingTableMap';

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
        $this->setName('transaction_clocking');
        $this->setPhpName('TransactionClocking');
        $this->setClassname('TransactionClocking');
        $this->setPackage('tymio');
        $this->setUseIdGenerator(false);
        // columns
        $this->addForeignPrimaryKey('transaction_id', 'TransactionId', 'INTEGER' , 'transaction', 'id', true, null, null);
        $this->addForeignPrimaryKey('clocking_id', 'ClockingId', 'INTEGER' , 'clocking', 'id', true, null, null);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Clocking', 'Clocking', RelationMap::MANY_TO_ONE, array('clocking_id' => 'id', ), 'CASCADE', 'CASCADE');
        $this->addRelation('Transaction', 'Transaction', RelationMap::MANY_TO_ONE, array('transaction_id' => 'id', ), 'CASCADE', 'CASCADE');
    } // buildRelations()

} // TransactionClockingTableMap
