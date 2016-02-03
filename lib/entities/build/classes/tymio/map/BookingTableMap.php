<?php



/**
 * This class defines the structure of the 'booking' table.
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
class BookingTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'tymio.map.BookingTableMap';

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
        $this->setName('booking');
        $this->setPhpName('Booking');
        $this->setClassname('Booking');
        $this->setPackage('tymio');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('transaction_id', 'TransactionId', 'INTEGER', 'transaction', 'id', true, null, null);
        $this->addForeignKey('booking_type_id', 'BookingTypeId', 'INTEGER', 'booking_type', 'id', true, null, null);
        $this->addColumn('label', 'Label', 'VARCHAR', false, 255, null);
        $this->addColumn('value', 'Value', 'INTEGER', true, null, 0);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Transaction', 'Transaction', RelationMap::MANY_TO_ONE, array('transaction_id' => 'id', ), 'CASCADE', 'CASCADE');
        $this->addRelation('BookingType', 'BookingType', RelationMap::MANY_TO_ONE, array('booking_type_id' => 'id', ), 'CASCADE', 'CASCADE');
    } // buildRelations()

} // BookingTableMap
