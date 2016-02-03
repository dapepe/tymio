<?php


/**
 * Base class that represents a row from the 'account' table.
 *
 *
 *
 * @package    propel.generator.tymio.om
 */
abstract class BaseAccount extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'AccountPeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        AccountPeer
     */
    protected static $peer;

    /**
     * The flag var to prevent infinit loop in deep copy
     * @var       boolean
     */
    protected $startCopy = false;

    /**
     * The value for the id field.
     * @var        int
     */
    protected $id;

    /**
     * The value for the address_id field.
     * @var        int
     */
    protected $address_id;

    /**
     * The value for the identifier field.
     * @var        string
     */
    protected $identifier;

    /**
     * The value for the name field.
     * @var        string
     */
    protected $name;

    /**
     * @var        Address
     */
    protected $aAddress;

    /**
     * @var        PropelObjectCollection|BookingType[] Collection to store aggregation of BookingType objects.
     */
    protected $collBookingTypes;
    protected $collBookingTypesPartial;

    /**
     * @var        PropelObjectCollection|ClockingType[] Collection to store aggregation of ClockingType objects.
     */
    protected $collClockingTypes;
    protected $collClockingTypesPartial;

    /**
     * @var        PropelObjectCollection|Domain[] Collection to store aggregation of Domain objects.
     */
    protected $collDomains;
    protected $collDomainsPartial;

    /**
     * @var        PropelObjectCollection|Holiday[] Collection to store aggregation of Holiday objects.
     */
    protected $collHolidays;
    protected $collHolidaysPartial;

    /**
     * @var        PropelObjectCollection|Plugin[] Collection to store aggregation of Plugin objects.
     */
    protected $collPlugins;
    protected $collPluginsPartial;

    /**
     * @var        PropelObjectCollection|Property[] Collection to store aggregation of Property objects.
     */
    protected $collPropertys;
    protected $collPropertysPartial;

    /**
     * @var        PropelObjectCollection|User[] Collection to store aggregation of User objects.
     */
    protected $collUsers;
    protected $collUsersPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInSave = false;

    /**
     * Flag to prevent endless validation loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInValidation = false;

    /**
     * Flag to prevent endless clearAllReferences($deep=true) loop, if this object is referenced
     * @var        boolean
     */
    protected $alreadyInClearAllReferencesDeep = false;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $bookingTypesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $clockingTypesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $domainsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $holidaysScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $pluginsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $propertysScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $usersScheduledForDeletion = null;

    /**
     * Get the [id] column value.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the [address_id] column value.
     *
     * @return int
     */
    public function getAddressId()
    {
        return $this->address_id;
    }

    /**
     * Get the [identifier] column value.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Get the [name] column value.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of [id] column.
     *
     * @param int $v new value
     * @return Account The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[] = AccountPeer::ID;
        }


        return $this;
    } // setId()

    /**
     * Set the value of [address_id] column.
     *
     * @param int $v new value
     * @return Account The current object (for fluent API support)
     */
    public function setAddressId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->address_id !== $v) {
            $this->address_id = $v;
            $this->modifiedColumns[] = AccountPeer::ADDRESS_ID;
        }

        if ($this->aAddress !== null && $this->aAddress->getId() !== $v) {
            $this->aAddress = null;
        }


        return $this;
    } // setAddressId()

    /**
     * Set the value of [identifier] column.
     *
     * @param string $v new value
     * @return Account The current object (for fluent API support)
     */
    public function setIdentifier($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->identifier !== $v) {
            $this->identifier = $v;
            $this->modifiedColumns[] = AccountPeer::IDENTIFIER;
        }


        return $this;
    } // setIdentifier()

    /**
     * Set the value of [name] column.
     *
     * @param string $v new value
     * @return Account The current object (for fluent API support)
     */
    public function setName($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->name !== $v) {
            $this->name = $v;
            $this->modifiedColumns[] = AccountPeer::NAME;
        }


        return $this;
    } // setName()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
        // otherwise, everything was equal, so return true
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array $row The row returned by PDOStatement->fetch(PDO::FETCH_NUM)
     * @param int $startcol 0-based offset column which indicates which restultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false)
    {
        try {

            $this->id = ($row[$startcol + 0] !== null) ? (int) $row[$startcol + 0] : null;
            $this->address_id = ($row[$startcol + 1] !== null) ? (int) $row[$startcol + 1] : null;
            $this->identifier = ($row[$startcol + 2] !== null) ? (string) $row[$startcol + 2] : null;
            $this->name = ($row[$startcol + 3] !== null) ? (string) $row[$startcol + 3] : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
            $this->postHydrate($row, $startcol, $rehydrate);
            return $startcol + 4; // 4 = AccountPeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating Account object", $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {

        if ($this->aAddress !== null && $this->address_id !== $this->aAddress->getId()) {
            $this->aAddress = null;
        }
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param boolean $deep (optional) Whether to also de-associated any related objects.
     * @param PropelPDO $con (optional) The PropelPDO connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, PropelPDO $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getConnection(AccountPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = AccountPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aAddress = null;
            $this->collBookingTypes = null;

            $this->collClockingTypes = null;

            $this->collDomains = null;

            $this->collHolidays = null;

            $this->collPlugins = null;

            $this->collPropertys = null;

            $this->collUsers = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param PropelPDO $con
     * @return void
     * @throws PropelException
     * @throws Exception
     * @see        BaseObject::setDeleted()
     * @see        BaseObject::isDeleted()
     */
    public function delete(PropelPDO $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getConnection(AccountPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = AccountQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $con->commit();
                $this->setDeleted(true);
            } else {
                $con->commit();
            }
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param PropelPDO $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @throws Exception
     * @see        doSave()
     */
    public function save(PropelPDO $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($con === null) {
            $con = Propel::getConnection(AccountPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        $isInsert = $this->isNew();
        try {
            $ret = $this->preSave($con);
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                AccountPeer::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param PropelPDO $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see        save()
     */
    protected function doSave(PropelPDO $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            // We call the save method on the following object(s) if they
            // were passed to this object by their coresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aAddress !== null) {
                if ($this->aAddress->isModified() || $this->aAddress->isNew()) {
                    $affectedRows += $this->aAddress->save($con);
                }
                $this->setAddress($this->aAddress);
            }

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                } else {
                    $this->doUpdate($con);
                }
                $affectedRows += 1;
                $this->resetModified();
            }

            if ($this->bookingTypesScheduledForDeletion !== null) {
                if (!$this->bookingTypesScheduledForDeletion->isEmpty()) {
                    BookingTypeQuery::create()
                        ->filterByPrimaryKeys($this->bookingTypesScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->bookingTypesScheduledForDeletion = null;
                }
            }

            if ($this->collBookingTypes !== null) {
                foreach ($this->collBookingTypes as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->clockingTypesScheduledForDeletion !== null) {
                if (!$this->clockingTypesScheduledForDeletion->isEmpty()) {
                    ClockingTypeQuery::create()
                        ->filterByPrimaryKeys($this->clockingTypesScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->clockingTypesScheduledForDeletion = null;
                }
            }

            if ($this->collClockingTypes !== null) {
                foreach ($this->collClockingTypes as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->domainsScheduledForDeletion !== null) {
                if (!$this->domainsScheduledForDeletion->isEmpty()) {
                    DomainQuery::create()
                        ->filterByPrimaryKeys($this->domainsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->domainsScheduledForDeletion = null;
                }
            }

            if ($this->collDomains !== null) {
                foreach ($this->collDomains as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->holidaysScheduledForDeletion !== null) {
                if (!$this->holidaysScheduledForDeletion->isEmpty()) {
                    HolidayQuery::create()
                        ->filterByPrimaryKeys($this->holidaysScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->holidaysScheduledForDeletion = null;
                }
            }

            if ($this->collHolidays !== null) {
                foreach ($this->collHolidays as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->pluginsScheduledForDeletion !== null) {
                if (!$this->pluginsScheduledForDeletion->isEmpty()) {
                    PluginQuery::create()
                        ->filterByPrimaryKeys($this->pluginsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->pluginsScheduledForDeletion = null;
                }
            }

            if ($this->collPlugins !== null) {
                foreach ($this->collPlugins as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->propertysScheduledForDeletion !== null) {
                if (!$this->propertysScheduledForDeletion->isEmpty()) {
                    PropertyQuery::create()
                        ->filterByPrimaryKeys($this->propertysScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->propertysScheduledForDeletion = null;
                }
            }

            if ($this->collPropertys !== null) {
                foreach ($this->collPropertys as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->usersScheduledForDeletion !== null) {
                if (!$this->usersScheduledForDeletion->isEmpty()) {
                    UserQuery::create()
                        ->filterByPrimaryKeys($this->usersScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->usersScheduledForDeletion = null;
                }
            }

            if ($this->collUsers !== null) {
                foreach ($this->collUsers as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param PropelPDO $con
     *
     * @throws PropelException
     * @see        doSave()
     */
    protected function doInsert(PropelPDO $con)
    {
        $modifiedColumns = array();
        $index = 0;

        $this->modifiedColumns[] = AccountPeer::ID;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . AccountPeer::ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(AccountPeer::ID)) {
            $modifiedColumns[':p' . $index++]  = '`id`';
        }
        if ($this->isColumnModified(AccountPeer::ADDRESS_ID)) {
            $modifiedColumns[':p' . $index++]  = '`address_id`';
        }
        if ($this->isColumnModified(AccountPeer::IDENTIFIER)) {
            $modifiedColumns[':p' . $index++]  = '`identifier`';
        }
        if ($this->isColumnModified(AccountPeer::NAME)) {
            $modifiedColumns[':p' . $index++]  = '`name`';
        }

        $sql = sprintf(
            'INSERT INTO `account` (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case '`id`':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);
                        break;
                    case '`address_id`':
                        $stmt->bindValue($identifier, $this->address_id, PDO::PARAM_INT);
                        break;
                    case '`identifier`':
                        $stmt->bindValue($identifier, $this->identifier, PDO::PARAM_STR);
                        break;
                    case '`name`':
                        $stmt->bindValue($identifier, $this->name, PDO::PARAM_STR);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', $e);
        }
        $this->setId($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param PropelPDO $con
     *
     * @see        doSave()
     */
    protected function doUpdate(PropelPDO $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();
        BasePeer::doUpdate($selectCriteria, $valuesCriteria, $con);
    }

    /**
     * Array of ValidationFailed objects.
     * @var        array ValidationFailed[]
     */
    protected $validationFailures = array();

    /**
     * Gets any ValidationFailed objects that resulted from last call to validate().
     *
     *
     * @return array ValidationFailed[]
     * @see        validate()
     */
    public function getValidationFailures()
    {
        return $this->validationFailures;
    }

    /**
     * Validates the objects modified field values and all objects related to this table.
     *
     * If $columns is either a column name or an array of column names
     * only those columns are validated.
     *
     * @param mixed $columns Column name or an array of column names.
     * @return boolean Whether all columns pass validation.
     * @see        doValidate()
     * @see        getValidationFailures()
     */
    public function validate($columns = null)
    {
        $res = $this->doValidate($columns);
        if ($res === true) {
            $this->validationFailures = array();

            return true;
        }

        $this->validationFailures = $res;

        return false;
    }

    /**
     * This function performs the validation work for complex object models.
     *
     * In addition to checking the current object, all related objects will
     * also be validated.  If all pass then <code>true</code> is returned; otherwise
     * an aggreagated array of ValidationFailed objects will be returned.
     *
     * @param array $columns Array of column names to validate.
     * @return mixed <code>true</code> if all validations pass; array of <code>ValidationFailed</code> objets otherwise.
     */
    protected function doValidate($columns = null)
    {
        if (!$this->alreadyInValidation) {
            $this->alreadyInValidation = true;
            $retval = null;

            $failureMap = array();


            // We call the validate method on the following object(s) if they
            // were passed to this object by their coresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aAddress !== null) {
                if (!$this->aAddress->validate($columns)) {
                    $failureMap = array_merge($failureMap, $this->aAddress->getValidationFailures());
                }
            }


            if (($retval = AccountPeer::doValidate($this, $columns)) !== true) {
                $failureMap = array_merge($failureMap, $retval);
            }


                if ($this->collBookingTypes !== null) {
                    foreach ($this->collBookingTypes as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collClockingTypes !== null) {
                    foreach ($this->collClockingTypes as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collDomains !== null) {
                    foreach ($this->collDomains as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collHolidays !== null) {
                    foreach ($this->collHolidays as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collPlugins !== null) {
                    foreach ($this->collPlugins as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collPropertys !== null) {
                    foreach ($this->collPropertys as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collUsers !== null) {
                    foreach ($this->collUsers as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }


            $this->alreadyInValidation = false;
        }

        return (!empty($failureMap) ? $failureMap : true);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param string $name name
     * @param string $type The type of fieldname the $name is of:
     *               one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *               BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     *               Defaults to BasePeer::TYPE_PHPNAME
     * @return mixed Value of field.
     */
    public function getByName($name, $type = BasePeer::TYPE_PHPNAME)
    {
        $pos = AccountPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();
                break;
            case 1:
                return $this->getAddressId();
                break;
            case 2:
                return $this->getIdentifier();
                break;
            case 3:
                return $this->getName();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME,
     *                    BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     *                    Defaults to BasePeer::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to true.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = BasePeer::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {
        if (isset($alreadyDumpedObjects['Account'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['Account'][$this->getPrimaryKey()] = true;
        $keys = AccountPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getAddressId(),
            $keys[2] => $this->getIdentifier(),
            $keys[3] => $this->getName(),
        );
        if ($includeForeignObjects) {
            if (null !== $this->aAddress) {
                $result['Address'] = $this->aAddress->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collBookingTypes) {
                $result['BookingTypes'] = $this->collBookingTypes->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collClockingTypes) {
                $result['ClockingTypes'] = $this->collClockingTypes->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collDomains) {
                $result['Domains'] = $this->collDomains->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collHolidays) {
                $result['Holidays'] = $this->collHolidays->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collPlugins) {
                $result['Plugins'] = $this->collPlugins->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collPropertys) {
                $result['Propertys'] = $this->collPropertys->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collUsers) {
                $result['Users'] = $this->collUsers->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param string $name peer name
     * @param mixed $value field value
     * @param string $type The type of fieldname the $name is of:
     *                     one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *                     BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     *                     Defaults to BasePeer::TYPE_PHPNAME
     * @return void
     */
    public function setByName($name, $value, $type = BasePeer::TYPE_PHPNAME)
    {
        $pos = AccountPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

        $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param int $pos position in xml schema
     * @param mixed $value field value
     * @return void
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setAddressId($value);
                break;
            case 2:
                $this->setIdentifier($value);
                break;
            case 3:
                $this->setName($value);
                break;
        } // switch()
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME,
     * BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     * The default key type is the column's BasePeer::TYPE_PHPNAME
     *
     * @param array  $arr     An array to populate the object from.
     * @param string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
    {
        $keys = AccountPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setAddressId($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setIdentifier($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setName($arr[$keys[3]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(AccountPeer::DATABASE_NAME);

        if ($this->isColumnModified(AccountPeer::ID)) $criteria->add(AccountPeer::ID, $this->id);
        if ($this->isColumnModified(AccountPeer::ADDRESS_ID)) $criteria->add(AccountPeer::ADDRESS_ID, $this->address_id);
        if ($this->isColumnModified(AccountPeer::IDENTIFIER)) $criteria->add(AccountPeer::IDENTIFIER, $this->identifier);
        if ($this->isColumnModified(AccountPeer::NAME)) $criteria->add(AccountPeer::NAME, $this->name);

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = new Criteria(AccountPeer::DATABASE_NAME);
        $criteria->add(AccountPeer::ID, $this->id);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param  int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of Account (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setAddressId($this->getAddressId());
        $copyObj->setIdentifier($this->getIdentifier());
        $copyObj->setName($this->getName());

        if ($deepCopy && !$this->startCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);
            // store object hash to prevent cycle
            $this->startCopy = true;

            foreach ($this->getBookingTypes() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addBookingType($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getClockingTypes() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addClockingType($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getDomains() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addDomain($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getHolidays() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addHoliday($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getPlugins() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addPlugin($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getPropertys() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addProperty($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getUsers() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addUser($relObj->copy($deepCopy));
                }
            }

            //unflag object copy
            $this->startCopy = false;
        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setId(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return Account Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }

    /**
     * Returns a peer instance associated with this om.
     *
     * Since Peer classes are not to have any instance attributes, this method returns the
     * same instance for all member of this class. The method could therefore
     * be static, but this would prevent one from overriding the behavior.
     *
     * @return AccountPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new AccountPeer();
        }

        return self::$peer;
    }

    /**
     * Declares an association between this object and a Address object.
     *
     * @param             Address $v
     * @return Account The current object (for fluent API support)
     * @throws PropelException
     */
    public function setAddress(Address $v = null)
    {
        if ($v === null) {
            $this->setAddressId(NULL);
        } else {
            $this->setAddressId($v->getId());
        }

        $this->aAddress = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the Address object, it will not be re-added.
        if ($v !== null) {
            $v->addAccount($this);
        }


        return $this;
    }


    /**
     * Get the associated Address object
     *
     * @param PropelPDO $con Optional Connection object.
     * @param $doQuery Executes a query to get the object if required
     * @return Address The associated Address object.
     * @throws PropelException
     */
    public function getAddress(PropelPDO $con = null, $doQuery = true)
    {
        if ($this->aAddress === null && ($this->address_id !== null) && $doQuery) {
            $this->aAddress = AddressQuery::create()->findPk($this->address_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aAddress->addAccounts($this);
             */
        }

        return $this->aAddress;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName)
    {
        if ('BookingType' == $relationName) {
            $this->initBookingTypes();
        }
        if ('ClockingType' == $relationName) {
            $this->initClockingTypes();
        }
        if ('Domain' == $relationName) {
            $this->initDomains();
        }
        if ('Holiday' == $relationName) {
            $this->initHolidays();
        }
        if ('Plugin' == $relationName) {
            $this->initPlugins();
        }
        if ('Property' == $relationName) {
            $this->initPropertys();
        }
        if ('User' == $relationName) {
            $this->initUsers();
        }
    }

    /**
     * Clears out the collBookingTypes collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return Account The current object (for fluent API support)
     * @see        addBookingTypes()
     */
    public function clearBookingTypes()
    {
        $this->collBookingTypes = null; // important to set this to null since that means it is uninitialized
        $this->collBookingTypesPartial = null;

        return $this;
    }

    /**
     * reset is the collBookingTypes collection loaded partially
     *
     * @return void
     */
    public function resetPartialBookingTypes($v = true)
    {
        $this->collBookingTypesPartial = $v;
    }

    /**
     * Initializes the collBookingTypes collection.
     *
     * By default this just sets the collBookingTypes collection to an empty array (like clearcollBookingTypes());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initBookingTypes($overrideExisting = true)
    {
        if (null !== $this->collBookingTypes && !$overrideExisting) {
            return;
        }
        $this->collBookingTypes = new PropelObjectCollection();
        $this->collBookingTypes->setModel('BookingType');
    }

    /**
     * Gets an array of BookingType objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this Account is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|BookingType[] List of BookingType objects
     * @throws PropelException
     */
    public function getBookingTypes($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collBookingTypesPartial && !$this->isNew();
        if (null === $this->collBookingTypes || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collBookingTypes) {
                // return empty collection
                $this->initBookingTypes();
            } else {
                $collBookingTypes = BookingTypeQuery::create(null, $criteria)
                    ->filterByAccount($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collBookingTypesPartial && count($collBookingTypes)) {
                      $this->initBookingTypes(false);

                      foreach($collBookingTypes as $obj) {
                        if (false == $this->collBookingTypes->contains($obj)) {
                          $this->collBookingTypes->append($obj);
                        }
                      }

                      $this->collBookingTypesPartial = true;
                    }

                    $collBookingTypes->getInternalIterator()->rewind();
                    return $collBookingTypes;
                }

                if($partial && $this->collBookingTypes) {
                    foreach($this->collBookingTypes as $obj) {
                        if($obj->isNew()) {
                            $collBookingTypes[] = $obj;
                        }
                    }
                }

                $this->collBookingTypes = $collBookingTypes;
                $this->collBookingTypesPartial = false;
            }
        }

        return $this->collBookingTypes;
    }

    /**
     * Sets a collection of BookingType objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $bookingTypes A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return Account The current object (for fluent API support)
     */
    public function setBookingTypes(PropelCollection $bookingTypes, PropelPDO $con = null)
    {
        $bookingTypesToDelete = $this->getBookingTypes(new Criteria(), $con)->diff($bookingTypes);

        $this->bookingTypesScheduledForDeletion = unserialize(serialize($bookingTypesToDelete));

        foreach ($bookingTypesToDelete as $bookingTypeRemoved) {
            $bookingTypeRemoved->setAccount(null);
        }

        $this->collBookingTypes = null;
        foreach ($bookingTypes as $bookingType) {
            $this->addBookingType($bookingType);
        }

        $this->collBookingTypes = $bookingTypes;
        $this->collBookingTypesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related BookingType objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related BookingType objects.
     * @throws PropelException
     */
    public function countBookingTypes(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collBookingTypesPartial && !$this->isNew();
        if (null === $this->collBookingTypes || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collBookingTypes) {
                return 0;
            }

            if($partial && !$criteria) {
                return count($this->getBookingTypes());
            }
            $query = BookingTypeQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAccount($this)
                ->count($con);
        }

        return count($this->collBookingTypes);
    }

    /**
     * Method called to associate a BookingType object to this object
     * through the BookingType foreign key attribute.
     *
     * @param    BookingType $l BookingType
     * @return Account The current object (for fluent API support)
     */
    public function addBookingType(BookingType $l)
    {
        if ($this->collBookingTypes === null) {
            $this->initBookingTypes();
            $this->collBookingTypesPartial = true;
        }
        if (!in_array($l, $this->collBookingTypes->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddBookingType($l);
        }

        return $this;
    }

    /**
     * @param	BookingType $bookingType The bookingType object to add.
     */
    protected function doAddBookingType($bookingType)
    {
        $this->collBookingTypes[]= $bookingType;
        $bookingType->setAccount($this);
    }

    /**
     * @param	BookingType $bookingType The bookingType object to remove.
     * @return Account The current object (for fluent API support)
     */
    public function removeBookingType($bookingType)
    {
        if ($this->getBookingTypes()->contains($bookingType)) {
            $this->collBookingTypes->remove($this->collBookingTypes->search($bookingType));
            if (null === $this->bookingTypesScheduledForDeletion) {
                $this->bookingTypesScheduledForDeletion = clone $this->collBookingTypes;
                $this->bookingTypesScheduledForDeletion->clear();
            }
            $this->bookingTypesScheduledForDeletion[]= clone $bookingType;
            $bookingType->setAccount(null);
        }

        return $this;
    }

    /**
     * Clears out the collClockingTypes collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return Account The current object (for fluent API support)
     * @see        addClockingTypes()
     */
    public function clearClockingTypes()
    {
        $this->collClockingTypes = null; // important to set this to null since that means it is uninitialized
        $this->collClockingTypesPartial = null;

        return $this;
    }

    /**
     * reset is the collClockingTypes collection loaded partially
     *
     * @return void
     */
    public function resetPartialClockingTypes($v = true)
    {
        $this->collClockingTypesPartial = $v;
    }

    /**
     * Initializes the collClockingTypes collection.
     *
     * By default this just sets the collClockingTypes collection to an empty array (like clearcollClockingTypes());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initClockingTypes($overrideExisting = true)
    {
        if (null !== $this->collClockingTypes && !$overrideExisting) {
            return;
        }
        $this->collClockingTypes = new PropelObjectCollection();
        $this->collClockingTypes->setModel('ClockingType');
    }

    /**
     * Gets an array of ClockingType objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this Account is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|ClockingType[] List of ClockingType objects
     * @throws PropelException
     */
    public function getClockingTypes($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collClockingTypesPartial && !$this->isNew();
        if (null === $this->collClockingTypes || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collClockingTypes) {
                // return empty collection
                $this->initClockingTypes();
            } else {
                $collClockingTypes = ClockingTypeQuery::create(null, $criteria)
                    ->filterByAccount($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collClockingTypesPartial && count($collClockingTypes)) {
                      $this->initClockingTypes(false);

                      foreach($collClockingTypes as $obj) {
                        if (false == $this->collClockingTypes->contains($obj)) {
                          $this->collClockingTypes->append($obj);
                        }
                      }

                      $this->collClockingTypesPartial = true;
                    }

                    $collClockingTypes->getInternalIterator()->rewind();
                    return $collClockingTypes;
                }

                if($partial && $this->collClockingTypes) {
                    foreach($this->collClockingTypes as $obj) {
                        if($obj->isNew()) {
                            $collClockingTypes[] = $obj;
                        }
                    }
                }

                $this->collClockingTypes = $collClockingTypes;
                $this->collClockingTypesPartial = false;
            }
        }

        return $this->collClockingTypes;
    }

    /**
     * Sets a collection of ClockingType objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $clockingTypes A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return Account The current object (for fluent API support)
     */
    public function setClockingTypes(PropelCollection $clockingTypes, PropelPDO $con = null)
    {
        $clockingTypesToDelete = $this->getClockingTypes(new Criteria(), $con)->diff($clockingTypes);

        $this->clockingTypesScheduledForDeletion = unserialize(serialize($clockingTypesToDelete));

        foreach ($clockingTypesToDelete as $clockingTypeRemoved) {
            $clockingTypeRemoved->setAccount(null);
        }

        $this->collClockingTypes = null;
        foreach ($clockingTypes as $clockingType) {
            $this->addClockingType($clockingType);
        }

        $this->collClockingTypes = $clockingTypes;
        $this->collClockingTypesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related ClockingType objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related ClockingType objects.
     * @throws PropelException
     */
    public function countClockingTypes(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collClockingTypesPartial && !$this->isNew();
        if (null === $this->collClockingTypes || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collClockingTypes) {
                return 0;
            }

            if($partial && !$criteria) {
                return count($this->getClockingTypes());
            }
            $query = ClockingTypeQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAccount($this)
                ->count($con);
        }

        return count($this->collClockingTypes);
    }

    /**
     * Method called to associate a ClockingType object to this object
     * through the ClockingType foreign key attribute.
     *
     * @param    ClockingType $l ClockingType
     * @return Account The current object (for fluent API support)
     */
    public function addClockingType(ClockingType $l)
    {
        if ($this->collClockingTypes === null) {
            $this->initClockingTypes();
            $this->collClockingTypesPartial = true;
        }
        if (!in_array($l, $this->collClockingTypes->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddClockingType($l);
        }

        return $this;
    }

    /**
     * @param	ClockingType $clockingType The clockingType object to add.
     */
    protected function doAddClockingType($clockingType)
    {
        $this->collClockingTypes[]= $clockingType;
        $clockingType->setAccount($this);
    }

    /**
     * @param	ClockingType $clockingType The clockingType object to remove.
     * @return Account The current object (for fluent API support)
     */
    public function removeClockingType($clockingType)
    {
        if ($this->getClockingTypes()->contains($clockingType)) {
            $this->collClockingTypes->remove($this->collClockingTypes->search($clockingType));
            if (null === $this->clockingTypesScheduledForDeletion) {
                $this->clockingTypesScheduledForDeletion = clone $this->collClockingTypes;
                $this->clockingTypesScheduledForDeletion->clear();
            }
            $this->clockingTypesScheduledForDeletion[]= clone $clockingType;
            $clockingType->setAccount(null);
        }

        return $this;
    }

    /**
     * Clears out the collDomains collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return Account The current object (for fluent API support)
     * @see        addDomains()
     */
    public function clearDomains()
    {
        $this->collDomains = null; // important to set this to null since that means it is uninitialized
        $this->collDomainsPartial = null;

        return $this;
    }

    /**
     * reset is the collDomains collection loaded partially
     *
     * @return void
     */
    public function resetPartialDomains($v = true)
    {
        $this->collDomainsPartial = $v;
    }

    /**
     * Initializes the collDomains collection.
     *
     * By default this just sets the collDomains collection to an empty array (like clearcollDomains());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initDomains($overrideExisting = true)
    {
        if (null !== $this->collDomains && !$overrideExisting) {
            return;
        }
        $this->collDomains = new PropelObjectCollection();
        $this->collDomains->setModel('Domain');
    }

    /**
     * Gets an array of Domain objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this Account is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|Domain[] List of Domain objects
     * @throws PropelException
     */
    public function getDomains($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collDomainsPartial && !$this->isNew();
        if (null === $this->collDomains || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collDomains) {
                // return empty collection
                $this->initDomains();
            } else {
                $collDomains = DomainQuery::create(null, $criteria)
                    ->filterByAccount($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collDomainsPartial && count($collDomains)) {
                      $this->initDomains(false);

                      foreach($collDomains as $obj) {
                        if (false == $this->collDomains->contains($obj)) {
                          $this->collDomains->append($obj);
                        }
                      }

                      $this->collDomainsPartial = true;
                    }

                    $collDomains->getInternalIterator()->rewind();
                    return $collDomains;
                }

                if($partial && $this->collDomains) {
                    foreach($this->collDomains as $obj) {
                        if($obj->isNew()) {
                            $collDomains[] = $obj;
                        }
                    }
                }

                $this->collDomains = $collDomains;
                $this->collDomainsPartial = false;
            }
        }

        return $this->collDomains;
    }

    /**
     * Sets a collection of Domain objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $domains A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return Account The current object (for fluent API support)
     */
    public function setDomains(PropelCollection $domains, PropelPDO $con = null)
    {
        $domainsToDelete = $this->getDomains(new Criteria(), $con)->diff($domains);

        $this->domainsScheduledForDeletion = unserialize(serialize($domainsToDelete));

        foreach ($domainsToDelete as $domainRemoved) {
            $domainRemoved->setAccount(null);
        }

        $this->collDomains = null;
        foreach ($domains as $domain) {
            $this->addDomain($domain);
        }

        $this->collDomains = $domains;
        $this->collDomainsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Domain objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related Domain objects.
     * @throws PropelException
     */
    public function countDomains(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collDomainsPartial && !$this->isNew();
        if (null === $this->collDomains || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collDomains) {
                return 0;
            }

            if($partial && !$criteria) {
                return count($this->getDomains());
            }
            $query = DomainQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAccount($this)
                ->count($con);
        }

        return count($this->collDomains);
    }

    /**
     * Method called to associate a Domain object to this object
     * through the Domain foreign key attribute.
     *
     * @param    Domain $l Domain
     * @return Account The current object (for fluent API support)
     */
    public function addDomain(Domain $l)
    {
        if ($this->collDomains === null) {
            $this->initDomains();
            $this->collDomainsPartial = true;
        }
        if (!in_array($l, $this->collDomains->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddDomain($l);
        }

        return $this;
    }

    /**
     * @param	Domain $domain The domain object to add.
     */
    protected function doAddDomain($domain)
    {
        $this->collDomains[]= $domain;
        $domain->setAccount($this);
    }

    /**
     * @param	Domain $domain The domain object to remove.
     * @return Account The current object (for fluent API support)
     */
    public function removeDomain($domain)
    {
        if ($this->getDomains()->contains($domain)) {
            $this->collDomains->remove($this->collDomains->search($domain));
            if (null === $this->domainsScheduledForDeletion) {
                $this->domainsScheduledForDeletion = clone $this->collDomains;
                $this->domainsScheduledForDeletion->clear();
            }
            $this->domainsScheduledForDeletion[]= clone $domain;
            $domain->setAccount(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Account is new, it will return
     * an empty collection; or if this Account has previously
     * been saved, it will retrieve related Domains from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Account.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|Domain[] List of Domain objects
     */
    public function getDomainsJoinAddress($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = DomainQuery::create(null, $criteria);
        $query->joinWith('Address', $join_behavior);

        return $this->getDomains($query, $con);
    }

    /**
     * Clears out the collHolidays collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return Account The current object (for fluent API support)
     * @see        addHolidays()
     */
    public function clearHolidays()
    {
        $this->collHolidays = null; // important to set this to null since that means it is uninitialized
        $this->collHolidaysPartial = null;

        return $this;
    }

    /**
     * reset is the collHolidays collection loaded partially
     *
     * @return void
     */
    public function resetPartialHolidays($v = true)
    {
        $this->collHolidaysPartial = $v;
    }

    /**
     * Initializes the collHolidays collection.
     *
     * By default this just sets the collHolidays collection to an empty array (like clearcollHolidays());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initHolidays($overrideExisting = true)
    {
        if (null !== $this->collHolidays && !$overrideExisting) {
            return;
        }
        $this->collHolidays = new PropelObjectCollection();
        $this->collHolidays->setModel('Holiday');
    }

    /**
     * Gets an array of Holiday objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this Account is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|Holiday[] List of Holiday objects
     * @throws PropelException
     */
    public function getHolidays($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collHolidaysPartial && !$this->isNew();
        if (null === $this->collHolidays || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collHolidays) {
                // return empty collection
                $this->initHolidays();
            } else {
                $collHolidays = HolidayQuery::create(null, $criteria)
                    ->filterByAccount($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collHolidaysPartial && count($collHolidays)) {
                      $this->initHolidays(false);

                      foreach($collHolidays as $obj) {
                        if (false == $this->collHolidays->contains($obj)) {
                          $this->collHolidays->append($obj);
                        }
                      }

                      $this->collHolidaysPartial = true;
                    }

                    $collHolidays->getInternalIterator()->rewind();
                    return $collHolidays;
                }

                if($partial && $this->collHolidays) {
                    foreach($this->collHolidays as $obj) {
                        if($obj->isNew()) {
                            $collHolidays[] = $obj;
                        }
                    }
                }

                $this->collHolidays = $collHolidays;
                $this->collHolidaysPartial = false;
            }
        }

        return $this->collHolidays;
    }

    /**
     * Sets a collection of Holiday objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $holidays A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return Account The current object (for fluent API support)
     */
    public function setHolidays(PropelCollection $holidays, PropelPDO $con = null)
    {
        $holidaysToDelete = $this->getHolidays(new Criteria(), $con)->diff($holidays);

        $this->holidaysScheduledForDeletion = unserialize(serialize($holidaysToDelete));

        foreach ($holidaysToDelete as $holidayRemoved) {
            $holidayRemoved->setAccount(null);
        }

        $this->collHolidays = null;
        foreach ($holidays as $holiday) {
            $this->addHoliday($holiday);
        }

        $this->collHolidays = $holidays;
        $this->collHolidaysPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Holiday objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related Holiday objects.
     * @throws PropelException
     */
    public function countHolidays(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collHolidaysPartial && !$this->isNew();
        if (null === $this->collHolidays || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collHolidays) {
                return 0;
            }

            if($partial && !$criteria) {
                return count($this->getHolidays());
            }
            $query = HolidayQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAccount($this)
                ->count($con);
        }

        return count($this->collHolidays);
    }

    /**
     * Method called to associate a Holiday object to this object
     * through the Holiday foreign key attribute.
     *
     * @param    Holiday $l Holiday
     * @return Account The current object (for fluent API support)
     */
    public function addHoliday(Holiday $l)
    {
        if ($this->collHolidays === null) {
            $this->initHolidays();
            $this->collHolidaysPartial = true;
        }
        if (!in_array($l, $this->collHolidays->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddHoliday($l);
        }

        return $this;
    }

    /**
     * @param	Holiday $holiday The holiday object to add.
     */
    protected function doAddHoliday($holiday)
    {
        $this->collHolidays[]= $holiday;
        $holiday->setAccount($this);
    }

    /**
     * @param	Holiday $holiday The holiday object to remove.
     * @return Account The current object (for fluent API support)
     */
    public function removeHoliday($holiday)
    {
        if ($this->getHolidays()->contains($holiday)) {
            $this->collHolidays->remove($this->collHolidays->search($holiday));
            if (null === $this->holidaysScheduledForDeletion) {
                $this->holidaysScheduledForDeletion = clone $this->collHolidays;
                $this->holidaysScheduledForDeletion->clear();
            }
            $this->holidaysScheduledForDeletion[]= clone $holiday;
            $holiday->setAccount(null);
        }

        return $this;
    }

    /**
     * Clears out the collPlugins collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return Account The current object (for fluent API support)
     * @see        addPlugins()
     */
    public function clearPlugins()
    {
        $this->collPlugins = null; // important to set this to null since that means it is uninitialized
        $this->collPluginsPartial = null;

        return $this;
    }

    /**
     * reset is the collPlugins collection loaded partially
     *
     * @return void
     */
    public function resetPartialPlugins($v = true)
    {
        $this->collPluginsPartial = $v;
    }

    /**
     * Initializes the collPlugins collection.
     *
     * By default this just sets the collPlugins collection to an empty array (like clearcollPlugins());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initPlugins($overrideExisting = true)
    {
        if (null !== $this->collPlugins && !$overrideExisting) {
            return;
        }
        $this->collPlugins = new PropelObjectCollection();
        $this->collPlugins->setModel('Plugin');
    }

    /**
     * Gets an array of Plugin objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this Account is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|Plugin[] List of Plugin objects
     * @throws PropelException
     */
    public function getPlugins($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collPluginsPartial && !$this->isNew();
        if (null === $this->collPlugins || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collPlugins) {
                // return empty collection
                $this->initPlugins();
            } else {
                $collPlugins = PluginQuery::create(null, $criteria)
                    ->filterByAccount($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collPluginsPartial && count($collPlugins)) {
                      $this->initPlugins(false);

                      foreach($collPlugins as $obj) {
                        if (false == $this->collPlugins->contains($obj)) {
                          $this->collPlugins->append($obj);
                        }
                      }

                      $this->collPluginsPartial = true;
                    }

                    $collPlugins->getInternalIterator()->rewind();
                    return $collPlugins;
                }

                if($partial && $this->collPlugins) {
                    foreach($this->collPlugins as $obj) {
                        if($obj->isNew()) {
                            $collPlugins[] = $obj;
                        }
                    }
                }

                $this->collPlugins = $collPlugins;
                $this->collPluginsPartial = false;
            }
        }

        return $this->collPlugins;
    }

    /**
     * Sets a collection of Plugin objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $plugins A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return Account The current object (for fluent API support)
     */
    public function setPlugins(PropelCollection $plugins, PropelPDO $con = null)
    {
        $pluginsToDelete = $this->getPlugins(new Criteria(), $con)->diff($plugins);

        $this->pluginsScheduledForDeletion = unserialize(serialize($pluginsToDelete));

        foreach ($pluginsToDelete as $pluginRemoved) {
            $pluginRemoved->setAccount(null);
        }

        $this->collPlugins = null;
        foreach ($plugins as $plugin) {
            $this->addPlugin($plugin);
        }

        $this->collPlugins = $plugins;
        $this->collPluginsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Plugin objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related Plugin objects.
     * @throws PropelException
     */
    public function countPlugins(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collPluginsPartial && !$this->isNew();
        if (null === $this->collPlugins || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collPlugins) {
                return 0;
            }

            if($partial && !$criteria) {
                return count($this->getPlugins());
            }
            $query = PluginQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAccount($this)
                ->count($con);
        }

        return count($this->collPlugins);
    }

    /**
     * Method called to associate a Plugin object to this object
     * through the Plugin foreign key attribute.
     *
     * @param    Plugin $l Plugin
     * @return Account The current object (for fluent API support)
     */
    public function addPlugin(Plugin $l)
    {
        if ($this->collPlugins === null) {
            $this->initPlugins();
            $this->collPluginsPartial = true;
        }
        if (!in_array($l, $this->collPlugins->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddPlugin($l);
        }

        return $this;
    }

    /**
     * @param	Plugin $plugin The plugin object to add.
     */
    protected function doAddPlugin($plugin)
    {
        $this->collPlugins[]= $plugin;
        $plugin->setAccount($this);
    }

    /**
     * @param	Plugin $plugin The plugin object to remove.
     * @return Account The current object (for fluent API support)
     */
    public function removePlugin($plugin)
    {
        if ($this->getPlugins()->contains($plugin)) {
            $this->collPlugins->remove($this->collPlugins->search($plugin));
            if (null === $this->pluginsScheduledForDeletion) {
                $this->pluginsScheduledForDeletion = clone $this->collPlugins;
                $this->pluginsScheduledForDeletion->clear();
            }
            $this->pluginsScheduledForDeletion[]= clone $plugin;
            $plugin->setAccount(null);
        }

        return $this;
    }

    /**
     * Clears out the collPropertys collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return Account The current object (for fluent API support)
     * @see        addPropertys()
     */
    public function clearPropertys()
    {
        $this->collPropertys = null; // important to set this to null since that means it is uninitialized
        $this->collPropertysPartial = null;

        return $this;
    }

    /**
     * reset is the collPropertys collection loaded partially
     *
     * @return void
     */
    public function resetPartialPropertys($v = true)
    {
        $this->collPropertysPartial = $v;
    }

    /**
     * Initializes the collPropertys collection.
     *
     * By default this just sets the collPropertys collection to an empty array (like clearcollPropertys());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initPropertys($overrideExisting = true)
    {
        if (null !== $this->collPropertys && !$overrideExisting) {
            return;
        }
        $this->collPropertys = new PropelObjectCollection();
        $this->collPropertys->setModel('Property');
    }

    /**
     * Gets an array of Property objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this Account is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|Property[] List of Property objects
     * @throws PropelException
     */
    public function getPropertys($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collPropertysPartial && !$this->isNew();
        if (null === $this->collPropertys || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collPropertys) {
                // return empty collection
                $this->initPropertys();
            } else {
                $collPropertys = PropertyQuery::create(null, $criteria)
                    ->filterByAccount($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collPropertysPartial && count($collPropertys)) {
                      $this->initPropertys(false);

                      foreach($collPropertys as $obj) {
                        if (false == $this->collPropertys->contains($obj)) {
                          $this->collPropertys->append($obj);
                        }
                      }

                      $this->collPropertysPartial = true;
                    }

                    $collPropertys->getInternalIterator()->rewind();
                    return $collPropertys;
                }

                if($partial && $this->collPropertys) {
                    foreach($this->collPropertys as $obj) {
                        if($obj->isNew()) {
                            $collPropertys[] = $obj;
                        }
                    }
                }

                $this->collPropertys = $collPropertys;
                $this->collPropertysPartial = false;
            }
        }

        return $this->collPropertys;
    }

    /**
     * Sets a collection of Property objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $propertys A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return Account The current object (for fluent API support)
     */
    public function setPropertys(PropelCollection $propertys, PropelPDO $con = null)
    {
        $propertysToDelete = $this->getPropertys(new Criteria(), $con)->diff($propertys);

        $this->propertysScheduledForDeletion = unserialize(serialize($propertysToDelete));

        foreach ($propertysToDelete as $propertyRemoved) {
            $propertyRemoved->setAccount(null);
        }

        $this->collPropertys = null;
        foreach ($propertys as $property) {
            $this->addProperty($property);
        }

        $this->collPropertys = $propertys;
        $this->collPropertysPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Property objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related Property objects.
     * @throws PropelException
     */
    public function countPropertys(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collPropertysPartial && !$this->isNew();
        if (null === $this->collPropertys || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collPropertys) {
                return 0;
            }

            if($partial && !$criteria) {
                return count($this->getPropertys());
            }
            $query = PropertyQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAccount($this)
                ->count($con);
        }

        return count($this->collPropertys);
    }

    /**
     * Method called to associate a Property object to this object
     * through the Property foreign key attribute.
     *
     * @param    Property $l Property
     * @return Account The current object (for fluent API support)
     */
    public function addProperty(Property $l)
    {
        if ($this->collPropertys === null) {
            $this->initPropertys();
            $this->collPropertysPartial = true;
        }
        if (!in_array($l, $this->collPropertys->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddProperty($l);
        }

        return $this;
    }

    /**
     * @param	Property $property The property object to add.
     */
    protected function doAddProperty($property)
    {
        $this->collPropertys[]= $property;
        $property->setAccount($this);
    }

    /**
     * @param	Property $property The property object to remove.
     * @return Account The current object (for fluent API support)
     */
    public function removeProperty($property)
    {
        if ($this->getPropertys()->contains($property)) {
            $this->collPropertys->remove($this->collPropertys->search($property));
            if (null === $this->propertysScheduledForDeletion) {
                $this->propertysScheduledForDeletion = clone $this->collPropertys;
                $this->propertysScheduledForDeletion->clear();
            }
            $this->propertysScheduledForDeletion[]= clone $property;
            $property->setAccount(null);
        }

        return $this;
    }

    /**
     * Clears out the collUsers collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return Account The current object (for fluent API support)
     * @see        addUsers()
     */
    public function clearUsers()
    {
        $this->collUsers = null; // important to set this to null since that means it is uninitialized
        $this->collUsersPartial = null;

        return $this;
    }

    /**
     * reset is the collUsers collection loaded partially
     *
     * @return void
     */
    public function resetPartialUsers($v = true)
    {
        $this->collUsersPartial = $v;
    }

    /**
     * Initializes the collUsers collection.
     *
     * By default this just sets the collUsers collection to an empty array (like clearcollUsers());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initUsers($overrideExisting = true)
    {
        if (null !== $this->collUsers && !$overrideExisting) {
            return;
        }
        $this->collUsers = new PropelObjectCollection();
        $this->collUsers->setModel('User');
    }

    /**
     * Gets an array of User objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this Account is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|User[] List of User objects
     * @throws PropelException
     */
    public function getUsers($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collUsersPartial && !$this->isNew();
        if (null === $this->collUsers || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collUsers) {
                // return empty collection
                $this->initUsers();
            } else {
                $collUsers = UserQuery::create(null, $criteria)
                    ->filterByAccount($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collUsersPartial && count($collUsers)) {
                      $this->initUsers(false);

                      foreach($collUsers as $obj) {
                        if (false == $this->collUsers->contains($obj)) {
                          $this->collUsers->append($obj);
                        }
                      }

                      $this->collUsersPartial = true;
                    }

                    $collUsers->getInternalIterator()->rewind();
                    return $collUsers;
                }

                if($partial && $this->collUsers) {
                    foreach($this->collUsers as $obj) {
                        if($obj->isNew()) {
                            $collUsers[] = $obj;
                        }
                    }
                }

                $this->collUsers = $collUsers;
                $this->collUsersPartial = false;
            }
        }

        return $this->collUsers;
    }

    /**
     * Sets a collection of User objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $users A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return Account The current object (for fluent API support)
     */
    public function setUsers(PropelCollection $users, PropelPDO $con = null)
    {
        $usersToDelete = $this->getUsers(new Criteria(), $con)->diff($users);

        $this->usersScheduledForDeletion = unserialize(serialize($usersToDelete));

        foreach ($usersToDelete as $userRemoved) {
            $userRemoved->setAccount(null);
        }

        $this->collUsers = null;
        foreach ($users as $user) {
            $this->addUser($user);
        }

        $this->collUsers = $users;
        $this->collUsersPartial = false;

        return $this;
    }

    /**
     * Returns the number of related User objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related User objects.
     * @throws PropelException
     */
    public function countUsers(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collUsersPartial && !$this->isNew();
        if (null === $this->collUsers || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collUsers) {
                return 0;
            }

            if($partial && !$criteria) {
                return count($this->getUsers());
            }
            $query = UserQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAccount($this)
                ->count($con);
        }

        return count($this->collUsers);
    }

    /**
     * Method called to associate a User object to this object
     * through the User foreign key attribute.
     *
     * @param    User $l User
     * @return Account The current object (for fluent API support)
     */
    public function addUser(User $l)
    {
        if ($this->collUsers === null) {
            $this->initUsers();
            $this->collUsersPartial = true;
        }
        if (!in_array($l, $this->collUsers->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddUser($l);
        }

        return $this;
    }

    /**
     * @param	User $user The user object to add.
     */
    protected function doAddUser($user)
    {
        $this->collUsers[]= $user;
        $user->setAccount($this);
    }

    /**
     * @param	User $user The user object to remove.
     * @return Account The current object (for fluent API support)
     */
    public function removeUser($user)
    {
        if ($this->getUsers()->contains($user)) {
            $this->collUsers->remove($this->collUsers->search($user));
            if (null === $this->usersScheduledForDeletion) {
                $this->usersScheduledForDeletion = clone $this->collUsers;
                $this->usersScheduledForDeletion->clear();
            }
            $this->usersScheduledForDeletion[]= clone $user;
            $user->setAccount(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Account is new, it will return
     * an empty collection; or if this Account has previously
     * been saved, it will retrieve related Users from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Account.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|User[] List of User objects
     */
    public function getUsersJoinDomain($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = UserQuery::create(null, $criteria);
        $query->joinWith('Domain', $join_behavior);

        return $this->getUsers($query, $con);
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id = null;
        $this->address_id = null;
        $this->identifier = null;
        $this->name = null;
        $this->alreadyInSave = false;
        $this->alreadyInValidation = false;
        $this->alreadyInClearAllReferencesDeep = false;
        $this->clearAllReferences();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references to other model objects or collections of model objects.
     *
     * This method is a user-space workaround for PHP's inability to garbage collect
     * objects with circular references (even in PHP 5.3). This is currently necessary
     * when using Propel in certain daemon or large-volumne/high-memory operations.
     *
     * @param boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep && !$this->alreadyInClearAllReferencesDeep) {
            $this->alreadyInClearAllReferencesDeep = true;
            if ($this->collBookingTypes) {
                foreach ($this->collBookingTypes as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collClockingTypes) {
                foreach ($this->collClockingTypes as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collDomains) {
                foreach ($this->collDomains as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collHolidays) {
                foreach ($this->collHolidays as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collPlugins) {
                foreach ($this->collPlugins as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collPropertys) {
                foreach ($this->collPropertys as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collUsers) {
                foreach ($this->collUsers as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->aAddress instanceof Persistent) {
              $this->aAddress->clearAllReferences($deep);
            }

            $this->alreadyInClearAllReferencesDeep = false;
        } // if ($deep)

        if ($this->collBookingTypes instanceof PropelCollection) {
            $this->collBookingTypes->clearIterator();
        }
        $this->collBookingTypes = null;
        if ($this->collClockingTypes instanceof PropelCollection) {
            $this->collClockingTypes->clearIterator();
        }
        $this->collClockingTypes = null;
        if ($this->collDomains instanceof PropelCollection) {
            $this->collDomains->clearIterator();
        }
        $this->collDomains = null;
        if ($this->collHolidays instanceof PropelCollection) {
            $this->collHolidays->clearIterator();
        }
        $this->collHolidays = null;
        if ($this->collPlugins instanceof PropelCollection) {
            $this->collPlugins->clearIterator();
        }
        $this->collPlugins = null;
        if ($this->collPropertys instanceof PropelCollection) {
            $this->collPropertys->clearIterator();
        }
        $this->collPropertys = null;
        if ($this->collUsers instanceof PropelCollection) {
            $this->collUsers->clearIterator();
        }
        $this->collUsers = null;
        $this->aAddress = null;
    }

    /**
     * return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(AccountPeer::DEFAULT_STRING_FORMAT);
    }

    /**
     * return true is the object is in saving state
     *
     * @return boolean
     */
    public function isAlreadyInSave()
    {
        return $this->alreadyInSave;
    }

}
