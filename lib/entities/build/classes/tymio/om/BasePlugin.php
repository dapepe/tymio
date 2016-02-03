<?php


/**
 * Base class that represents a row from the 'plugin' table.
 *
 *
 *
 * @package    propel.generator.tymio.om
 */
abstract class BasePlugin extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'PluginPeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        PluginPeer
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
     * The value for the entity field.
     * @var        string
     */
    protected $entity;

    /**
     * The value for the event field.
     * @var        string
     */
    protected $event;

    /**
     * The value for the priority field.
     * @var        int
     */
    protected $priority;

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
     * The value for the code field.
     * @var        string
     */
    protected $code;

    /**
     * The value for the active field.
     * Note: this column has a database default value of: 1
     * @var        int
     */
    protected $active;

    /**
     * The value for the interval field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $interval;

    /**
     * The value for the start field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $start;

    /**
     * The value for the last_execution_time field.
     * Note: this column has a database default value of: '0'
     * @var        string
     */
    protected $last_execution_time;

    /**
     * @var        Account
     */
    protected $aAccount;

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
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see        __construct()
     */
    public function applyDefaultValues()
    {
        $this->active = 1;
        $this->interval = 0;
        $this->start = 0;
        $this->last_execution_time = '0';
    }

    /**
     * Initializes internal state of BasePlugin object.
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
     * Get the [entity] column value.
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Get the [event] column value.
     *
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Get the [priority] column value.
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
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
     * Get the [code] column value.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get the [active] column value.
     *
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Get the [interval] column value.
     *
     * @return int
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * Get the [start] column value.
     *
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Get the [last_execution_time] column value.
     *
     * @return string
     */
    public function getLastExecutionTime()
    {
        return $this->last_execution_time;
    }

    /**
     * Set the value of [id] column.
     *
     * @param int $v new value
     * @return Plugin The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[] = PluginPeer::ID;
        }


        return $this;
    } // setId()

    /**
     * Set the value of [account_id] column.
     *
     * @param int $v new value
     * @return Plugin The current object (for fluent API support)
     */
    public function setAccountId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->account_id !== $v) {
            $this->account_id = $v;
            $this->modifiedColumns[] = PluginPeer::ACCOUNT_ID;
        }

        if ($this->aAccount !== null && $this->aAccount->getId() !== $v) {
            $this->aAccount = null;
        }


        return $this;
    } // setAccountId()

    /**
     * Set the value of [entity] column.
     *
     * @param string $v new value
     * @return Plugin The current object (for fluent API support)
     */
    public function setEntity($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->entity !== $v) {
            $this->entity = $v;
            $this->modifiedColumns[] = PluginPeer::ENTITY;
        }


        return $this;
    } // setEntity()

    /**
     * Set the value of [event] column.
     *
     * @param string $v new value
     * @return Plugin The current object (for fluent API support)
     */
    public function setEvent($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->event !== $v) {
            $this->event = $v;
            $this->modifiedColumns[] = PluginPeer::EVENT;
        }


        return $this;
    } // setEvent()

    /**
     * Set the value of [priority] column.
     *
     * @param int $v new value
     * @return Plugin The current object (for fluent API support)
     */
    public function setPriority($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->priority !== $v) {
            $this->priority = $v;
            $this->modifiedColumns[] = PluginPeer::PRIORITY;
        }


        return $this;
    } // setPriority()

    /**
     * Set the value of [identifier] column.
     *
     * @param string $v new value
     * @return Plugin The current object (for fluent API support)
     */
    public function setIdentifier($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->identifier !== $v) {
            $this->identifier = $v;
            $this->modifiedColumns[] = PluginPeer::IDENTIFIER;
        }


        return $this;
    } // setIdentifier()

    /**
     * Set the value of [name] column.
     *
     * @param string $v new value
     * @return Plugin The current object (for fluent API support)
     */
    public function setName($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->name !== $v) {
            $this->name = $v;
            $this->modifiedColumns[] = PluginPeer::NAME;
        }


        return $this;
    } // setName()

    /**
     * Set the value of [code] column.
     *
     * @param string $v new value
     * @return Plugin The current object (for fluent API support)
     */
    public function setCode($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->code !== $v) {
            $this->code = $v;
            $this->modifiedColumns[] = PluginPeer::CODE;
        }


        return $this;
    } // setCode()

    /**
     * Set the value of [active] column.
     *
     * @param int $v new value
     * @return Plugin The current object (for fluent API support)
     */
    public function setActive($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->active !== $v) {
            $this->active = $v;
            $this->modifiedColumns[] = PluginPeer::ACTIVE;
        }


        return $this;
    } // setActive()

    /**
     * Set the value of [interval] column.
     *
     * @param int $v new value
     * @return Plugin The current object (for fluent API support)
     */
    public function setInterval($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->interval !== $v) {
            $this->interval = $v;
            $this->modifiedColumns[] = PluginPeer::INTERVAL;
        }


        return $this;
    } // setInterval()

    /**
     * Set the value of [start] column.
     *
     * @param int $v new value
     * @return Plugin The current object (for fluent API support)
     */
    public function setStart($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->start !== $v) {
            $this->start = $v;
            $this->modifiedColumns[] = PluginPeer::START;
        }


        return $this;
    } // setStart()

    /**
     * Set the value of [last_execution_time] column.
     *
     * @param string $v new value
     * @return Plugin The current object (for fluent API support)
     */
    public function setLastExecutionTime($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->last_execution_time !== $v) {
            $this->last_execution_time = $v;
            $this->modifiedColumns[] = PluginPeer::LAST_EXECUTION_TIME;
        }


        return $this;
    } // setLastExecutionTime()

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
            if ($this->active !== 1) {
                return false;
            }

            if ($this->interval !== 0) {
                return false;
            }

            if ($this->start !== 0) {
                return false;
            }

            if ($this->last_execution_time !== '0') {
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
            $this->entity = ($row[$startcol + 2] !== null) ? (string) $row[$startcol + 2] : null;
            $this->event = ($row[$startcol + 3] !== null) ? (string) $row[$startcol + 3] : null;
            $this->priority = ($row[$startcol + 4] !== null) ? (int) $row[$startcol + 4] : null;
            $this->identifier = ($row[$startcol + 5] !== null) ? (string) $row[$startcol + 5] : null;
            $this->name = ($row[$startcol + 6] !== null) ? (string) $row[$startcol + 6] : null;
            $this->code = ($row[$startcol + 7] !== null) ? (string) $row[$startcol + 7] : null;
            $this->active = ($row[$startcol + 8] !== null) ? (int) $row[$startcol + 8] : null;
            $this->interval = ($row[$startcol + 9] !== null) ? (int) $row[$startcol + 9] : null;
            $this->start = ($row[$startcol + 10] !== null) ? (int) $row[$startcol + 10] : null;
            $this->last_execution_time = ($row[$startcol + 11] !== null) ? (string) $row[$startcol + 11] : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
            $this->postHydrate($row, $startcol, $rehydrate);
            return $startcol + 12; // 12 = PluginPeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating Plugin object", $e);
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
            $con = Propel::getConnection(PluginPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = PluginPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aAccount = null;
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
            $con = Propel::getConnection(PluginPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = PluginQuery::create()
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
            $con = Propel::getConnection(PluginPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
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
                PluginPeer::addInstanceToPool($this);
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

        $this->modifiedColumns[] = PluginPeer::ID;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . PluginPeer::ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(PluginPeer::ID)) {
            $modifiedColumns[':p' . $index++]  = '`id`';
        }
        if ($this->isColumnModified(PluginPeer::ACCOUNT_ID)) {
            $modifiedColumns[':p' . $index++]  = '`account_id`';
        }
        if ($this->isColumnModified(PluginPeer::ENTITY)) {
            $modifiedColumns[':p' . $index++]  = '`entity`';
        }
        if ($this->isColumnModified(PluginPeer::EVENT)) {
            $modifiedColumns[':p' . $index++]  = '`event`';
        }
        if ($this->isColumnModified(PluginPeer::PRIORITY)) {
            $modifiedColumns[':p' . $index++]  = '`priority`';
        }
        if ($this->isColumnModified(PluginPeer::IDENTIFIER)) {
            $modifiedColumns[':p' . $index++]  = '`identifier`';
        }
        if ($this->isColumnModified(PluginPeer::NAME)) {
            $modifiedColumns[':p' . $index++]  = '`name`';
        }
        if ($this->isColumnModified(PluginPeer::CODE)) {
            $modifiedColumns[':p' . $index++]  = '`code`';
        }
        if ($this->isColumnModified(PluginPeer::ACTIVE)) {
            $modifiedColumns[':p' . $index++]  = '`active`';
        }
        if ($this->isColumnModified(PluginPeer::INTERVAL)) {
            $modifiedColumns[':p' . $index++]  = '`interval`';
        }
        if ($this->isColumnModified(PluginPeer::START)) {
            $modifiedColumns[':p' . $index++]  = '`start`';
        }
        if ($this->isColumnModified(PluginPeer::LAST_EXECUTION_TIME)) {
            $modifiedColumns[':p' . $index++]  = '`last_execution_time`';
        }

        $sql = sprintf(
            'INSERT INTO `plugin` (%s) VALUES (%s)',
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
                    case '`entity`':
                        $stmt->bindValue($identifier, $this->entity, PDO::PARAM_STR);
                        break;
                    case '`event`':
                        $stmt->bindValue($identifier, $this->event, PDO::PARAM_STR);
                        break;
                    case '`priority`':
                        $stmt->bindValue($identifier, $this->priority, PDO::PARAM_INT);
                        break;
                    case '`identifier`':
                        $stmt->bindValue($identifier, $this->identifier, PDO::PARAM_STR);
                        break;
                    case '`name`':
                        $stmt->bindValue($identifier, $this->name, PDO::PARAM_STR);
                        break;
                    case '`code`':
                        $stmt->bindValue($identifier, $this->code, PDO::PARAM_STR);
                        break;
                    case '`active`':
                        $stmt->bindValue($identifier, $this->active, PDO::PARAM_INT);
                        break;
                    case '`interval`':
                        $stmt->bindValue($identifier, $this->interval, PDO::PARAM_INT);
                        break;
                    case '`start`':
                        $stmt->bindValue($identifier, $this->start, PDO::PARAM_INT);
                        break;
                    case '`last_execution_time`':
                        $stmt->bindValue($identifier, $this->last_execution_time, PDO::PARAM_STR);
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


            if (($retval = PluginPeer::doValidate($this, $columns)) !== true) {
                $failureMap = array_merge($failureMap, $retval);
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
        $pos = PluginPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
                return $this->getEntity();
                break;
            case 3:
                return $this->getEvent();
                break;
            case 4:
                return $this->getPriority();
                break;
            case 5:
                return $this->getIdentifier();
                break;
            case 6:
                return $this->getName();
                break;
            case 7:
                return $this->getCode();
                break;
            case 8:
                return $this->getActive();
                break;
            case 9:
                return $this->getInterval();
                break;
            case 10:
                return $this->getStart();
                break;
            case 11:
                return $this->getLastExecutionTime();
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
        if (isset($alreadyDumpedObjects['Plugin'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['Plugin'][$this->getPrimaryKey()] = true;
        $keys = PluginPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getAccountId(),
            $keys[2] => $this->getEntity(),
            $keys[3] => $this->getEvent(),
            $keys[4] => $this->getPriority(),
            $keys[5] => $this->getIdentifier(),
            $keys[6] => $this->getName(),
            $keys[7] => $this->getCode(),
            $keys[8] => $this->getActive(),
            $keys[9] => $this->getInterval(),
            $keys[10] => $this->getStart(),
            $keys[11] => $this->getLastExecutionTime(),
        );
        if ($includeForeignObjects) {
            if (null !== $this->aAccount) {
                $result['Account'] = $this->aAccount->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
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
        $pos = PluginPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

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
                $this->setEntity($value);
                break;
            case 3:
                $this->setEvent($value);
                break;
            case 4:
                $this->setPriority($value);
                break;
            case 5:
                $this->setIdentifier($value);
                break;
            case 6:
                $this->setName($value);
                break;
            case 7:
                $this->setCode($value);
                break;
            case 8:
                $this->setActive($value);
                break;
            case 9:
                $this->setInterval($value);
                break;
            case 10:
                $this->setStart($value);
                break;
            case 11:
                $this->setLastExecutionTime($value);
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
        $keys = PluginPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setAccountId($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setEntity($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setEvent($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setPriority($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setIdentifier($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setName($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setCode($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setActive($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setInterval($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setStart($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setLastExecutionTime($arr[$keys[11]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(PluginPeer::DATABASE_NAME);

        if ($this->isColumnModified(PluginPeer::ID)) $criteria->add(PluginPeer::ID, $this->id);
        if ($this->isColumnModified(PluginPeer::ACCOUNT_ID)) $criteria->add(PluginPeer::ACCOUNT_ID, $this->account_id);
        if ($this->isColumnModified(PluginPeer::ENTITY)) $criteria->add(PluginPeer::ENTITY, $this->entity);
        if ($this->isColumnModified(PluginPeer::EVENT)) $criteria->add(PluginPeer::EVENT, $this->event);
        if ($this->isColumnModified(PluginPeer::PRIORITY)) $criteria->add(PluginPeer::PRIORITY, $this->priority);
        if ($this->isColumnModified(PluginPeer::IDENTIFIER)) $criteria->add(PluginPeer::IDENTIFIER, $this->identifier);
        if ($this->isColumnModified(PluginPeer::NAME)) $criteria->add(PluginPeer::NAME, $this->name);
        if ($this->isColumnModified(PluginPeer::CODE)) $criteria->add(PluginPeer::CODE, $this->code);
        if ($this->isColumnModified(PluginPeer::ACTIVE)) $criteria->add(PluginPeer::ACTIVE, $this->active);
        if ($this->isColumnModified(PluginPeer::INTERVAL)) $criteria->add(PluginPeer::INTERVAL, $this->interval);
        if ($this->isColumnModified(PluginPeer::START)) $criteria->add(PluginPeer::START, $this->start);
        if ($this->isColumnModified(PluginPeer::LAST_EXECUTION_TIME)) $criteria->add(PluginPeer::LAST_EXECUTION_TIME, $this->last_execution_time);

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
        $criteria = new Criteria(PluginPeer::DATABASE_NAME);
        $criteria->add(PluginPeer::ID, $this->id);

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
     * @param object $copyObj An object of Plugin (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setAccountId($this->getAccountId());
        $copyObj->setEntity($this->getEntity());
        $copyObj->setEvent($this->getEvent());
        $copyObj->setPriority($this->getPriority());
        $copyObj->setIdentifier($this->getIdentifier());
        $copyObj->setName($this->getName());
        $copyObj->setCode($this->getCode());
        $copyObj->setActive($this->getActive());
        $copyObj->setInterval($this->getInterval());
        $copyObj->setStart($this->getStart());
        $copyObj->setLastExecutionTime($this->getLastExecutionTime());

        if ($deepCopy && !$this->startCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);
            // store object hash to prevent cycle
            $this->startCopy = true;

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
     * @return Plugin Clone of current object.
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
     * @return PluginPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new PluginPeer();
        }

        return self::$peer;
    }

    /**
     * Declares an association between this object and a Account object.
     *
     * @param             Account $v
     * @return Plugin The current object (for fluent API support)
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
            $v->addPlugin($this);
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
                $this->aAccount->addPlugins($this);
             */
        }

        return $this->aAccount;
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id = null;
        $this->account_id = null;
        $this->entity = null;
        $this->event = null;
        $this->priority = null;
        $this->identifier = null;
        $this->name = null;
        $this->code = null;
        $this->active = null;
        $this->interval = null;
        $this->start = null;
        $this->last_execution_time = null;
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
            if ($this->aAccount instanceof Persistent) {
              $this->aAccount->clearAllReferences($deep);
            }

            $this->alreadyInClearAllReferencesDeep = false;
        } // if ($deep)

        $this->aAccount = null;
    }

    /**
     * return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(PluginPeer::DEFAULT_STRING_FORMAT);
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
