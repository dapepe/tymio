<?php



/**
 * This class defines the structure of the 'user' table.
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
class UserTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'tymio.map.UserTableMap';

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
        $this->setName('user');
        $this->setPhpName('User');
        $this->setClassname('User');
        $this->setPackage('tymio');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('account_id', 'AccountId', 'INTEGER', 'account', 'id', true, null, null);
        $this->addForeignKey('domain_id', 'DomainId', 'INTEGER', 'domain', 'id', true, null, null);
        $this->addColumn('deleted', 'Deleted', 'TINYINT', true, null, 0);
        $this->addColumn('name', 'Name', 'VARCHAR', true, 45, null);
        $this->addColumn('firstname', 'Firstname', 'VARCHAR', false, 255, null);
        $this->addColumn('lastname', 'Lastname', 'VARCHAR', false, 255, null);
        $this->addColumn('phone', 'Phone', 'VARCHAR', false, 255, null);
        $this->addColumn('manager_of', 'ManagerOf', 'INTEGER', false, null, null);
        $this->addColumn('is_admin', 'IsAdmin', 'TINYINT', true, null, 0);
        $this->addColumn('email', 'Email', 'VARCHAR', false, 255, null);
        $this->addColumn('password_hash', 'PasswordHash', 'VARCHAR', true, 255, null);
        $this->addColumn('number', 'Number', 'VARCHAR', false, 45, null);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Account', 'Account', RelationMap::MANY_TO_ONE, array('account_id' => 'id', ), 'CASCADE', 'CASCADE');
        $this->addRelation('Domain', 'Domain', RelationMap::MANY_TO_ONE, array('domain_id' => 'id', ), 'CASCADE', 'CASCADE');
        $this->addRelation('ClockingRelatedByCreatorId', 'Clocking', RelationMap::ONE_TO_MANY, array('id' => 'creator_id', ), 'SET NULL', 'CASCADE', 'ClockingsRelatedByCreatorId');
        $this->addRelation('ClockingRelatedByUserId', 'Clocking', RelationMap::ONE_TO_MANY, array('id' => 'user_id', ), 'CASCADE', 'CASCADE', 'ClockingsRelatedByUserId');
        $this->addRelation('PropertyValue', 'PropertyValue', RelationMap::ONE_TO_MANY, array('id' => 'user_id', ), 'CASCADE', 'CASCADE', 'PropertyValues');
        $this->addRelation('SystemLog', 'SystemLog', RelationMap::ONE_TO_MANY, array('id' => 'user_id', ), 'SET NULL', 'CASCADE', 'SystemLogs');
        $this->addRelation('TransactionRelatedByCreatorId', 'Transaction', RelationMap::ONE_TO_MANY, array('id' => 'creator_id', ), 'SET NULL', 'CASCADE', 'TransactionsRelatedByCreatorId');
        $this->addRelation('TransactionRelatedByUserId', 'Transaction', RelationMap::ONE_TO_MANY, array('id' => 'user_id', ), 'CASCADE', 'CASCADE', 'TransactionsRelatedByUserId');
    } // buildRelations()

} // UserTableMap
