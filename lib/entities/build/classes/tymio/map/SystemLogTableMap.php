<?php



/**
 * This class defines the structure of the 'system_log' table.
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
class SystemLogTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'tymio.map.SystemLogTableMap';

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
        $this->setName('system_log');
        $this->setPhpName('SystemLog');
        $this->setClassname('SystemLog');
        $this->setPackage('tymio');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('user_id', 'UserId', 'INTEGER', 'user', 'id', false, null, null);
        $this->addColumn('index', 'Index', 'VARCHAR', false, 255, null);
        $this->addColumn('entity', 'Entity', 'VARCHAR', true, 255, null);
        $this->addColumn('service', 'Service', 'VARCHAR', true, 255, null);
        $this->addColumn('code', 'Code', 'INTEGER', false, null, null);
        $this->addColumn('message', 'Message', 'LONGVARCHAR', false, null, null);
        $this->addColumn('data', 'Data', 'CLOB', false, null, null);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('User', 'User', RelationMap::MANY_TO_ONE, array('user_id' => 'id', ), 'SET NULL', 'CASCADE');
    } // buildRelations()

} // SystemLogTableMap
