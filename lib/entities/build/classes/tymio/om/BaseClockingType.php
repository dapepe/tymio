<?php


/**
 * Base class that represents a row from the 'clocking_type' table.
 *
 *
 *
 * @package    propel.generator.tymio.om
 */
abstract class BaseClockingType extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'ClockingTypePeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        ClockingTypePeer
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
     * The value for the identifier field.
     * @var        string
     */
    protected $identifier;

    /**
     * The value for the label field.
     * @var        string
     */
    protected $label;

    /**
     * The value for the whole_day field.
     * Note: this column has a database default value of: false
     * @var        boolean
     */
    protected $whole_day;

    /**
     * The value for the future_grace_time field.
     * @var        string
     */
    protected $future_grace_time;

    /**
     * The value for the past_grace_time field.
     * @var        string
     */
    protected $past_grace_time;

    /**
     * The value for the approval_required field.
     * Note: this column has a database default value of: false
     * @var        boolean
     */
    protected $approval_required;

    /**
     * @var        Account
     */
    protected $aAccount;

    /**
     * @var        PropelObjectCollection|Clocking[] Collection to store aggregation of Clocking objects.
     */
    protected $collClockings;
    protected $collClockingsPartial;

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
    protected $clockingsScheduledForDeletion = null;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see        __construct()
     */
    public function applyDefaultValues()
    {
        $this->whole_day = false;
        $this->approval_required = false;
    }

    /**
     * Initializes internal state of BaseClockingType object.
     * @see        applyDefaults()
     */
    public function __construct()
    {
        parent::__construct();
        $this->applyDefaultValues();
    }

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
     * Get the [identifier] column value.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Get the [label] column value.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get the [whole_day] column value.
     *
     * @return boolean
     */
    public function getWholeDay()
    {
        return $this->whole_day;
    }

    /**
     * Get the [future_grace_time] column value.
     *
     * @return string
     */
    public function getFutureGraceTime()
    {
        return $this->future_grace_time;
    }

    /**
     * Get the [past_grace_time] column value.
     *
     * @return string
     */
    public function getPastGraceTime()
    {
        return $this->past_grace_time;
    }

    /**
     * Get the [approval_required] column value.
     *
     * @return boolean
     */
    public function getApprovalRequired()
    {
        return $this->approval_required;
    }

    /**
     * Set the value of [id] column.
     *
     * @param int $v new value
     * @return ClockingType The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[] = ClockingTypePeer::ID;
        }


        return $this;
    } // setId()

    /**
     * Set the value of [account_id] column.
     *
     * @param int $v new value
     * @return ClockingType The current object (for fluent API support)
     */
    public function setAccountId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->account_id !== $v) {
            $this->account_id = $v;
            $this->modifiedColumns[] = ClockingTypePeer::ACCOUNT_ID;
        }

        if ($this->aAccount !== null && $this->aAccount->getId() !== $v) {
            $this->aAccount = null;
        }


        return $this;
    } // setAccountId()

    /**
     * Set the value of [identifier] column.
     *
     * @param string $v new value
     * @return ClockingType The current object (for fluent API support)
     */
    public function setIdentifier($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->identifier !== $v) {
            $this->identifier = $v;
            $this->modifiedColumns[] = ClockingTypePeer::IDENTIFIER;
        }


        return $this;
    } // setIdentifier()

    /**
     * Set the value of [label] column.
     *
     * @param string $v new value
     * @return ClockingType The current object (for fluent API support)
     */
    public function setLabel($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->label !== $v) {
            $this->label = $v;
            $this->modifiedColumns[] = ClockingTypePeer::LABEL;
        }


        return $this;
    } // setLabel()

    /**
     * Sets the value of the [whole_day] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param boolean|integer|string $v The new value
     * @return ClockingType The current object (for fluent API support)
     */
    public function setWholeDay($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->whole_day !== $v) {
            $this->whole_day = $v;
            $this->modifiedColumns[] = ClockingTypePeer::WHOLE_DAY;
        }


        return $this;
    } // setWholeDay()

    /**
     * Set the value of [future_grace_time] column.
     *
     * @param string $v new value
     * @return ClockingType The current object (for fluent API support)
     */
    public function setFutureGraceTime($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->future_grace_time !== $v) {
            $this->future_grace_time = $v;
            $this->modifiedColumns[] = ClockingTypePeer::FUTURE_GRACE_TIME;
        }


        return $this;
    } // setFutureGraceTime()

    /**
     * Set the value of [past_grace_time] column.
     *
     * @param string $v new value
     * @return ClockingType The current object (for fluent API support)
     */
    public function setPastGraceTime($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->past_grace_time !== $v) {
            $this->past_grace_time = $v;
            $this->modifiedColumns[] = ClockingTypePeer::PAST_GRACE_TIME;
        }


        return $this;
    } // setPastGraceTime()

    /**
     * Sets the value of the [approval_required] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param boolean|integer|string $v The new value
     * @return ClockingType The current object (for fluent API support)
     */
    public function setApprovalRequired($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->approval_required !== $v) {
            $this->approval_required = $v;
            $this->modifiedColumns[] = ClockingTypePeer::APPROVAL_REQUIRED;
        }


        return $this;
    } // setApprovalRequired()

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
            if ($this->whole_day !== false) {
                return false;
            }

            if ($this->approval_required !== false) {
                return false;
            }

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
            $this->identifier = ($row[$startcol + 2] !== null) ? (string) $row[$startcol + 2] : null;
            $this->label = ($row[$startcol + 3] !== null) ? (string) $row[$startcol + 3] : null;
            $this->whole_day = ($row[$startcol + 4] !== null) ? (boolean) $row[$startcol + 4] : null;
            $this->future_grace_time = ($row[$startcol + 5] !== null) ? (string) $row[$startcol + 5] : null;
            $this->past_grace_time = ($row[$startcol + 6] !== null) ? (string) $row[$startcol + 6] : null;
            $this->approval_required = ($row[$startcol + 7] !== null) ? (boolean) $row[$startcol + 7] : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
            $this->postHydrate($row, $startcol, $rehydrate);
            return $startcol + 8; // 8 = ClockingTypePeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating ClockingType object", $e);
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
            $con = Propel::getConnection(ClockingTypePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = ClockingTypePeer::doSelectStmt($this->buildPkeyCriteria(), $con);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aAccount = null;
            $this->collClockings = null;

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
            $con = Propel::getConnection(ClockingTypePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = ClockingTypeQuery::create()
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
            $con = Propel::getConnection(ClockingTypePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
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
                ClockingTypePeer::addInstanceToPool($this);
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

            if ($this->clockingsScheduledForDeletion !== null) {
                if (!$this->clockingsScheduledForDeletion->isEmpty()) {
                    ClockingQuery::create()
                        ->filterByPrimaryKeys($this->clockingsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->clockingsScheduledForDeletion = null;
                }
            }

            if ($this->collClockings !== null) {
                foreach ($this->collClockings as $referrerFK) {
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

        $this->modifiedColumns[] = ClockingTypePeer::ID;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . ClockingTypePeer::ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(ClockingTypePeer::ID)) {
            $modifiedColumns[':p' . $index++]  = '`id`';
        }
        if ($this->isColumnModified(ClockingTypePeer::ACCOUNT_ID)) {
            $modifiedColumns[':p' . $index++]  = '`account_id`';
        }
        if ($this->isColumnModified(ClockingTypePeer::IDENTIFIER)) {
            $modifiedColumns[':p' . $index++]  = '`identifier`';
        }
        if ($this->isColumnModified(ClockingTypePeer::LABEL)) {
            $modifiedColumns[':p' . $index++]  = '`label`';
        }
        if ($this->isColumnModified(ClockingTypePeer::WHOLE_DAY)) {
            $modifiedColumns[':p' . $index++]  = '`whole_day`';
        }
        if ($this->isColumnModified(ClockingTypePeer::FUTURE_GRACE_TIME)) {
            $modifiedColumns[':p' . $index++]  = '`future_grace_time`';
        }
        if ($this->isColumnModified(ClockingTypePeer::PAST_GRACE_TIME)) {
            $modifiedColumns[':p' . $index++]  = '`past_grace_time`';
        }
        if ($this->isColumnModified(ClockingTypePeer::APPROVAL_REQUIRED)) {
            $modifiedColumns[':p' . $index++]  = '`approval_required`';
        }

        $sql = sprintf(
            'INSERT INTO `clocking_type` (%s) VALUES (%s)',
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
                    case '`identifier`':
                        $stmt->bindValue($identifier, $this->identifier, PDO::PARAM_STR);
                        break;
                    case '`label`':
                        $stmt->bindValue($identifier, $this->label, PDO::PARAM_STR);
                        break;
                    case '`whole_day`':
                        $stmt->bindValue($identifier, (int) $this->whole_day, PDO::PARAM_INT);
                        break;
                    case '`future_grace_time`':
                        $stmt->bindValue($identifier, $this->future_grace_time, PDO::PARAM_STR);
                        break;
                    case '`past_grace_time`':
                        $stmt->bindValue($identifier, $this->past_grace_time, PDO::PARAM_STR);
                        break;
                    case '`approval_required`':
                        $stmt->bindValue($identifier, (int) $this->approval_required, PDO::PARAM_INT);
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


            if (($retval = ClockingTypePeer::doValidate($this, $columns)) !== true) {
                $failureMap = array_merge($failureMap, $retval);
            }


                if ($this->collClockings !== null) {
                    foreach ($this->collClockings as $referrerFK) {
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
        $pos = ClockingTypePeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
                return $this->getIdentifier();
                break;
            case 3:
                return $this->getLabel();
                break;
            case 4:
                return $this->getWholeDay();
                break;
            case 5:
                return $this->getFutureGraceTime();
                break;
            case 6:
                return $this->getPastGraceTime();
                break;
            case 7:
                return $this->getApprovalRequired();
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
        if (isset($alreadyDumpedObjects['ClockingType'][serialize($this->getPrimaryKey())])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['ClockingType'][serialize($this->getPrimaryKey())] = true;
        $keys = ClockingTypePeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getAccountId(),
            $keys[2] => $this->getIdentifier(),
            $keys[3] => $this->getLabel(),
            $keys[4] => $this->getWholeDay(),
            $keys[5] => $this->getFutureGraceTime(),
            $keys[6] => $this->getPastGraceTime(),
            $keys[7] => $this->getApprovalRequired(),
        );
        if ($includeForeignObjects) {
            if (null !== $this->aAccount) {
                $result['Account'] = $this->aAccount->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collClockings) {
                $result['Clockings'] = $this->collClockings->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = ClockingTypePeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

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
                $this->setIdentifier($value);
                break;
            case 3:
                $this->setLabel($value);
                break;
            case 4:
                $this->setWholeDay($value);
                break;
            case 5:
                $this->setFutureGraceTime($value);
                break;
            case 6:
                $this->setPastGraceTime($value);
                break;
            case 7:
                $this->setApprovalRequired($value);
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
        $keys = ClockingTypePeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setAccountId($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setIdentifier($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setLabel($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setWholeDay($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setFutureGraceTime($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setPastGraceTime($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setApprovalRequired($arr[$keys[7]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(ClockingTypePeer::DATABASE_NAME);

        if ($this->isColumnModified(ClockingTypePeer::ID)) $criteria->add(ClockingTypePeer::ID, $this->id);
        if ($this->isColumnModified(ClockingTypePeer::ACCOUNT_ID)) $criteria->add(ClockingTypePeer::ACCOUNT_ID, $this->account_id);
        if ($this->isColumnModified(ClockingTypePeer::IDENTIFIER)) $criteria->add(ClockingTypePeer::IDENTIFIER, $this->identifier);
        if ($this->isColumnModified(ClockingTypePeer::LABEL)) $criteria->add(ClockingTypePeer::LABEL, $this->label);
        if ($this->isColumnModified(ClockingTypePeer::WHOLE_DAY)) $criteria->add(ClockingTypePeer::WHOLE_DAY, $this->whole_day);
        if ($this->isColumnModified(ClockingTypePeer::FUTURE_GRACE_TIME)) $criteria->add(ClockingTypePeer::FUTURE_GRACE_TIME, $this->future_grace_time);
        if ($this->isColumnModified(ClockingTypePeer::PAST_GRACE_TIME)) $criteria->add(ClockingTypePeer::PAST_GRACE_TIME, $this->past_grace_time);
        if ($this->isColumnModified(ClockingTypePeer::APPROVAL_REQUIRED)) $criteria->add(ClockingTypePeer::APPROVAL_REQUIRED, $this->approval_required);

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
        $criteria = new Criteria(ClockingTypePeer::DATABASE_NAME);
        $criteria->add(ClockingTypePeer::ID, $this->id);
        $criteria->add(ClockingTypePeer::ACCOUNT_ID, $this->account_id);

        return $criteria;
    }

    /**
     * Returns the composite primary key for this object.
     * The array elements will be in same order as specified in XML.
     * @return array
     */
    public function getPrimaryKey()
    {
        $pks = array();
        $pks[0] = $this->getId();
        $pks[1] = $this->getAccountId();

        return $pks;
    }

    /**
     * Set the [composite] primary key.
     *
     * @param array $keys The elements of the composite key (order must match the order in XML file).
     * @return void
     */
    public function setPrimaryKey($keys)
    {
        $this->setId($keys[0]);
        $this->setAccountId($keys[1]);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return (null === $this->getId()) && (null === $this->getAccountId());
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of ClockingType (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setAccountId($this->getAccountId());
        $copyObj->setIdentifier($this->getIdentifier());
        $copyObj->setLabel($this->getLabel());
        $copyObj->setWholeDay($this->getWholeDay());
        $copyObj->setFutureGraceTime($this->getFutureGraceTime());
        $copyObj->setPastGraceTime($this->getPastGraceTime());
        $copyObj->setApprovalRequired($this->getApprovalRequired());

        if ($deepCopy && !$this->startCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);
            // store object hash to prevent cycle
            $this->startCopy = true;

            foreach ($this->getClockings() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addClocking($relObj->copy($deepCopy));
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
     * @return ClockingType Clone of current object.
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
     * @return ClockingTypePeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new ClockingTypePeer();
        }

        return self::$peer;
    }

    /**
     * Declares an association between this object and a Account object.
     *
     * @param             Account $v
     * @return ClockingType The current object (for fluent API support)
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
            $v->addClockingType($this);
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
                $this->aAccount->addClockingTypes($this);
             */
        }

        return $this->aAccount;
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
        if ('Clocking' == $relationName) {
            $this->initClockings();
        }
    }

    /**
     * Clears out the collClockings collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return ClockingType The current object (for fluent API support)
     * @see        addClockings()
     */
    public function clearClockings()
    {
        $this->collClockings = null; // important to set this to null since that means it is uninitialized
        $this->collClockingsPartial = null;

        return $this;
    }

    /**
     * reset is the collClockings collection loaded partially
     *
     * @return void
     */
    public function resetPartialClockings($v = true)
    {
        $this->collClockingsPartial = $v;
    }

    /**
     * Initializes the collClockings collection.
     *
     * By default this just sets the collClockings collection to an empty array (like clearcollClockings());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initClockings($overrideExisting = true)
    {
        if (null !== $this->collClockings && !$overrideExisting) {
            return;
        }
        $this->collClockings = new PropelObjectCollection();
        $this->collClockings->setModel('Clocking');
    }

    /**
     * Gets an array of Clocking objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ClockingType is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|Clocking[] List of Clocking objects
     * @throws PropelException
     */
    public function getClockings($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collClockingsPartial && !$this->isNew();
        if (null === $this->collClockings || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collClockings) {
                // return empty collection
                $this->initClockings();
            } else {
                $collClockings = ClockingQuery::create(null, $criteria)
                    ->filterByClockingType($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collClockingsPartial && count($collClockings)) {
                      $this->initClockings(false);

                      foreach($collClockings as $obj) {
                        if (false == $this->collClockings->contains($obj)) {
                          $this->collClockings->append($obj);
                        }
                      }

                      $this->collClockingsPartial = true;
                    }

                    $collClockings->getInternalIterator()->rewind();
                    return $collClockings;
                }

                if($partial && $this->collClockings) {
                    foreach($this->collClockings as $obj) {
                        if($obj->isNew()) {
                            $collClockings[] = $obj;
                        }
                    }
                }

                $this->collClockings = $collClockings;
                $this->collClockingsPartial = false;
            }
        }

        return $this->collClockings;
    }

    /**
     * Sets a collection of Clocking objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $clockings A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return ClockingType The current object (for fluent API support)
     */
    public function setClockings(PropelCollection $clockings, PropelPDO $con = null)
    {
        $clockingsToDelete = $this->getClockings(new Criteria(), $con)->diff($clockings);

        $this->clockingsScheduledForDeletion = unserialize(serialize($clockingsToDelete));

        foreach ($clockingsToDelete as $clockingRemoved) {
            $clockingRemoved->setClockingType(null);
        }

        $this->collClockings = null;
        foreach ($clockings as $clocking) {
            $this->addClocking($clocking);
        }

        $this->collClockings = $clockings;
        $this->collClockingsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Clocking objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related Clocking objects.
     * @throws PropelException
     */
    public function countClockings(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collClockingsPartial && !$this->isNew();
        if (null === $this->collClockings || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collClockings) {
                return 0;
            }

            if($partial && !$criteria) {
                return count($this->getClockings());
            }
            $query = ClockingQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByClockingType($this)
                ->count($con);
        }

        return count($this->collClockings);
    }

    /**
     * Method called to associate a Clocking object to this object
     * through the Clocking foreign key attribute.
     *
     * @param    Clocking $l Clocking
     * @return ClockingType The current object (for fluent API support)
     */
    public function addClocking(Clocking $l)
    {
        if ($this->collClockings === null) {
            $this->initClockings();
            $this->collClockingsPartial = true;
        }
        if (!in_array($l, $this->collClockings->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddClocking($l);
        }

        return $this;
    }

    /**
     * @param	Clocking $clocking The clocking object to add.
     */
    protected function doAddClocking($clocking)
    {
        $this->collClockings[]= $clocking;
        $clocking->setClockingType($this);
    }

    /**
     * @param	Clocking $clocking The clocking object to remove.
     * @return ClockingType The current object (for fluent API support)
     */
    public function removeClocking($clocking)
    {
        if ($this->getClockings()->contains($clocking)) {
            $this->collClockings->remove($this->collClockings->search($clocking));
            if (null === $this->clockingsScheduledForDeletion) {
                $this->clockingsScheduledForDeletion = clone $this->collClockings;
                $this->clockingsScheduledForDeletion->clear();
            }
            $this->clockingsScheduledForDeletion[]= clone $clocking;
            $clocking->setClockingType(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this ClockingType is new, it will return
     * an empty collection; or if this ClockingType has previously
     * been saved, it will retrieve related Clockings from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in ClockingType.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|Clocking[] List of Clocking objects
     */
    public function getClockingsJoinUserRelatedByCreatorId($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = ClockingQuery::create(null, $criteria);
        $query->joinWith('UserRelatedByCreatorId', $join_behavior);

        return $this->getClockings($query, $con);
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this ClockingType is new, it will return
     * an empty collection; or if this ClockingType has previously
     * been saved, it will retrieve related Clockings from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in ClockingType.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|Clocking[] List of Clocking objects
     */
    public function getClockingsJoinUserRelatedByUserId($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = ClockingQuery::create(null, $criteria);
        $query->joinWith('UserRelatedByUserId', $join_behavior);

        return $this->getClockings($query, $con);
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id = null;
        $this->account_id = null;
        $this->identifier = null;
        $this->label = null;
        $this->whole_day = null;
        $this->future_grace_time = null;
        $this->past_grace_time = null;
        $this->approval_required = null;
        $this->alreadyInSave = false;
        $this->alreadyInValidation = false;
        $this->alreadyInClearAllReferencesDeep = false;
        $this->clearAllReferences();
        $this->applyDefaultValues();
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
            if ($this->collClockings) {
                foreach ($this->collClockings as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->aAccount instanceof Persistent) {
              $this->aAccount->clearAllReferences($deep);
            }

            $this->alreadyInClearAllReferencesDeep = false;
        } // if ($deep)

        if ($this->collClockings instanceof PropelCollection) {
            $this->collClockings->clearIterator();
        }
        $this->collClockings = null;
        $this->aAccount = null;
    }

    /**
     * return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(ClockingTypePeer::DEFAULT_STRING_FORMAT);
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
