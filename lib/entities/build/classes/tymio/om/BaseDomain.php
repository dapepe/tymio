<?php


/**
 * Base class that represents a row from the 'domain' table.
 *
 *
 *
 * @package    propel.generator.tymio.om
 */
abstract class BaseDomain extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'DomainPeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        DomainPeer
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
     * The value for the account_id field.
     * @var        int
     */
    protected $account_id;

    /**
     * The value for the address_id field.
     * @var        int
     */
    protected $address_id;

    /**
     * The value for the name field.
     * @var        string
     */
    protected $name;

    /**
     * The value for the valid field.
     * @var        boolean
     */
    protected $valid;

    /**
     * The value for the description field.
     * @var        string
     */
    protected $description;

    /**
     * The value for the number field.
     * @var        string
     */
    protected $number;

    /**
     * @var        Account
     */
    protected $aAccount;

    /**
     * @var        Address
     */
    protected $aAddress;

    /**
     * @var        PropelObjectCollection|HolidayDomain[] Collection to store aggregation of HolidayDomain objects.
     */
    protected $collHolidayDomains;
    protected $collHolidayDomainsPartial;

    /**
     * @var        PropelObjectCollection|PropertyValue[] Collection to store aggregation of PropertyValue objects.
     */
    protected $collPropertyValues;
    protected $collPropertyValuesPartial;

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
    protected $holidayDomainsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $propertyValuesScheduledForDeletion = null;

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
     * Get the [account_id] column value.
     *
     * @return int
     */
    public function getAccountId()
    {
        return $this->account_id;
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
     * Get the [name] column value.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the [valid] column value.
     *
     * @return boolean
     */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     * Get the [description] column value.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get the [number] column value.
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set the value of [id] column.
     *
     * @param int $v new value
     * @return Domain The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[] = DomainPeer::ID;
        }


        return $this;
    } // setId()

    /**
     * Set the value of [account_id] column.
     *
     * @param int $v new value
     * @return Domain The current object (for fluent API support)
     */
    public function setAccountId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->account_id !== $v) {
            $this->account_id = $v;
            $this->modifiedColumns[] = DomainPeer::ACCOUNT_ID;
        }

        if ($this->aAccount !== null && $this->aAccount->getId() !== $v) {
            $this->aAccount = null;
        }


        return $this;
    } // setAccountId()

    /**
     * Set the value of [address_id] column.
     *
     * @param int $v new value
     * @return Domain The current object (for fluent API support)
     */
    public function setAddressId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->address_id !== $v) {
            $this->address_id = $v;
            $this->modifiedColumns[] = DomainPeer::ADDRESS_ID;
        }

        if ($this->aAddress !== null && $this->aAddress->getId() !== $v) {
            $this->aAddress = null;
        }


        return $this;
    } // setAddressId()

    /**
     * Set the value of [name] column.
     *
     * @param string $v new value
     * @return Domain The current object (for fluent API support)
     */
    public function setName($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->name !== $v) {
            $this->name = $v;
            $this->modifiedColumns[] = DomainPeer::NAME;
        }


        return $this;
    } // setName()

    /**
     * Sets the value of the [valid] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param boolean|integer|string $v The new value
     * @return Domain The current object (for fluent API support)
     */
    public function setValid($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->valid !== $v) {
            $this->valid = $v;
            $this->modifiedColumns[] = DomainPeer::VALID;
        }


        return $this;
    } // setValid()

    /**
     * Set the value of [description] column.
     *
     * @param string $v new value
     * @return Domain The current object (for fluent API support)
     */
    public function setDescription($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->description !== $v) {
            $this->description = $v;
            $this->modifiedColumns[] = DomainPeer::DESCRIPTION;
        }


        return $this;
    } // setDescription()

    /**
     * Set the value of [number] column.
     *
     * @param string $v new value
     * @return Domain The current object (for fluent API support)
     */
    public function setNumber($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->number !== $v) {
            $this->number = $v;
            $this->modifiedColumns[] = DomainPeer::NUMBER;
        }


        return $this;
    } // setNumber()

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
            $this->account_id = ($row[$startcol + 1] !== null) ? (int) $row[$startcol + 1] : null;
            $this->address_id = ($row[$startcol + 2] !== null) ? (int) $row[$startcol + 2] : null;
            $this->name = ($row[$startcol + 3] !== null) ? (string) $row[$startcol + 3] : null;
            $this->valid = ($row[$startcol + 4] !== null) ? (boolean) $row[$startcol + 4] : null;
            $this->description = ($row[$startcol + 5] !== null) ? (string) $row[$startcol + 5] : null;
            $this->number = ($row[$startcol + 6] !== null) ? (string) $row[$startcol + 6] : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
            $this->postHydrate($row, $startcol, $rehydrate);
            return $startcol + 7; // 7 = DomainPeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating Domain object", $e);
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

        if ($this->aAccount !== null && $this->account_id !== $this->aAccount->getId()) {
            $this->aAccount = null;
        }
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
            $con = Propel::getConnection(DomainPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = DomainPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aAccount = null;
            $this->aAddress = null;
            $this->collHolidayDomains = null;

            $this->collPropertyValues = null;

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
            $con = Propel::getConnection(DomainPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = DomainQuery::create()
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
            $con = Propel::getConnection(DomainPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
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
                DomainPeer::addInstanceToPool($this);
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

            if ($this->aAccount !== null) {
                if ($this->aAccount->isModified() || $this->aAccount->isNew()) {
                    $affectedRows += $this->aAccount->save($con);
                }
                $this->setAccount($this->aAccount);
            }

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

            if ($this->holidayDomainsScheduledForDeletion !== null) {
                if (!$this->holidayDomainsScheduledForDeletion->isEmpty()) {
                    HolidayDomainQuery::create()
                        ->filterByPrimaryKeys($this->holidayDomainsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->holidayDomainsScheduledForDeletion = null;
                }
            }

            if ($this->collHolidayDomains !== null) {
                foreach ($this->collHolidayDomains as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->propertyValuesScheduledForDeletion !== null) {
                if (!$this->propertyValuesScheduledForDeletion->isEmpty()) {
                    foreach ($this->propertyValuesScheduledForDeletion as $propertyValue) {
                        // need to save related object because we set the relation to null
                        $propertyValue->save($con);
                    }
                    $this->propertyValuesScheduledForDeletion = null;
                }
            }

            if ($this->collPropertyValues !== null) {
                foreach ($this->collPropertyValues as $referrerFK) {
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

        $this->modifiedColumns[] = DomainPeer::ID;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . DomainPeer::ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(DomainPeer::ID)) {
            $modifiedColumns[':p' . $index++]  = '`id`';
        }
        if ($this->isColumnModified(DomainPeer::ACCOUNT_ID)) {
            $modifiedColumns[':p' . $index++]  = '`account_id`';
        }
        if ($this->isColumnModified(DomainPeer::ADDRESS_ID)) {
            $modifiedColumns[':p' . $index++]  = '`address_id`';
        }
        if ($this->isColumnModified(DomainPeer::NAME)) {
            $modifiedColumns[':p' . $index++]  = '`name`';
        }
        if ($this->isColumnModified(DomainPeer::VALID)) {
            $modifiedColumns[':p' . $index++]  = '`valid`';
        }
        if ($this->isColumnModified(DomainPeer::DESCRIPTION)) {
            $modifiedColumns[':p' . $index++]  = '`description`';
        }
        if ($this->isColumnModified(DomainPeer::NUMBER)) {
            $modifiedColumns[':p' . $index++]  = '`number`';
        }

        $sql = sprintf(
            'INSERT INTO `domain` (%s) VALUES (%s)',
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
                    case '`account_id`':
                        $stmt->bindValue($identifier, $this->account_id, PDO::PARAM_INT);
                        break;
                    case '`address_id`':
                        $stmt->bindValue($identifier, $this->address_id, PDO::PARAM_INT);
                        break;
                    case '`name`':
                        $stmt->bindValue($identifier, $this->name, PDO::PARAM_STR);
                        break;
                    case '`valid`':
                        $stmt->bindValue($identifier, (int) $this->valid, PDO::PARAM_INT);
                        break;
                    case '`description`':
                        $stmt->bindValue($identifier, $this->description, PDO::PARAM_STR);
                        break;
                    case '`number`':
                        $stmt->bindValue($identifier, $this->number, PDO::PARAM_STR);
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

            if ($this->aAccount !== null) {
                if (!$this->aAccount->validate($columns)) {
                    $failureMap = array_merge($failureMap, $this->aAccount->getValidationFailures());
                }
            }

            if ($this->aAddress !== null) {
                if (!$this->aAddress->validate($columns)) {
                    $failureMap = array_merge($failureMap, $this->aAddress->getValidationFailures());
                }
            }


            if (($retval = DomainPeer::doValidate($this, $columns)) !== true) {
                $failureMap = array_merge($failureMap, $retval);
            }


                if ($this->collHolidayDomains !== null) {
                    foreach ($this->collHolidayDomains as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collPropertyValues !== null) {
                    foreach ($this->collPropertyValues as $referrerFK) {
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
        $pos = DomainPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
                return $this->getAccountId();
                break;
            case 2:
                return $this->getAddressId();
                break;
            case 3:
                return $this->getName();
                break;
            case 4:
                return $this->getValid();
                break;
            case 5:
                return $this->getDescription();
                break;
            case 6:
                return $this->getNumber();
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
        if (isset($alreadyDumpedObjects['Domain'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['Domain'][$this->getPrimaryKey()] = true;
        $keys = DomainPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getAccountId(),
            $keys[2] => $this->getAddressId(),
            $keys[3] => $this->getName(),
            $keys[4] => $this->getValid(),
            $keys[5] => $this->getDescription(),
            $keys[6] => $this->getNumber(),
        );
        if ($includeForeignObjects) {
            if (null !== $this->aAccount) {
                $result['Account'] = $this->aAccount->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aAddress) {
                $result['Address'] = $this->aAddress->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collHolidayDomains) {
                $result['HolidayDomains'] = $this->collHolidayDomains->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collPropertyValues) {
                $result['PropertyValues'] = $this->collPropertyValues->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = DomainPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

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
                $this->setAccountId($value);
                break;
            case 2:
                $this->setAddressId($value);
                break;
            case 3:
                $this->setName($value);
                break;
            case 4:
                $this->setValid($value);
                break;
            case 5:
                $this->setDescription($value);
                break;
            case 6:
                $this->setNumber($value);
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
        $keys = DomainPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setAccountId($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setAddressId($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setName($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setValid($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setDescription($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setNumber($arr[$keys[6]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(DomainPeer::DATABASE_NAME);

        if ($this->isColumnModified(DomainPeer::ID)) $criteria->add(DomainPeer::ID, $this->id);
        if ($this->isColumnModified(DomainPeer::ACCOUNT_ID)) $criteria->add(DomainPeer::ACCOUNT_ID, $this->account_id);
        if ($this->isColumnModified(DomainPeer::ADDRESS_ID)) $criteria->add(DomainPeer::ADDRESS_ID, $this->address_id);
        if ($this->isColumnModified(DomainPeer::NAME)) $criteria->add(DomainPeer::NAME, $this->name);
        if ($this->isColumnModified(DomainPeer::VALID)) $criteria->add(DomainPeer::VALID, $this->valid);
        if ($this->isColumnModified(DomainPeer::DESCRIPTION)) $criteria->add(DomainPeer::DESCRIPTION, $this->description);
        if ($this->isColumnModified(DomainPeer::NUMBER)) $criteria->add(DomainPeer::NUMBER, $this->number);

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
        $criteria = new Criteria(DomainPeer::DATABASE_NAME);
        $criteria->add(DomainPeer::ID, $this->id);

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
     * @param object $copyObj An object of Domain (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setAccountId($this->getAccountId());
        $copyObj->setAddressId($this->getAddressId());
        $copyObj->setName($this->getName());
        $copyObj->setValid($this->getValid());
        $copyObj->setDescription($this->getDescription());
        $copyObj->setNumber($this->getNumber());

        if ($deepCopy && !$this->startCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);
            // store object hash to prevent cycle
            $this->startCopy = true;

            foreach ($this->getHolidayDomains() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addHolidayDomain($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getPropertyValues() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addPropertyValue($relObj->copy($deepCopy));
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
     * @return Domain Clone of current object.
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
     * @return DomainPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new DomainPeer();
        }

        return self::$peer;
    }

    /**
     * Declares an association between this object and a Account object.
     *
     * @param             Account $v
     * @return Domain The current object (for fluent API support)
     * @throws PropelException
     */
    public function setAccount(Account $v = null)
    {
        if ($v === null) {
            $this->setAccountId(NULL);
        } else {
            $this->setAccountId($v->getId());
        }

        $this->aAccount = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the Account object, it will not be re-added.
        if ($v !== null) {
            $v->addDomain($this);
        }


        return $this;
    }


    /**
     * Get the associated Account object
     *
     * @param PropelPDO $con Optional Connection object.
     * @param $doQuery Executes a query to get the object if required
     * @return Account The associated Account object.
     * @throws PropelException
     */
    public function getAccount(PropelPDO $con = null, $doQuery = true)
    {
        if ($this->aAccount === null && ($this->account_id !== null) && $doQuery) {
            $this->aAccount = AccountQuery::create()->findPk($this->account_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aAccount->addDomains($this);
             */
        }

        return $this->aAccount;
    }

    /**
     * Declares an association between this object and a Address object.
     *
     * @param             Address $v
     * @return Domain The current object (for fluent API support)
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
            $v->addDomain($this);
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
                $this->aAddress->addDomains($this);
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
        if ('HolidayDomain' == $relationName) {
            $this->initHolidayDomains();
        }
        if ('PropertyValue' == $relationName) {
            $this->initPropertyValues();
        }
        if ('User' == $relationName) {
            $this->initUsers();
        }
    }

    /**
     * Clears out the collHolidayDomains collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return Domain The current object (for fluent API support)
     * @see        addHolidayDomains()
     */
    public function clearHolidayDomains()
    {
        $this->collHolidayDomains = null; // important to set this to null since that means it is uninitialized
        $this->collHolidayDomainsPartial = null;

        return $this;
    }

    /**
     * reset is the collHolidayDomains collection loaded partially
     *
     * @return void
     */
    public function resetPartialHolidayDomains($v = true)
    {
        $this->collHolidayDomainsPartial = $v;
    }

    /**
     * Initializes the collHolidayDomains collection.
     *
     * By default this just sets the collHolidayDomains collection to an empty array (like clearcollHolidayDomains());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initHolidayDomains($overrideExisting = true)
    {
        if (null !== $this->collHolidayDomains && !$overrideExisting) {
            return;
        }
        $this->collHolidayDomains = new PropelObjectCollection();
        $this->collHolidayDomains->setModel('HolidayDomain');
    }

    /**
     * Gets an array of HolidayDomain objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this Domain is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|HolidayDomain[] List of HolidayDomain objects
     * @throws PropelException
     */
    public function getHolidayDomains($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collHolidayDomainsPartial && !$this->isNew();
        if (null === $this->collHolidayDomains || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collHolidayDomains) {
                // return empty collection
                $this->initHolidayDomains();
            } else {
                $collHolidayDomains = HolidayDomainQuery::create(null, $criteria)
                    ->filterByDomain($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collHolidayDomainsPartial && count($collHolidayDomains)) {
                      $this->initHolidayDomains(false);

                      foreach($collHolidayDomains as $obj) {
                        if (false == $this->collHolidayDomains->contains($obj)) {
                          $this->collHolidayDomains->append($obj);
                        }
                      }

                      $this->collHolidayDomainsPartial = true;
                    }

                    $collHolidayDomains->getInternalIterator()->rewind();
                    return $collHolidayDomains;
                }

                if($partial && $this->collHolidayDomains) {
                    foreach($this->collHolidayDomains as $obj) {
                        if($obj->isNew()) {
                            $collHolidayDomains[] = $obj;
                        }
                    }
                }

                $this->collHolidayDomains = $collHolidayDomains;
                $this->collHolidayDomainsPartial = false;
            }
        }

        return $this->collHolidayDomains;
    }

    /**
     * Sets a collection of HolidayDomain objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $holidayDomains A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return Domain The current object (for fluent API support)
     */
    public function setHolidayDomains(PropelCollection $holidayDomains, PropelPDO $con = null)
    {
        $holidayDomainsToDelete = $this->getHolidayDomains(new Criteria(), $con)->diff($holidayDomains);

        $this->holidayDomainsScheduledForDeletion = unserialize(serialize($holidayDomainsToDelete));

        foreach ($holidayDomainsToDelete as $holidayDomainRemoved) {
            $holidayDomainRemoved->setDomain(null);
        }

        $this->collHolidayDomains = null;
        foreach ($holidayDomains as $holidayDomain) {
            $this->addHolidayDomain($holidayDomain);
        }

        $this->collHolidayDomains = $holidayDomains;
        $this->collHolidayDomainsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related HolidayDomain objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related HolidayDomain objects.
     * @throws PropelException
     */
    public function countHolidayDomains(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collHolidayDomainsPartial && !$this->isNew();
        if (null === $this->collHolidayDomains || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collHolidayDomains) {
                return 0;
            }

            if($partial && !$criteria) {
                return count($this->getHolidayDomains());
            }
            $query = HolidayDomainQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByDomain($this)
                ->count($con);
        }

        return count($this->collHolidayDomains);
    }

    /**
     * Method called to associate a HolidayDomain object to this object
     * through the HolidayDomain foreign key attribute.
     *
     * @param    HolidayDomain $l HolidayDomain
     * @return Domain The current object (for fluent API support)
     */
    public function addHolidayDomain(HolidayDomain $l)
    {
        if ($this->collHolidayDomains === null) {
            $this->initHolidayDomains();
            $this->collHolidayDomainsPartial = true;
        }
        if (!in_array($l, $this->collHolidayDomains->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddHolidayDomain($l);
        }

        return $this;
    }

    /**
     * @param	HolidayDomain $holidayDomain The holidayDomain object to add.
     */
    protected function doAddHolidayDomain($holidayDomain)
    {
        $this->collHolidayDomains[]= $holidayDomain;
        $holidayDomain->setDomain($this);
    }

    /**
     * @param	HolidayDomain $holidayDomain The holidayDomain object to remove.
     * @return Domain The current object (for fluent API support)
     */
    public function removeHolidayDomain($holidayDomain)
    {
        if ($this->getHolidayDomains()->contains($holidayDomain)) {
            $this->collHolidayDomains->remove($this->collHolidayDomains->search($holidayDomain));
            if (null === $this->holidayDomainsScheduledForDeletion) {
                $this->holidayDomainsScheduledForDeletion = clone $this->collHolidayDomains;
                $this->holidayDomainsScheduledForDeletion->clear();
            }
            $this->holidayDomainsScheduledForDeletion[]= clone $holidayDomain;
            $holidayDomain->setDomain(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Domain is new, it will return
     * an empty collection; or if this Domain has previously
     * been saved, it will retrieve related HolidayDomains from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Domain.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|HolidayDomain[] List of HolidayDomain objects
     */
    public function getHolidayDomainsJoinHoliday($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = HolidayDomainQuery::create(null, $criteria);
        $query->joinWith('Holiday', $join_behavior);

        return $this->getHolidayDomains($query, $con);
    }

    /**
     * Clears out the collPropertyValues collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return Domain The current object (for fluent API support)
     * @see        addPropertyValues()
     */
    public function clearPropertyValues()
    {
        $this->collPropertyValues = null; // important to set this to null since that means it is uninitialized
        $this->collPropertyValuesPartial = null;

        return $this;
    }

    /**
     * reset is the collPropertyValues collection loaded partially
     *
     * @return void
     */
    public function resetPartialPropertyValues($v = true)
    {
        $this->collPropertyValuesPartial = $v;
    }

    /**
     * Initializes the collPropertyValues collection.
     *
     * By default this just sets the collPropertyValues collection to an empty array (like clearcollPropertyValues());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initPropertyValues($overrideExisting = true)
    {
        if (null !== $this->collPropertyValues && !$overrideExisting) {
            return;
        }
        $this->collPropertyValues = new PropelObjectCollection();
        $this->collPropertyValues->setModel('PropertyValue');
    }

    /**
     * Gets an array of PropertyValue objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this Domain is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|PropertyValue[] List of PropertyValue objects
     * @throws PropelException
     */
    public function getPropertyValues($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collPropertyValuesPartial && !$this->isNew();
        if (null === $this->collPropertyValues || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collPropertyValues) {
                // return empty collection
                $this->initPropertyValues();
            } else {
                $collPropertyValues = PropertyValueQuery::create(null, $criteria)
                    ->filterByDomain($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collPropertyValuesPartial && count($collPropertyValues)) {
                      $this->initPropertyValues(false);

                      foreach($collPropertyValues as $obj) {
                        if (false == $this->collPropertyValues->contains($obj)) {
                          $this->collPropertyValues->append($obj);
                        }
                      }

                      $this->collPropertyValuesPartial = true;
                    }

                    $collPropertyValues->getInternalIterator()->rewind();
                    return $collPropertyValues;
                }

                if($partial && $this->collPropertyValues) {
                    foreach($this->collPropertyValues as $obj) {
                        if($obj->isNew()) {
                            $collPropertyValues[] = $obj;
                        }
                    }
                }

                $this->collPropertyValues = $collPropertyValues;
                $this->collPropertyValuesPartial = false;
            }
        }

        return $this->collPropertyValues;
    }

    /**
     * Sets a collection of PropertyValue objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $propertyValues A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return Domain The current object (for fluent API support)
     */
    public function setPropertyValues(PropelCollection $propertyValues, PropelPDO $con = null)
    {
        $propertyValuesToDelete = $this->getPropertyValues(new Criteria(), $con)->diff($propertyValues);

        $this->propertyValuesScheduledForDeletion = unserialize(serialize($propertyValuesToDelete));

        foreach ($propertyValuesToDelete as $propertyValueRemoved) {
            $propertyValueRemoved->setDomain(null);
        }

        $this->collPropertyValues = null;
        foreach ($propertyValues as $propertyValue) {
            $this->addPropertyValue($propertyValue);
        }

        $this->collPropertyValues = $propertyValues;
        $this->collPropertyValuesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related PropertyValue objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related PropertyValue objects.
     * @throws PropelException
     */
    public function countPropertyValues(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collPropertyValuesPartial && !$this->isNew();
        if (null === $this->collPropertyValues || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collPropertyValues) {
                return 0;
            }

            if($partial && !$criteria) {
                return count($this->getPropertyValues());
            }
            $query = PropertyValueQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByDomain($this)
                ->count($con);
        }

        return count($this->collPropertyValues);
    }

    /**
     * Method called to associate a PropertyValue object to this object
     * through the PropertyValue foreign key attribute.
     *
     * @param    PropertyValue $l PropertyValue
     * @return Domain The current object (for fluent API support)
     */
    public function addPropertyValue(PropertyValue $l)
    {
        if ($this->collPropertyValues === null) {
            $this->initPropertyValues();
            $this->collPropertyValuesPartial = true;
        }
        if (!in_array($l, $this->collPropertyValues->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddPropertyValue($l);
        }

        return $this;
    }

    /**
     * @param	PropertyValue $propertyValue The propertyValue object to add.
     */
    protected function doAddPropertyValue($propertyValue)
    {
        $this->collPropertyValues[]= $propertyValue;
        $propertyValue->setDomain($this);
    }

    /**
     * @param	PropertyValue $propertyValue The propertyValue object to remove.
     * @return Domain The current object (for fluent API support)
     */
    public function removePropertyValue($propertyValue)
    {
        if ($this->getPropertyValues()->contains($propertyValue)) {
            $this->collPropertyValues->remove($this->collPropertyValues->search($propertyValue));
            if (null === $this->propertyValuesScheduledForDeletion) {
                $this->propertyValuesScheduledForDeletion = clone $this->collPropertyValues;
                $this->propertyValuesScheduledForDeletion->clear();
            }
            $this->propertyValuesScheduledForDeletion[]= $propertyValue;
            $propertyValue->setDomain(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Domain is new, it will return
     * an empty collection; or if this Domain has previously
     * been saved, it will retrieve related PropertyValues from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Domain.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|PropertyValue[] List of PropertyValue objects
     */
    public function getPropertyValuesJoinProperty($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = PropertyValueQuery::create(null, $criteria);
        $query->joinWith('Property', $join_behavior);

        return $this->getPropertyValues($query, $con);
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Domain is new, it will return
     * an empty collection; or if this Domain has previously
     * been saved, it will retrieve related PropertyValues from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Domain.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|PropertyValue[] List of PropertyValue objects
     */
    public function getPropertyValuesJoinUser($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = PropertyValueQuery::create(null, $criteria);
        $query->joinWith('User', $join_behavior);

        return $this->getPropertyValues($query, $con);
    }

    /**
     * Clears out the collUsers collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return Domain The current object (for fluent API support)
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
     * If this Domain is new, it will return
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
                    ->filterByDomain($this)
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
     * @return Domain The current object (for fluent API support)
     */
    public function setUsers(PropelCollection $users, PropelPDO $con = null)
    {
        $usersToDelete = $this->getUsers(new Criteria(), $con)->diff($users);

        $this->usersScheduledForDeletion = unserialize(serialize($usersToDelete));

        foreach ($usersToDelete as $userRemoved) {
            $userRemoved->setDomain(null);
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
                ->filterByDomain($this)
                ->count($con);
        }

        return count($this->collUsers);
    }

    /**
     * Method called to associate a User object to this object
     * through the User foreign key attribute.
     *
     * @param    User $l User
     * @return Domain The current object (for fluent API support)
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
        $user->setDomain($this);
    }

    /**
     * @param	User $user The user object to remove.
     * @return Domain The current object (for fluent API support)
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
            $user->setDomain(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Domain is new, it will return
     * an empty collection; or if this Domain has previously
     * been saved, it will retrieve related Users from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Domain.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|User[] List of User objects
     */
    public function getUsersJoinAccount($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = UserQuery::create(null, $criteria);
        $query->joinWith('Account', $join_behavior);

        return $this->getUsers($query, $con);
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id = null;
        $this->account_id = null;
        $this->address_id = null;
        $this->name = null;
        $this->valid = null;
        $this->description = null;
        $this->number = null;
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
            if ($this->collHolidayDomains) {
                foreach ($this->collHolidayDomains as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collPropertyValues) {
                foreach ($this->collPropertyValues as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collUsers) {
                foreach ($this->collUsers as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->aAccount instanceof Persistent) {
              $this->aAccount->clearAllReferences($deep);
            }
            if ($this->aAddress instanceof Persistent) {
              $this->aAddress->clearAllReferences($deep);
            }

            $this->alreadyInClearAllReferencesDeep = false;
        } // if ($deep)

        if ($this->collHolidayDomains instanceof PropelCollection) {
            $this->collHolidayDomains->clearIterator();
        }
        $this->collHolidayDomains = null;
        if ($this->collPropertyValues instanceof PropelCollection) {
            $this->collPropertyValues->clearIterator();
        }
        $this->collPropertyValues = null;
        if ($this->collUsers instanceof PropelCollection) {
            $this->collUsers->clearIterator();
        }
        $this->collUsers = null;
        $this->aAccount = null;
        $this->aAddress = null;
    }

    /**
     * return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(DomainPeer::DEFAULT_STRING_FORMAT);
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