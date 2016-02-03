<?php



/**
 * This class defines the structure of the 'booking_type' table.
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
class BookingTypeTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'tymio.map.BookingTypeTableMap';

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
        $this->setName('booking_type');
        $this->setPhpName('BookingType');
        $this->setClassname('BookingType');
        $this->setPackage('tymio');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('account_id', 'AccountId', 'INTEGER', 'account', 'id', true, null, null);
        $this->addColumn('identifier', 'Identifier', 'VARCHAR', true, 255, null);
        $this->addColumn('label', 'Label', 'VARCHAR', true, 255, null);
        $this->addColumn('unit', 'Unit', 'CHAR', true, null, null);
        $this->getColumn('unit', false)->setValueSet(array (
  0 => 'seconds',
  1 => 'minutes',
  2 => 'hours',
  3 => 'halfdays',
  4 => 'days',
  5 => 'weeks',
  6 => 'months',
  7 => 'years',
));
        $this->addColumn('display_unit', 'DisplayUnit', 'CHAR', false, null, null);
        $this->getColumn('display_unit', false)->setValueSet(array (
  0 => 'seconds',
  1 => 'minutes',
  2 => 'hours',
  3 => 'halfdays',
  4 => 'days',
  5 => 'weeks',
  6 => 'months',
  7 => 'years',
));
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Account', 'Account', RelationMap::MANY_TO_ONE, array('account_id' => 'id', ), 'CASCADE', 'CASCADE');
        $this->addRelation('Booking', 'Booking', RelationMap::ONE_TO_MANY, array('id' => 'booking_type_id', ), 'CASCADE', 'CASCADE', 'Bookings');
    } // buildRelations()

} // BookingTypeTableMap
