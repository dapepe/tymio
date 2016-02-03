<?php


/**
 * Base class that represents a row from the 'transaction' table.
 *
 *
 *
 * @package    propel.generator.tymio.om
 */
abstract class BaseTransaction extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'TransactionPeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        TransactionPeer
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
     * The value for the creator_id field.
     * @var        int
     */
    protected $creator_id;

    /**
     * The value for the user_id field.
     * @var        int
     */
    protected $user_id;

    /**
     * The value for the deleted field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $deleted;

    /**
     * The value for the start field.
     * @var        string
     */
    protected $start;

    /**
     * The value for the end field.
     * @var        string
     */
    protected $end;

    /**
     * The value for the creationdate field.
     * @var        int
     */
    protected $creationdate;

    /**
     * The value for the comment field.
     * @var        string
     */
    protected $comment;

    /**
     * @var        User
     */
    protected $aUserRelatedByCreatorId;

    /**
     * @var        User
     */
    protected $aUserRelatedByUserId;

    /**
     * @var        PropelObjectCollection|Booking[] Collection to store aggregation of Booking objects.
     */
    protected $collBookings;
    protected $collBookingsPartial;

    /**
     * @var        PropelObjectCollection|TransactionClocking[] Collection to store aggregation of TransactionClocking objects.
     */
    protected $collTransactionClockings;
    protected $collTransactionClockingsPartial;

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
    protected $bookingsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $transactionClockingsScheduledForDeletion = null;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see        __construct()
     */
    public function applyDefaultValues()
    {
        $this->deleted = 0;
    }

    /**
     * Initializes internal state of BaseTransaction object.
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
     * Get the [creator_id] column value.
     *
     * @return int
     */
    public function getCreatorId()
    {
        return $this->creator_id;
    }

    /**
     * Get the [user_id] column value.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Get the [deleted] column value.
     *
     * @return int
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Get the [optionally formatted] temporal [start] column value.
     *
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw DateTime object will be returned.
     * @return mixed Formatted date/time value as string or DateTime object (if format is null), null if column is null, and 0 if column value is 0000-00-00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getStart($format = '%x')
    {
        if ($this->start === null) {
            return null;
        }

        if ($this->start === '0000-00-00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->start);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->start, true), $x);
        }

        if ($format === null) {
            // Because propel.useDateTimeClass is true, we return a DateTime object.
            return $dt;
        }

        if (strpos($format, '%') !== false) {
            return strftime($format, $dt->format('U'));
        }

        return $dt->format($format);

    }

    /**
     * Get the [optionally formatted] temporal [end] column value.
     *
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw DateTime object will be returned.
     * @return mixed Formatted date/time value as string or DateTime object (if format is null), null if column is null, and 0 if column value is 0000-00-00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getEnd($format = '%x')
    {
        if ($this->end === null) {
            return null;
        }

        if ($this->end === '0000-00-00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->end);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->end, true), $x);
        }

        if ($format === null) {
            // Because propel.useDateTimeClass is true, we return a DateTime object.
            return $dt;
        }

        if (strpos($format, '%') !== false) {
            return strftime($format, $dt->format('U'));
        }

        return $dt->format($format);

    }

    /**
     * Get the [creationdate] column value.
     *
     * @return int
     */
    public function getCreationdate()
    {
        return $this->creationdate;
    }

    /**
     * Get the [comment] column value.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set the value of [id] column.
     *
     * @param int $v new value
     * @return Transaction The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[] = TransactionPeer::ID;
        }


        return $this;
    } // setId()

    /**
     * Set the value of [creator_id] column.
     *
     * @param int $v new value
     * @return Transaction The current object (for fluent API support)
     */
    public function setCreatorId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->creator_id !== $v) {
            $this->creator_id = $v;
            $this->modifiedColumns[] = TransactionPeer::CREATOR_ID;
        }

        if ($this->aUserRelatedByCreatorId !== null && $this->aUserRelatedByCreatorId->getId() !== $v) {
            $this->aUserRelatedByCreatorId = null;
        }


        return $this;
    } // setCreatorId()

    /**
     * Set the value of [user_id] column.
     *
     * @param int $v new value
     * @return Transaction The current object (for fluent API support)
     */
    public function setUserId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->user_id !== $v) {
            $this->user_id = $v;
            $this->modifiedColumns[] = TransactionPeer::USER_ID;
        }

        if ($this->aUserRelatedByUserId !== null && $this->aUserRelatedByUserId->getId() !== $v) {
            $this->aUserRelatedByUserId = null;
        }


        return $this;
    } // setUserId()

    /**
     * Set the value of [deleted] column.
     *
     * @param int $v new value
     * @return Transaction The current object (for fluent API support)
     */
    public function setDeleted($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->deleted !== $v) {
            $this->deleted = $v;
            $this->modifiedColumns[] = TransactionPeer::DELETED;
        }


        return $this;
    } // setDeleted()

    /**
     * Sets the value of [start] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return Transaction The current object (for fluent API support)
     */
    public function setStart($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->start !== null || $dt !== null) {
            $currentDateAsString = ($this->start !== null && $tmpDt = new DateTime($this->start)) ? $tmpDt->format('Y-m-d') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->start = $newDateAsString;
                $this->modifiedColumns[] = TransactionPeer::START;
            }
        } // if either are not null


        return $this;
    } // setStart()

    /**
     * Sets the value of [end] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return Transaction The current object (for fluent API support)
     */
    public function setEnd($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->end !== null || $dt !== null) {
            $currentDateAsString = ($this->end !== null && $tmpDt = new DateTime($this->end)) ? $tmpDt->format('Y-m-d') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->end = $newDateAsString;
                $this->modifiedColumns[] = TransactionPeer::END;
            }
        } // if either are not null


        return $this;
    } // setEnd()

    /**
     * Set the value of [creationdate] column.
     *
     * @param int $v new value
     * @return Transaction The current object (for fluent API support)
     */
    public function setCreationdate($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->creationdate !== $v) {
            $this->creationdate = $v;
            $this->modifiedColumns[] = TransactionPeer::CREATIONDATE;
        }


        return $this;
    } // setCreationdate()

    /**
     * Set the value of [comment] column.
     *
     * @param string $v new value
     * @return Transaction The current object (for fluent API support)
     */
    public function setComment($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->comment !== $v) {
            $this->comment = $v;
            $this->modifiedColumns[] = TransactionPeer::COMMENT;
        }


        return $this;
    } // setComment()

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
            if ($this->deleted !== 0) {
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
            $this->creator_id = ($row[$startcol + 1] !== null) ? (int) $row[$startcol + 1] : null;
            $this->user_id = ($row[$startcol + 2] !== null) ? (int) $row[$startcol + 2] : null;
            $this->deleted = ($row[$startcol + 3] !== null) ? (int) $row[$startcol + 3] : null;
            $this->start = ($row[$startcol + 4] !== null) ? (string) $row[$startcol + 4] : null;
            $this->end = ($row[$startcol + 5] !== null) ? (string) $row[$startcol + 5] : null;
            $this->creationdate = ($row[$startcol + 6] !== null) ? (int) $row[$startcol + 6] : null;
            $this->comment = ($row[$startcol + 7] !== null) ? (string) $row[$startcol + 7] : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
            $this->postHydrate($row, $startcol, $rehydrate);
            return $startcol + 8; // 8 = TransactionPeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating Transaction object", $e);
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

        if ($this->aUserRelatedByCreatorId !== null && $this->creator_id !== $this->aUserRelatedByCreatorId->getId()) {
            $this->aUserRelatedByCreatorId = null;
        }
        if ($this->aUserRelatedByUserId !== null && $this->user_id !== $this->aUserRelatedByUserId->getId()) {
            $this->aUserRelatedByUserId = null;
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
            $con = Propel::getConnection(TransactionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = TransactionPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aUserRelatedByCreatorId = null;
            $this->aUserRelatedByUserId = null;
            $this->collBookings = null;

            $this->collTransactionClockings = null;

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
            $con = Propel::getConnection(TransactionPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = TransactionQuery::create()
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
            $con = Propel::getConnection(TransactionPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
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
                TransactionPeer::addInstanceToPool($this);
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

            if ($this->aUserRelatedByCreatorId !== null) {
                if ($this->aUserRelatedByCreatorId->isModified() || $this->aUserRelatedByCreatorId->isNew()) {
                    $affectedRows += $this->aUserRelatedByCreatorId->save($con);
                }
                $this->setUserRelatedByCreatorId($this->aUserRelatedByCreatorId);
            }

            if ($this->aUserRelatedByUserId !== null) {
                if ($this->aUserRelatedByUserId->isModified() || $this->aUserRelatedByUserId->isNew()) {
                    $affectedRows += $this->aUserRelatedByUserId->save($con);
                }
                $this->setUserRelatedByUserId($this->aUserRelatedByUserId);
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

            if ($this->bookingsScheduledForDeletion !== null) {
                if (!$this->bookingsScheduledForDeletion->isEmpty()) {
                    BookingQuery::create()
                        ->filterByPrimaryKeys($this->bookingsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->bookingsScheduledForDeletion = null;
                }
            }

            if ($this->collBookings !== null) {
                foreach ($this->collBookings as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->transactionClockingsScheduledForDeletion !== null) {
                if (!$this->transactionClockingsScheduledForDeletion->isEmpty()) {
                    TransactionClockingQuery::create()
                        ->filterByPrimaryKeys($this->transactionClockingsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->transactionClockingsScheduledForDeletion = null;
                }
            }

            if ($this->collTransactionClockings !== null) {
                foreach ($this->collTransactionClockings as $referrerFK) {
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

        $this->modifiedColumns[] = TransactionPeer::ID;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . TransactionPeer::ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(TransactionPeer::ID)) {
            $modifiedColumns[':p' . $index++]  = '`id`';
        }
        if ($this->isColumnModified(TransactionPeer::CREATOR_ID)) {
            $modifiedColumns[':p' . $index++]  = '`creator_id`';
        }
        if ($this->isColumnModified(TransactionPeer::USER_ID)) {
            $modifiedColumns[':p' . $index++]  = '`user_id`';
        }
        if ($this->isColumnModified(TransactionPeer::DELETED)) {
            $modifiedColumns[':p' . $index++]  = '`deleted`';
        }
        if ($this->isColumnModified(TransactionPeer::START)) {
            $modifiedColumns[':p' . $index++]  = '`start`';
        }
        if ($this->isColumnModified(TransactionPeer::END)) {
            $modifiedColumns[':p' . $index++]  = '`end`';
        }
        if ($this->isColumnModified(TransactionPeer::CREATIONDATE)) {
            $modifiedColumns[':p' . $index++]  = '`creationdate`';
        }
        if ($this->isColumnModified(TransactionPeer::COMMENT)) {
            $modifiedColumns[':p' . $index++]  = '`comment`';
        }

        $sql = sprintf(
            'INSERT INTO `transaction` (%s) VALUES (%s)',
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
                    case '`creator_id`':
                        $stmt->bindValue($identifier, $this->creator_id, PDO::PARAM_INT);
                        break;
                    case '`user_id`':
                        $stmt->bindValue($identifier, $this->user_id, PDO::PARAM_INT);
                        break;
                    case '`deleted`':
                        $stmt->bindValue($identifier, $this->deleted, PDO::PARAM_INT);
                        break;
                    case '`start`':
                        $stmt->bindValue($identifier, $this->start, PDO::PARAM_STR);
                        break;
                    case '`end`':
                        $stmt->bindValue($identifier, $this->end, PDO::PARAM_STR);
                        break;
                    case '`creationdate`':
                        $stmt->bindValue($identifier, $this->creationdate, PDO::PARAM_INT);
                        break;
                    case '`comment`':
                        $stmt->bindValue($identifier, $this->comment, PDO::PARAM_STR);
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

            if ($this->aUserRelatedByCreatorId !== null) {
                if (!$this->aUserRelatedByCreatorId->validate($columns)) {
                    $failureMap = array_merge($failureMap, $this->aUserRelatedByCreatorId->getValidationFailures());
                }
            }

            if ($this->aUserRelatedByUserId !== null) {
                if (!$this->aUserRelatedByUserId->validate($columns)) {
                    $failureMap = array_merge($failureMap, $this->aUserRelatedByUserId->getValidationFailures());
                }
            }


            if (($retval = TransactionPeer::doValidate($this, $columns)) !== true) {
                $failureMap = array_merge($failureMap, $retval);
            }


                if ($this->collBookings !== null) {
                    foreach ($this->collBookings as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collTransactionClockings !== null) {
                    foreach ($this->collTransactionClockings as $referrerFK) {
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
        $pos = TransactionPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
                return $this->getCreatorId();
                break;
            case 2:
                return $this->getUserId();
                break;
            case 3:
                return $this->getDeleted();
                break;
            case 4:
                return $this->getStart();
                break;
            case 5:
                return $this->getEnd();
                break;
            case 6:
                return $this->getCreationdate();
                break;
            case 7:
                return $this->getComment();
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
        if (isset($alreadyDumpedObjects['Transaction'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['Transaction'][$this->getPrimaryKey()] = true;
        $keys = TransactionPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getCreatorId(),
            $keys[2] => $this->getUserId(),
            $keys[3] => $this->getDeleted(),
            $keys[4] => $this->getStart(),
            $keys[5] => $this->getEnd(),
            $keys[6] => $this->getCreationdate(),
            $keys[7] => $this->getComment(),
        );
        if ($includeForeignObjects) {
            if (null !== $this->aUserRelatedByCreatorId) {
                $result['UserRelatedByCreatorId'] = $this->aUserRelatedByCreatorId->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aUserRelatedByUserId) {
                $result['UserRelatedByUserId'] = $this->aUserRelatedByUserId->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collBookings) {
                $result['Bookings'] = $this->collBookings->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collTransactionClockings) {
                $result['TransactionClockings'] = $this->collTransactionClockings->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = TransactionPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

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
                $this->setCreatorId($value);
                break;
            case 2:
                $this->setUserId($value);
                break;
            case 3:
                $this->setDeleted($value);
                break;
            case 4:
                $this->setStart($value);
                break;
            case 5:
                $this->setEnd($value);
                break;
            case 6:
                $this->setCreationdate($value);
                break;
            case 7:
                $this->setComment($value);
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
        $keys = TransactionPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setCreatorId($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setUserId($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setDeleted($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setStart($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setEnd($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setCreationdate($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setComment($arr[$keys[7]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(TransactionPeer::DATABASE_NAME);

        if ($this->isColumnModified(TransactionPeer::ID)) $criteria->add(TransactionPeer::ID, $this->id);
        if ($this->isColumnModified(TransactionPeer::CREATOR_ID)) $criteria->add(TransactionPeer::CREATOR_ID, $this->creator_id);
        if ($this->isColumnModified(TransactionPeer::USER_ID)) $criteria->add(TransactionPeer::USER_ID, $this->user_id);
        if ($this->isColumnModified(TransactionPeer::DELETED)) $criteria->add(TransactionPeer::DELETED, $this->deleted);
        if ($this->isColumnModified(TransactionPeer::START)) $criteria->add(TransactionPeer::START, $this->start);
        if ($this->isColumnModified(TransactionPeer::END)) $criteria->add(TransactionPeer::END, $this->end);
        if ($this->isColumnModified(TransactionPeer::CREATIONDATE)) $criteria->add(TransactionPeer::CREATIONDATE, $this->creationdate);
        if ($this->isColumnModified(TransactionPeer::COMMENT)) $criteria->add(TransactionPeer::COMMENT, $this->comment);

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
        $criteria = new Criteria(TransactionPeer::DATABASE_NAME);
        $criteria->add(TransactionPeer::ID, $this->id);

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
     * @param object $copyObj An object of Transaction (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setCreatorId($this->getCreatorId());
        $copyObj->setUserId($this->getUserId());
        $copyObj->setDeleted($this->getDeleted());
        $copyObj->setStart($this->getStart());
        $copyObj->setEnd($this->getEnd());
        $copyObj->setCreationdate($this->getCreationdate());
        $copyObj->setComment($this->getComment());

        if ($deepCopy && !$this->startCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);
            // store object hash to prevent cycle
            $this->startCopy = true;

            foreach ($this->getBookings() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addBooking($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getTransactionClockings() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addTransactionClocking($relObj->copy($deepCopy));
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
     * @return Transaction Clone of current object.
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
     * @return TransactionPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new TransactionPeer();
        }

        return self::$peer;
    }

    /**
     * Declares an association between this object and a User object.
     *
     * @param             User $v
     * @return Transaction The current object (for fluent API support)
     * @throws PropelException
     */
    public function setUserRelatedByCreatorId(User $v = null)
    {
        if ($v === null) {
            $this->setCreatorId(NULL);
        } else {
            $this->setCreatorId($v->getId());
        }

        $this->aUserRelatedByCreatorId = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the User object, it will not be re-added.
        if ($v !== null) {
            $v->addTransactionRelatedByCreatorId($this);
        }


        return $this;
    }


    /**
     * Get the associated User object
     *
     * @param PropelPDO $con Optional Connection object.
     * @param $doQuery Executes a query to get the object if required
     * @return User The associated User object.
     * @throws PropelException
     */
    public function getUserRelatedByCreatorId(PropelPDO $con = null, $doQuery = true)
    {
        if ($this->aUserRelatedByCreatorId === null && ($this->creator_id !== null) && $doQuery) {
            $this->aUserRelatedByCreatorId = UserQuery::create()->findPk($this->creator_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aUserRelatedByCreatorId->addTransactionsRelatedByCreatorId($this);
             */
        }

        return $this->aUserRelatedByCreatorId;
    }

    /**
     * Declares an association between this object and a User object.
     *
     * @param             User $v
     * @return Transaction The current object (for fluent API support)
     * @throws PropelException
     */
    public function setUserRelatedByUserId(User $v = null)
    {
        if ($v === null) {
            $this->setUserId(NULL);
        } else {
            $this->setUserId($v->getId());
        }

        $this->aUserRelatedByUserId = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the User object, it will not be re-added.
        if ($v !== null) {
            $v->addTransactionRelatedByUserId($this);
        }


        return $this;
    }


    /**
     * Get the associated User object
     *
     * @param PropelPDO $con Optional Connection object.
     * @param $doQuery Executes a query to get the object if required
     * @return User The associated User object.
     * @throws PropelException
     */
    public function getUserRelatedByUserId(PropelPDO $con = null, $doQuery = true)
    {
        if ($this->aUserRelatedByUserId === null && ($this->user_id !== null) && $doQuery) {
            $this->aUserRelatedByUserId = UserQuery::create()->findPk($this->user_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aUserRelatedByUserId->addTransactionsRelatedByUserId($this);
             */
        }

        return $this->aUserRelatedByUserId;
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
        if ('Booking' == $relationName) {
            $this->initBookings();
        }
        if ('TransactionClocking' == $relationName) {
            $this->initTransactionClockings();
        }
    }

    /**
     * Clears out the collBookings collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return Transaction The current object (for fluent API support)
     * @see        addBookings()
     */
    public function clearBookings()
    {
        $this->collBookings = null; // important to set this to null since that means it is uninitialized
        $this->collBookingsPartial = null;

        return $this;
    }

    /**
     * reset is the collBookings collection loaded partially
     *
     * @return void
     */
    public function resetPartialBookings($v = true)
    {
        $this->collBookingsPartial = $v;
    }

    /**
     * Initializes the collBookings collection.
     *
     * By default this just sets the collBookings collection to an empty array (like clearcollBookings());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initBookings($overrideExisting = true)
    {
        if (null !== $this->collBookings && !$overrideExisting) {
            return;
        }
        $this->collBookings = new PropelObjectCollection();
        $this->collBookings->setModel('Booking');
    }

    /**
     * Gets an array of Booking objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this Transaction is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|Booking[] List of Booking objects
     * @throws PropelException
     */
    public function getBookings($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collBookingsPartial && !$this->isNew();
        if (null === $this->collBookings || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collBookings) {
                // return empty collection
                $this->initBookings();
            } else {
                $collBookings = BookingQuery::create(null, $criteria)
                    ->filterByTransaction($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collBookingsPartial && count($collBookings)) {
                      $this->initBookings(false);

                      foreach($collBookings as $obj) {
                        if (false == $this->collBookings->contains($obj)) {
                          $this->collBookings->append($obj);
                        }
                      }

                      $this->collBookingsPartial = true;
                    }

                    $collBookings->getInternalIterator()->rewind();
                    return $collBookings;
                }

                if($partial && $this->collBookings) {
                    foreach($this->collBookings as $obj) {
                        if($obj->isNew()) {
                            $collBookings[] = $obj;
                        }
                    }
                }

                $this->collBookings = $collBookings;
                $this->collBookingsPartial = false;
            }
        }

        return $this->collBookings;
    }

    /**
     * Sets a collection of Booking objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $bookings A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return Transaction The current object (for fluent API support)
     */
    public function setBookings(PropelCollection $bookings, PropelPDO $con = null)
    {
        $bookingsToDelete = $this->getBookings(new Criteria(), $con)->diff($bookings);

        $this->bookingsScheduledForDeletion = unserialize(serialize($bookingsToDelete));

        foreach ($bookingsToDelete as $bookingRemoved) {
            $bookingRemoved->setTransaction(null);
        }

        $this->collBookings = null;
        foreach ($bookings as $booking) {
            $this->addBooking($booking);
        }

        $this->collBookings = $bookings;
        $this->collBookingsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Booking objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related Booking objects.
     * @throws PropelException
     */
    public function countBookings(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collBookingsPartial && !$this->isNew();
        if (null === $this->collBookings || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collBookings) {
                return 0;
            }

            if($partial && !$criteria) {
                return count($this->getBookings());
            }
            $query = BookingQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByTransaction($this)
                ->count($con);
        }

        return count($this->collBookings);
    }

    /**
     * Method called to associate a Booking object to this object
     * through the Booking foreign key attribute.
     *
     * @param    Booking $l Booking
     * @return Transaction The current object (for fluent API support)
     */
    public function addBooking(Booking $l)
    {
        if ($this->collBookings === null) {
            $this->initBookings();
            $this->collBookingsPartial = true;
        }
        if (!in_array($l, $this->collBookings->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddBooking($l);
        }

        return $this;
    }

    /**
     * @param	Booking $booking The booking object to add.
     */
    protected function doAddBooking($booking)
    {
        $this->collBookings[]= $booking;
        $booking->setTransaction($this);
    }

    /**
     * @param	Booking $booking The booking object to remove.
     * @return Transaction The current object (for fluent API support)
     */
    public function removeBooking($booking)
    {
        if ($this->getBookings()->contains($booking)) {
            $this->collBookings->remove($this->collBookings->search($booking));
            if (null === $this->bookingsScheduledForDeletion) {
                $this->bookingsScheduledForDeletion = clone $this->collBookings;
                $this->bookingsScheduledForDeletion->clear();
            }
            $this->bookingsScheduledForDeletion[]= clone $booking;
            $booking->setTransaction(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Transaction is new, it will return
     * an empty collection; or if this Transaction has previously
     * been saved, it will retrieve related Bookings from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Transaction.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|Booking[] List of Booking objects
     */
    public function getBookingsJoinBookingType($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = BookingQuery::create(null, $criteria);
        $query->joinWith('BookingType', $join_behavior);

        return $this->getBookings($query, $con);
    }

    /**
     * Clears out the collTransactionClockings collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return Transaction The current object (for fluent API support)
     * @see        addTransactionClockings()
     */
    public function clearTransactionClockings()
    {
        $this->collTransactionClockings = null; // important to set this to null since that means it is uninitialized
        $this->collTransactionClockingsPartial = null;

        return $this;
    }

    /**
     * reset is the collTransactionClockings collection loaded partially
     *
     * @return void
     */
    public function resetPartialTransactionClockings($v = true)
    {
        $this->collTransactionClockingsPartial = $v;
    }

    /**
     * Initializes the collTransactionClockings collection.
     *
     * By default this just sets the collTransactionClockings collection to an empty array (like clearcollTransactionClockings());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initTransactionClockings($overrideExisting = true)
    {
        if (null !== $this->collTransactionClockings && !$overrideExisting) {
            return;
        }
        $this->collTransactionClockings = new PropelObjectCollection();
        $this->collTransactionClockings->setModel('TransactionClocking');
    }

    /**
     * Gets an array of TransactionClocking objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this Transaction is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|TransactionClocking[] List of TransactionClocking objects
     * @throws PropelException
     */
    public function getTransactionClockings($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collTransactionClockingsPartial && !$this->isNew();
        if (null === $this->collTransactionClockings || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collTransactionClockings) {
                // return empty collection
                $this->initTransactionClockings();
            } else {
                $collTransactionClockings = TransactionClockingQuery::create(null, $criteria)
                    ->filterByTransaction($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collTransactionClockingsPartial && count($collTransactionClockings)) {
                      $this->initTransactionClockings(false);

                      foreach($collTransactionClockings as $obj) {
                        if (false == $this->collTransactionClockings->contains($obj)) {
                          $this->collTransactionClockings->append($obj);
                        }
                      }

                      $this->collTransactionClockingsPartial = true;
                    }

                    $collTransactionClockings->getInternalIterator()->rewind();
                    return $collTransactionClockings;
                }

                if($partial && $this->collTransactionClockings) {
                    foreach($this->collTransactionClockings as $obj) {
                        if($obj->isNew()) {
                            $collTransactionClockings[] = $obj;
                        }
                    }
                }

                $this->collTransactionClockings = $collTransactionClockings;
                $this->collTransactionClockingsPartial = false;
            }
        }

        return $this->collTransactionClockings;
    }

    /**
     * Sets a collection of TransactionClocking objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $transactionClockings A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return Transaction The current object (for fluent API support)
     */
    public function setTransactionClockings(PropelCollection $transactionClockings, PropelPDO $con = null)
    {
        $transactionClockingsToDelete = $this->getTransactionClockings(new Criteria(), $con)->diff($transactionClockings);

        $this->transactionClockingsScheduledForDeletion = unserialize(serialize($transactionClockingsToDelete));

        foreach ($transactionClockingsToDelete as $transactionClockingRemoved) {
            $transactionClockingRemoved->setTransaction(null);
        }

        $this->collTransactionClockings = null;
        foreach ($transactionClockings as $transactionClocking) {
            $this->addTransactionClocking($transactionClocking);
        }

        $this->collTransactionClockings = $transactionClockings;
        $this->collTransactionClockingsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related TransactionClocking objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related TransactionClocking objects.
     * @throws PropelException
     */
    public function countTransactionClockings(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collTransactionClockingsPartial && !$this->isNew();
        if (null === $this->collTransactionClockings || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collTransactionClockings) {
                return 0;
            }

            if($partial && !$criteria) {
                return count($this->getTransactionClockings());
            }
            $query = TransactionClockingQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByTransaction($this)
                ->count($con);
        }

        return count($this->collTransactionClockings);
    }

    /**
     * Method called to associate a TransactionClocking object to this object
     * through the TransactionClocking foreign key attribute.
     *
     * @param    TransactionClocking $l TransactionClocking
     * @return Transaction The current object (for fluent API support)
     */
    public function addTransactionClocking(TransactionClocking $l)
    {
        if ($this->collTransactionClockings === null) {
            $this->initTransactionClockings();
            $this->collTransactionClockingsPartial = true;
        }
        if (!in_array($l, $this->collTransactionClockings->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddTransactionClocking($l);
        }

        return $this;
    }

    /**
     * @param	TransactionClocking $transactionClocking The transactionClocking object to add.
     */
    protected function doAddTransactionClocking($transactionClocking)
    {
        $this->collTransactionClockings[]= $transactionClocking;
        $transactionClocking->setTransaction($this);
    }

    /**
     * @param	TransactionClocking $transactionClocking The transactionClocking object to remove.
     * @return Transaction The current object (for fluent API support)
     */
    public function removeTransactionClocking($transactionClocking)
    {
        if ($this->getTransactionClockings()->contains($transactionClocking)) {
            $this->collTransactionClockings->remove($this->collTransactionClockings->search($transactionClocking));
            if (null === $this->transactionClockingsScheduledForDeletion) {
                $this->transactionClockingsScheduledForDeletion = clone $this->collTransactionClockings;
                $this->transactionClockingsScheduledForDeletion->clear();
            }
            $this->transactionClockingsScheduledForDeletion[]= clone $transactionClocking;
            $transactionClocking->setTransaction(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Transaction is new, it will return
     * an empty collection; or if this Transaction has previously
     * been saved, it will retrieve related TransactionClockings from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Transaction.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|TransactionClocking[] List of TransactionClocking objects
     */
    public function getTransactionClockingsJoinClocking($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = TransactionClockingQuery::create(null, $criteria);
        $query->joinWith('Clocking', $join_behavior);

        return $this->getTransactionClockings($query, $con);
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id = null;
        $this->creator_id = null;
        $this->user_id = null;
        $this->deleted = null;
        $this->start = null;
        $this->end = null;
        $this->creationdate = null;
        $this->comment = null;
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
            if ($this->collBookings) {
                foreach ($this->collBookings as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collTransactionClockings) {
                foreach ($this->collTransactionClockings as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->aUserRelatedByCreatorId instanceof Persistent) {
              $this->aUserRelatedByCreatorId->clearAllReferences($deep);
            }
            if ($this->aUserRelatedByUserId instanceof Persistent) {
              $this->aUserRelatedByUserId->clearAllReferences($deep);
            }

            $this->alreadyInClearAllReferencesDeep = false;
        } // if ($deep)

        if ($this->collBookings instanceof PropelCollection) {
            $this->collBookings->clearIterator();
        }
        $this->collBookings = null;
        if ($this->collTransactionClockings instanceof PropelCollection) {
            $this->collTransactionClockings->clearIterator();
        }
        $this->collTransactionClockings = null;
        $this->aUserRelatedByCreatorId = null;
        $this->aUserRelatedByUserId = null;
    }

    /**
     * return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(TransactionPeer::DEFAULT_STRING_FORMAT);
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
