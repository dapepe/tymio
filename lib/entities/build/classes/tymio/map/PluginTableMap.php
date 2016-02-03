<?php



/**
 * This class defines the structure of the 'plugin' table.
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
class PluginTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'tymio.map.PluginTableMap';

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
        $this->setName('plugin');
        $this->setPhpName('Plugin');
        $this->setClassname('Plugin');
        $this->setPackage('tymio');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('account_id', 'AccountId', 'INTEGER', 'account', 'id', true, null, null);
        $this->addColumn('entity', 'Entity', 'VARCHAR', true, 255, null);
        $this->addColumn('event', 'Event', 'VARCHAR', true, 255, null);
        $this->addColumn('priority', 'Priority', 'INTEGER', true, 10, null);
        $this->addColumn('identifier', 'Identifier', 'VARCHAR', true, 255, null);
        $this->addColumn('name', 'Name', 'VARCHAR', true, 255, null);
        $this->addColumn('code', 'Code', 'CLOB', true, null, null);
        $this->addColumn('active', 'Active', 'TINYINT', true, 11, 1);
        $this->addColumn('interval', 'Interval', 'INTEGER', true, null, 0);
        $this->addColumn('start', 'Start', 'INTEGER', true, null, 0);
        $this->addColumn('last_execution_time', 'LastExecutionTime', 'BIGINT', true, null, 0);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Account', 'Account', RelationMap::MANY_TO_ONE, array('account_id' => 'id', ), 'CASCADE', 'CASCADE');
    } // buildRelations()

} // PluginTableMap
