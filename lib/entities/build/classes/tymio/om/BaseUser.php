<?php


/**
 * Base class that represents a row from the 'user' table.
 *
 *
 *
 * @package    propel.generator.tymio.om
 */
abstract class BaseUser extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'UserPeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        UserPeer
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
     * The value for the domain_id field.
     * @var        int
     */
    protected $domain_id;

    /**
     * The value for the deleted field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $deleted;

    /**
     * The value for the name field.
     * @var        string
     */
    protected $name;

    /**
     * The value for the firstname field.
     * @var        string
     */
    protected $firstname;

    /**
     * The value for the lastname field.
     * @var        string
     */
    protected $lastname;

    /**
     * The value for the phone field.
     * @var        string
     */
    protected $phone;

    /**
     * The value for the manager_of field.
     * @var        int
     */
    protected $manager_of;

    /**
     * The value for the is_admin field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $is_admin;

    /**
     * The value for the email field.
     * @var        string
     */
    protected $email;

    /**
     * The value for the password_hash field.
     * @var        string
     */
    protected $password_hash;

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
     * @var        Domain
     */
    protected $aDomain;

    /**
     * @var        PropelObjectCollection|Clocking[] Collection to store aggregation of Clocking objects.
     */
    protected $collClockingsRelatedByCreatorId;
    protected $collClockingsRelatedByCreatorIdPartial;

    /**
     * @var        PropelObjectCollection|Clocking[] Collection to store aggregation of Clocking objects.
     */
    protected $collClockingsRelatedByUserId;
    protected $collClockingsRelatedByUserIdPartial;

    /**
     * @var        PropelObjectCollection|PropertyValue[] Collection to store aggregation of PropertyValue objects.
     */
    protected $collPropertyValues;
    protected $collPropertyValuesPartial;

    /**
     * @var        PropelObjectCollection|SystemLog[] Collection to store aggregation of SystemLog objects.
     */
    protected $collSystemLogs;
    protected $collSystemLogsPartial;

    /**
     * @var        PropelObjectCollection|Transaction[] Collection to store aggregation of Transaction objects.
     */
    protected $collTransactionsRelatedByCreatorId;
    protected $collTransactionsRelatedByCreatorIdPartial;

    /**
     * @var        PropelObjectCollection|Transaction[] Collection to store aggregation of Transaction objects.
     */
    protected $collTransactionsRelatedByUserId;
    protected $collTransactionsRelatedByUserIdPartial;

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
    protected $clockingsRelatedByCreatorIdScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $clockingsRelatedByUserIdScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $propertyValuesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $systemLogsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $transactionsRelatedByCreatorIdScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $transactionsRelatedByUserIdScheduledForDeletion = null;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see        __construct()
     */
    public function applyDefaultValues()
    {
        $this->deleted = 0;
        $this->is_admin = 0;
    }

    /**
     * Initializes internal state of BaseUser object.
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
     * Get the [domain_id] column value.
     *
     * @return int
     */
    public function getDomainId()
    {
        return $this->domain_id;
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
     * Get the [name] column value.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the [firstname] column value.
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Get the [lastname] column value.
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Get the [phone] column value.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Get the [manager_of] column value.
     *
     * @return int
     */
    public function getManagerOf()
    {
        return $this->manager_of;
    }

    /**
     * Get the [is_admin] column value.
     *
     * @return int
     */
    public function getIsAdmin()
    {
        return $this->is_admin;
    }

    /**
     * Get the [email] column value.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get the [password_hash] column value.
     *
     * @return string
     */
    public function getPasswordHash()
    {
        return $this->password_hash;
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
     * @return User The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[] = UserPeer::ID;
        }


        return $this;
    } // setId()

    /**
     * Set the value of [account_id] column.
     *
     * @param int $v new value
     * @return User The current object (for fluent API support)
     */
    public function setAccountId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->account_id !== $v) {
            $this->account_id = $v;
            $this->modifiedColumns[] = UserPeer::ACCOUNT_ID;
        }

        if ($this->aAccount !== null && $this->aAccount->getId() !== $v) {
            $this->aAccount = null;
        }


        return $this;
    } // setAccountId()

    /**
     * Set the value of [domain_id] column.
     *
     * @param int $v new value
     * @return User The current object (for fluent API support)
     */
    public function setDomainId($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->domain_id !== $v) {
            $this->domain_id = $v;
            $this->modifiedColumns[] = UserPeer::DOMAIN_ID;
        }

        if ($this->aDomain !== null && $this->aDomain->getId() !== $v) {
            $this->aDomain = null;
        }


        return $this;
    } // setDomainId()

    /**
     * Set the value of [deleted] column.
     *
     * @param int $v new value
     * @return User The current object (for fluent API support)
     */
    public function setDeleted($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->deleted !== $v) {
            $this->deleted = $v;
            $this->modifiedColumns[] = UserPeer::DELETED;
        }


        return $this;
    } // setDeleted()

    /**
     * Set the value of [name] column.
     *
     * @param string $v new value
     * @return User The current object (for fluent API support)
     */
    public function setName($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->name !== $v) {
            $this->name = $v;
            $this->modifiedColumns[] = UserPeer::NAME;
        }


        return $this;
    } // setName()

    /**
     * Set the value of [firstname] column.
     *
     * @param string $v new value
     * @return User The current object (for fluent API support)
     */
    public function setFirstname($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->firstname !== $v) {
            $this->firstname = $v;
            $this->modifiedColumns[] = UserPeer::FIRSTNAME;
        }


        return $this;
    } // setFirstname()

    /**
     * Set the value of [lastname] column.
     *
     * @param string $v new value
     * @return User The current object (for fluent API support)
     */
    public function setLastname($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->lastname !== $v) {
            $this->lastname = $v;
            $this->modifiedColumns[] = UserPeer::LASTNAME;
        }


        return $this;
    } // setLastname()

    /**
     * Set the value of [phone] column.
     *
     * @param string $v new value
     * @return User The current object (for fluent API support)
     */
    public function setPhone($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->phone !== $v) {
            $this->phone = $v;
            $this->modifiedColumns[] = UserPeer::PHONE;
        }


        return $this;
    } // setPhone()

    /**
     * Set the value of [manager_of] column.
     *
     * @param int $v new value
     * @return User The current object (for fluent API support)
     */
    public function setManagerOf($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->manager_of !== $v) {
            $this->manager_of = $v;
            $this->modifiedColumns[] = UserPeer::MANAGER_OF;
        }


        return $this;
    } // setManagerOf()

    /**
     * Set the value of [is_admin] column.
     *
     * @param int $v new value
     * @return User The current object (for fluent API support)
     */
    public function setIsAdmin($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->is_admin !== $v) {
            $this->is_admin = $v;
            $this->modifiedColumns[] = UserPeer::IS_ADMIN;
        }


        return $this;
    } // setIsAdmin()

    /**
     * Set the value of [email] column.
     *
     * @param string $v new value
     * @return User The current object (for fluent API support)
     */
    public function setEmail($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->email !== $v) {
            $this->email = $v;
            $this->modifiedColumns[] = UserPeer::EMAIL;
        }


        return $this;
    } // setEmail()

    /**
     * Set the value of [password_hash] column.
     *
     * @param string $v new value
     * @return User The current object (for fluent API support)
     */
    public function setPasswordHash($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->password_hash !== $v) {
            $this->password_hash = $v;
            $this->modifiedColumns[] = UserPeer::PASSWORD_HASH;
        }


        return $this;
    } // setPasswordHash()

    /**
     * Set the value of [number] column.
     *
     * @param string $v new value
     * @return User The current object (for fluent API support)
     */
    public function setNumber($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->number !== $v) {
            $this->number = $v;
            $this->modifiedColumns[] = UserPeer::NUMBER;
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
            if ($this->deleted !== 0) {
                return false;
            }

            if ($this->is_admin !== 0) {
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
            $this->domain_id = ($row[$startcol + 2] !== null) ? (int) $row[$startcol + 2] : null;
            $this->deleted = ($row[$startcol + 3] !== null) ? (int) $row[$startcol + 3] : null;
            $this->name = ($row[$startcol + 4] !== null) ? (string) $row[$startcol + 4] : null;
            $this->firstname = ($row[$startcol + 5] !== null) ? (string) $row[$startcol + 5] : null;
            $this->lastname = ($row[$startcol + 6] !== null) ? (string) $row[$startcol + 6] : null;
            $this->phone = ($row[$startcol + 7] !== null) ? (string) $row[$startcol + 7] : null;
            $this->manager_of = ($row[$startcol + 8] !== null) ? (int) $row[$startcol + 8] : null;
            $this->is_admin = ($row[$startcol + 9] !== null) ? (int) $row[$startcol + 9] : null;
            $this->email = ($row[$startcol + 10] !== null) ? (string) $row[$startcol + 10] : null;
            $this->password_hash = ($row[$startcol + 11] !== null) ? (string) $row[$startcol + 11] : null;
            $this->number = ($row[$startcol + 12] !== null) ? (string) $row[$startcol + 12] : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
            $this->postHydrate($row, $startcol, $rehydrate);
            return $startcol + 13; // 13 = UserPeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating User object", $e);
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
        if ($this->aDomain !== null && $this->domain_id !== $this->aDomain->getId()) {
            $this->aDomain = null;
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
            $con = Propel::getConnection(UserPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = UserPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aAccount = null;
            $this->aDomain = null;
            $this->collClockingsRelatedByCreatorId = null;

            $this->collClockingsRelatedByUserId = null;

            $this->collPropertyValues = null;

            $this->collSystemLogs = null;

            $this->collTransactionsRelatedByCreatorId = null;

            $this->collTransactionsRelatedByUserId = null;

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
            $con = Propel::getConnection(UserPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = UserQuery::create()
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
            $con = Propel::getConnection(UserPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
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
                UserPeer::addInstanceToPool($this);
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

            if ($this->aDomain !== null) {
                if ($this->aDomain->isModified() || $this->aDomain->isNew()) {
                    $affectedRows += $this->aDomain->save($con);
                }
                $this->setDomain($this->aDomain);
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

            if ($this->clockingsRelatedByCreatorIdScheduledForDeletion !== null) {
                if (!$this->clockingsRelatedByCreatorIdScheduledForDeletion->isEmpty()) {
                    foreach ($this->clockingsRelatedByCreatorIdScheduledForDeletion as $clockingRelatedByCreatorId) {
                        // need to save related object because we set the relation to null
                        $clockingRelatedByCreatorId->save($con);
                    }
                    $this->clockingsRelatedByCreatorIdScheduledForDeletion = null;
                }
            }

            if ($this->collClockingsRelatedByCreatorId !== null) {
                foreach ($this->collClockingsRelatedByCreatorId as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->clockingsRelatedByUserIdScheduledForDeletion !== null) {
                if (!$this->clockingsRelatedByUserIdScheduledForDeletion->isEmpty()) {
                    ClockingQuery::create()
                        ->filterByPrimaryKeys($this->clockingsRelatedByUserIdScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->clockingsRelatedByUserIdScheduledForDeletion = null;
                }
            }

            if ($this->collClockingsRelatedByUserId !== null) {
                foreach ($this->collClockingsRelatedByUserId as $referrerFK) {
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

            if ($this->systemLogsScheduledForDeletion !== null) {
                if (!$this->systemLogsScheduledForDeletion->isEmpty()) {
                    foreach ($this->systemLogsScheduledForDeletion as $systemLog) {
                        // need to save related object because we set the relation to null
                        $systemLog->save($con);
                    }
                    $this->systemLogsScheduledForDeletion = null;
                }
            }

            if ($this->collSystemLogs !== null) {
                foreach ($this->collSystemLogs as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->transactionsRelatedByCreatorIdScheduledForDeletion !== null) {
                if (!$this->transactionsRelatedByCreatorIdScheduledForDeletion->isEmpty()) {
                    foreach ($this->transactionsRelatedByCreatorIdScheduledForDeletion as $transactionRelatedByCreatorId) {
                        // need to save related object because we set the relation to null
                        $transactionRelatedByCreatorId->save($con);
                    }
                    $this->transactionsRelatedByCreatorIdScheduledForDeletion = null;
                }
            }

            if ($this->collTransactionsRelatedByCreatorId !== null) {
                foreach ($this->collTransactionsRelatedByCreatorId as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->transactionsRelatedByUserIdScheduledForDeletion !== null) {
                if (!$this->transactionsRelatedByUserIdScheduledForDeletion->isEmpty()) {
                    TransactionQuery::create()
                        ->filterByPrimaryKeys($this->transactionsRelatedByUserIdScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->transactionsRelatedByUserIdScheduledForDeletion = null;
                }
            }

            if ($this->collTransactionsRelatedByUserId !== null) {
                foreach ($this->collTransactionsRelatedByUserId as $referrerFK) {
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

        $this->modifiedColumns[] = UserPeer::ID;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . UserPeer::ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(UserPeer::ID)) {
            $modifiedColumns[':p' . $index++]  = '`id`';
        }
        if ($this->isColumnModified(UserPeer::ACCOUNT_ID)) {
            $modifiedColumns[':p' . $index++]  = '`account_id`';
        }
        if ($this->isColumnModified(UserPeer::DOMAIN_ID)) {
            $modifiedColumns[':p' . $index++]  = '`domain_id`';
        }
        if ($this->isColumnModified(UserPeer::DELETED)) {
            $modifiedColumns[':p' . $index++]  = '`deleted`';
        }
        if ($this->isColumnModified(UserPeer::NAME)) {
            $modifiedColumns[':p' . $index++]  = '`name`';
        }
        if ($this->isColumnModified(UserPeer::FIRSTNAME)) {
            $modifiedColumns[':p' . $index++]  = '`firstname`';
        }
        if ($this->isColumnModified(UserPeer::LASTNAME)) {
            $modifiedColumns[':p' . $index++]  = '`lastname`';
        }
        if ($this->isColumnModified(UserPeer::PHONE)) {
            $modifiedColumns[':p' . $index++]  = '`phone`';
        }
        if ($this->isColumnModified(UserPeer::MANAGER_OF)) {
            $modifiedColumns[':p' . $index++]  = '`manager_of`';
        }
        if ($this->isColumnModified(UserPeer::IS_ADMIN)) {
            $modifiedColumns[':p' . $index++]  = '`is_admin`';
        }
        if ($this->isColumnModified(UserPeer::EMAIL)) {
            $modifiedColumns[':p' . $index++]  = '`email`';
        }
        if ($this->isColumnModified(UserPeer::PASSWORD_HASH)) {
            $modifiedColumns[':p' . $index++]  = '`password_hash`';
        }
        if ($this->isColumnModified(UserPeer::NUMBER)) {
            $modifiedColumns[':p' . $index++]  = '`number`';
        }

        $sql = sprintf(
            'INSERT INTO `user` (%s) VALUES (%s)',
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
                    case '`domain_id`':
                        $stmt->bindValue($identifier, $this->domain_id, PDO::PARAM_INT);
                        break;
                    case '`deleted`':
                        $stmt->bindValue($identifier, $this->deleted, PDO::PARAM_INT);
                        break;
                    case '`name`':
                        $stmt->bindValue($identifier, $this->name, PDO::PARAM_STR);
                        break;
                    case '`firstname`':
                        $stmt->bindValue($identifier, $this->firstname, PDO::PARAM_STR);
                        break;
                    case '`lastname`':
                        $stmt->bindValue($identifier, $this->lastname, PDO::PARAM_STR);
                        break;
                    case '`phone`':
                        $stmt->bindValue($identifier, $this->phone, PDO::PARAM_STR);
                        break;
                    case '`manager_of`':
                        $stmt->bindValue($identifier, $this->manager_of, PDO::PARAM_INT);
                        break;
                    case '`is_admin`':
                        $stmt->bindValue($identifier, $this->is_admin, PDO::PARAM_INT);
                        break;
                    case '`email`':
                        $stmt->bindValue($identifier, $this->email, PDO::PARAM_STR);
                        break;
                    case '`password_hash`':
                        $stmt->bindValue($identifier, $this->password_hash, PDO::PARAM_STR);
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

            if ($this->aDomain !== null) {
                if (!$this->aDomain->validate($columns)) {
                    $failureMap = array_merge($failureMap, $this->aDomain->getValidationFailures());
                }
            }


            if (($retval = UserPeer::doValidate($this, $columns)) !== true) {
                $failureMap = array_merge($failureMap, $retval);
            }


                if ($this->collClockingsRelatedByCreatorId !== null) {
                    foreach ($this->collClockingsRelatedByCreatorId as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collClockingsRelatedByUserId !== null) {
                    foreach ($this->collClockingsRelatedByUserId as $referrerFK) {
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

                if ($this->collSystemLogs !== null) {
                    foreach ($this->collSystemLogs as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collTransactionsRelatedByCreatorId !== null) {
                    foreach ($this->collTransactionsRelatedByCreatorId as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collTransactionsRelatedByUserId !== null) {
                    foreach ($this->collTransactionsRelatedByUserId as $referrerFK) {
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
        $pos = UserPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
                return $this->getDomainId();
                break;
            case 3:
                return $this->getDeleted();
                break;
            case 4:
                return $this->getName();
                break;
            case 5:
                return $this->getFirstname();
                break;
            case 6:
                return $this->getLastname();
                break;
            case 7:
                return $this->getPhone();
                break;
            case 8:
                return $this->getManagerOf();
                break;
            case 9:
                return $this->getIsAdmin();
                break;
            case 10:
                return $this->getEmail();
                break;
            case 11:
                return $this->getPasswordHash();
                break;
            case 12:
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
        if (isset($alreadyDumpedObjects['User'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['User'][$this->getPrimaryKey()] = true;
        $keys = UserPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getAccountId(),
            $keys[2] => $this->getDomainId(),
            $keys[3] => $this->getDeleted(),
            $keys[4] => $this->getName(),
            $keys[5] => $this->getFirstname(),
            $keys[6] => $this->getLastname(),
            $keys[7] => $this->getPhone(),
            $keys[8] => $this->getManagerOf(),
            $keys[9] => $this->getIsAdmin(),
            $keys[10] => $this->getEmail(),
            $keys[11] => $this->getPasswordHash(),
            $keys[12] => $this->getNumber(),
        );
        if ($includeForeignObjects) {
            if (null !== $this->aAccount) {
                $result['Account'] = $this->aAccount->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aDomain) {
                $result['Domain'] = $this->aDomain->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collClockingsRelatedByCreatorId) {
                $result['ClockingsRelatedByCreatorId'] = $this->collClockingsRelatedByCreatorId->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collClockingsRelatedByUserId) {
                $result['ClockingsRelatedByUserId'] = $this->collClockingsRelatedByUserId->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collPropertyValues) {
                $result['PropertyValues'] = $this->collPropertyValues->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collSystemLogs) {
                $result['SystemLogs'] = $this->collSystemLogs->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collTransactionsRelatedByCreatorId) {
                $result['TransactionsRelatedByCreatorId'] = $this->collTransactionsRelatedByCreatorId->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collTransactionsRelatedByUserId) {
                $result['TransactionsRelatedByUserId'] = $this->collTransactionsRelatedByUserId->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = UserPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

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
                $this->setDomainId($value);
                break;
            case 3:
                $this->setDeleted($value);
                break;
            case 4:
                $this->setName($value);
                break;
            case 5:
                $this->setFirstname($value);
                break;
            case 6:
                $this->setLastname($value);
                break;
            case 7:
                $this->setPhone($value);
                break;
            case 8:
                $this->setManagerOf($value);
                break;
            case 9:
                $this->setIsAdmin($value);
                break;
            case 10:
                $this->setEmail($value);
                break;
            case 11:
                $this->setPasswordHash($value);
                break;
            case 12:
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
        $keys = UserPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setAccountId($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setDomainId($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setDeleted($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setName($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setFirstname($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setLastname($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setPhone($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setManagerOf($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setIsAdmin($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setEmail($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setPasswordHash($arr[$keys[11]]);
        if (array_key_exists($keys[12], $arr)) $this->setNumber($arr[$keys[12]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(UserPeer::DATABASE_NAME);

        if ($this->isColumnModified(UserPeer::ID)) $criteria->add(UserPeer::ID, $this->id);
        if ($this->isColumnModified(UserPeer::ACCOUNT_ID)) $criteria->add(UserPeer::ACCOUNT_ID, $this->account_id);
        if ($this->isColumnModified(UserPeer::DOMAIN_ID)) $criteria->add(UserPeer::DOMAIN_ID, $this->domain_id);
        if ($this->isColumnModified(UserPeer::DELETED)) $criteria->add(UserPeer::DELETED, $this->deleted);
        if ($this->isColumnModified(UserPeer::NAME)) $criteria->add(UserPeer::NAME, $this->name);
        if ($this->isColumnModified(UserPeer::FIRSTNAME)) $criteria->add(UserPeer::FIRSTNAME, $this->firstname);
        if ($this->isColumnModified(UserPeer::LASTNAME)) $criteria->add(UserPeer::LASTNAME, $this->lastname);
        if ($this->isColumnModified(UserPeer::PHONE)) $criteria->add(UserPeer::PHONE, $this->phone);
        if ($this->isColumnModified(UserPeer::MANAGER_OF)) $criteria->add(UserPeer::MANAGER_OF, $this->manager_of);
        if ($this->isColumnModified(UserPeer::IS_ADMIN)) $criteria->add(UserPeer::IS_ADMIN, $this->is_admin);
        if ($this->isColumnModified(UserPeer::EMAIL)) $criteria->add(UserPeer::EMAIL, $this->email);
        if ($this->isColumnModified(UserPeer::PASSWORD_HASH)) $criteria->add(UserPeer::PASSWORD_HASH, $this->password_hash);
        if ($this->isColumnModified(UserPeer::NUMBER)) $criteria->add(UserPeer::NUMBER, $this->number);

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
        $criteria = new Criteria(UserPeer::DATABASE_NAME);
        $criteria->add(UserPeer::ID, $this->id);

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
     * @param object $copyObj An object of User (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setAccountId($this->getAccountId());
        $copyObj->setDomainId($this->getDomainId());
        $copyObj->setDeleted($this->getDeleted());
        $copyObj->setName($this->getName());
        $copyObj->setFirstname($this->getFirstname());
        $copyObj->setLastname($this->getLastname());
        $copyObj->setPhone($this->getPhone());
        $copyObj->setManagerOf($this->getManagerOf());
        $copyObj->setIsAdmin($this->getIsAdmin());
        $copyObj->setEmail($this->getEmail());
        $copyObj->setPasswordHash($this->getPasswordHash());
        $copyObj->setNumber($this->getNumber());

        if ($deepCopy && !$this->startCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);
            // store object hash to prevent cycle
            $this->startCopy = true;

            foreach ($this->getClockingsRelatedByCreatorId() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addClockingRelatedByCreatorId($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getClockingsRelatedByUserId() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addClockingRelatedByUserId($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getPropertyValues() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addPropertyValue($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getSystemLogs() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addSystemLog($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getTransactionsRelatedByCreatorId() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addTransactionRelatedByCreatorId($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getTransactionsRelatedByUserId() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addTransactionRelatedByUserId($relObj->copy($deepCopy));
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
     * @return User Clone of current object.
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
     * @return UserPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new UserPeer();
        }

        return self::$peer;
    }

    /**
     * Declares an association between this object and a Account object.
     *
     * @param             Account $v
     * @return User The current object (for fluent API support)
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
            $v->addUser($this);
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
                $this->aAccount->addUsers($this);
             */
        }

        return $this->aAccount;
    }

    /**
     * Declares an association between this object and a Domain object.
     *
     * @param             Domain $v
     * @return User The current object (for fluent API support)
     * @throws PropelException
     */
    public function setDomain(Domain $v = null)
    {
        if ($v === null) {
            $this->setDomainId(NULL);
        } else {
            $this->setDomainId($v->getId());
        }

        $this->aDomain = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the Domain object, it will not be re-added.
        if ($v !== null) {
            $v->addUser($this);
        }


        return $this;
    }


    /**
     * Get the associated Domain object
     *
     * @param PropelPDO $con Optional Connection object.
     * @param $doQuery Executes a query to get the object if required
     * @return Domain The associated Domain object.
     * @throws PropelException
     */
    public function getDomain(PropelPDO $con = null, $doQuery = true)
    {
        if ($this->aDomain === null && ($this->domain_id !== null) && $doQuery) {
            $this->aDomain = DomainQuery::create()->findPk($this->domain_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aDomain->addUsers($this);
             */
        }

        return $this->aDomain;
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
        if ('ClockingRelatedByCreatorId' == $relationName) {
            $this->initClockingsRelatedByCreatorId();
        }
        if ('ClockingRelatedByUserId' == $relationName) {
            $this->initClockingsRelatedByUserId();
        }
        if ('PropertyValue' == $relationName) {
            $this->initPropertyValues();
        }
        if ('SystemLog' == $relationName) {
            $this->initSystemLogs();
        }
        if ('TransactionRelatedByCreatorId' == $relationName) {
            $this->initTransactionsRelatedByCreatorId();
        }
        if ('TransactionRelatedByUserId' == $relationName) {
            $this->initTransactionsRelatedByUserId();
        }
    }

    /**
     * Clears out the collClockingsRelatedByCreatorId collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return User The current object (for fluent API support)
     * @see        addClockingsRelatedByCreatorId()
     */
    public function clearClockingsRelatedByCreatorId()
    {
        $this->collClockingsRelatedByCreatorId = null; // important to set this to null since that means it is uninitialized
        $this->collClockingsRelatedByCreatorIdPartial = null;

        return $this;
    }

    /**
     * reset is the collClockingsRelatedByCreatorId collection loaded partially
     *
     * @return void
     */
    public function resetPartialClockingsRelatedByCreatorId($v = true)
    {
        $this->collClockingsRelatedByCreatorIdPartial = $v;
    }

    /**
     * Initializes the collClockingsRelatedByCreatorId collection.
     *
     * By default this just sets the collClockingsRelatedByCreatorId collection to an empty array (like clearcollClockingsRelatedByCreatorId());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initClockingsRelatedByCreatorId($overrideExisting = true)
    {
        if (null !== $this->collClockingsRelatedByCreatorId && !$overrideExisting) {
            return;
        }
        $this->collClockingsRelatedByCreatorId = new PropelObjectCollection();
        $this->collClockingsRelatedByCreatorId->setModel('Clocking');
    }

    /**
     * Gets an array of Clocking objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this User is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|Clocking[] List of Clocking objects
     * @throws PropelException
     */
    public function getClockingsRelatedByCreatorId($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collClockingsRelatedByCreatorIdPartial && !$this->isNew();
        if (null === $this->collClockingsRelatedByCreatorId || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collClockingsRelatedByCreatorId) {
                // return empty collection
                $this->initClockingsRelatedByCreatorId();
            } else {
                $collClockingsRelatedByCreatorId = ClockingQuery::create(null, $criteria)
                    ->filterByUserRelatedByCreatorId($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collClockingsRelatedByCreatorIdPartial && count($collClockingsRelatedByCreatorId)) {
                      $this->initClockingsRelatedByCreatorId(false);

                      foreach($collClockingsRelatedByCreatorId as $obj) {
                        if (false == $this->collClockingsRelatedByCreatorId->contains($obj)) {
                          $this->collClockingsRelatedByCreatorId->append($obj);
                        }
                      }

                      $this->collClockingsRelatedByCreatorIdPartial = true;
                    }

                    $collClockingsRelatedByCreatorId->getInternalIterator()->rewind();
                    return $collClockingsRelatedByCreatorId;
                }

                if($partial && $this->collClockingsRelatedByCreatorId) {
                    foreach($this->collClockingsRelatedByCreatorId as $obj) {
                        if($obj->isNew()) {
                            $collClockingsRelatedByCreatorId[] = $obj;
                        }
                    }
                }

                $this->collClockingsRelatedByCreatorId = $collClockingsRelatedByCreatorId;
                $this->collClockingsRelatedByCreatorIdPartial = false;
            }
        }

        return $this->collClockingsRelatedByCreatorId;
    }

    /**
     * Sets a collection of ClockingRelatedByCreatorId objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $clockingsRelatedByCreatorId A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return User The current object (for fluent API support)
     */
    public function setClockingsRelatedByCreatorId(PropelCollection $clockingsRelatedByCreatorId, PropelPDO $con = null)
    {
        $clockingsRelatedByCreatorIdToDelete = $this->getClockingsRelatedByCreatorId(new Criteria(), $con)->diff($clockingsRelatedByCreatorId);

        $this->clockingsRelatedByCreatorIdScheduledForDeletion = unserialize(serialize($clockingsRelatedByCreatorIdToDelete));

        foreach ($clockingsRelatedByCreatorIdToDelete as $clockingRelatedByCreatorIdRemoved) {
            $clockingRelatedByCreatorIdRemoved->setUserRelatedByCreatorId(null);
        }

        $this->collClockingsRelatedByCreatorId = null;
        foreach ($clockingsRelatedByCreatorId as $clockingRelatedByCreatorId) {
            $this->addClockingRelatedByCreatorId($clockingRelatedByCreatorId);
        }

        $this->collClockingsRelatedByCreatorId = $clockingsRelatedByCreatorId;
        $this->collClockingsRelatedByCreatorIdPartial = false;

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
    public function countClockingsRelatedByCreatorId(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collClockingsRelatedByCreatorIdPartial && !$this->isNew();
        if (null === $this->collClockingsRelatedByCreatorId || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collClockingsRelatedByCreatorId) {
                return 0;
            }

            if($partial && !$criteria) {
                return count($this->getClockingsRelatedByCreatorId());
            }
            $query = ClockingQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByUserRelatedByCreatorId($this)
                ->count($con);
        }

        return count($this->collClockingsRelatedByCreatorId);
    }

    /**
     * Method called to associate a Clocking object to this object
     * through the Clocking foreign key attribute.
     *
     * @param    Clocking $l Clocking
     * @return User The current object (for fluent API support)
     */
    public function addClockingRelatedByCreatorId(Clocking $l)
    {
        if ($this->collClockingsRelatedByCreatorId === null) {
            $this->initClockingsRelatedByCreatorId();
            $this->collClockingsRelatedByCreatorIdPartial = true;
        }
        if (!in_array($l, $this->collClockingsRelatedByCreatorId->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddClockingRelatedByCreatorId($l);
        }

        return $this;
    }

    /**
     * @param	ClockingRelatedByCreatorId $clockingRelatedByCreatorId The clockingRelatedByCreatorId object to add.
     */
    protected function doAddClockingRelatedByCreatorId($clockingRelatedByCreatorId)
    {
        $this->collClockingsRelatedByCreatorId[]= $clockingRelatedByCreatorId;
        $clockingRelatedByCreatorId->setUserRelatedByCreatorId($this);
    }

    /**
     * @param	ClockingRelatedByCreatorId $clockingRelatedByCreatorId The clockingRelatedByCreatorId object to remove.
     * @return User The current object (for fluent API support)
     */
    public function removeClockingRelatedByCreatorId($clockingRelatedByCreatorId)
    {
        if ($this->getClockingsRelatedByCreatorId()->contains($clockingRelatedByCreatorId)) {
            $this->collClockingsRelatedByCreatorId->remove($this->collClockingsRelatedByCreatorId->search($clockingRelatedByCreatorId));
            if (null === $this->clockingsRelatedByCreatorIdScheduledForDeletion) {
                $this->clockingsRelatedByCreatorIdScheduledForDeletion = clone $this->collClockingsRelatedByCreatorId;
                $this->clockingsRelatedByCreatorIdScheduledForDeletion->clear();
            }
            $this->clockingsRelatedByCreatorIdScheduledForDeletion[]= $clockingRelatedByCreatorId;
            $clockingRelatedByCreatorId->setUserRelatedByCreatorId(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this User is new, it will return
     * an empty collection; or if this User has previously
     * been saved, it will retrieve related ClockingsRelatedByCreatorId from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in User.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|Clocking[] List of Clocking objects
     */
    public function getClockingsRelatedByCreatorIdJoinClockingType($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = ClockingQuery::create(null, $criteria);
        $query->joinWith('ClockingType', $join_behavior);

        return $this->getClockingsRelatedByCreatorId($query, $con);
    }

    /**
     * Clears out the collClockingsRelatedByUserId collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return User The current object (for fluent API support)
     * @see        addClockingsRelatedByUserId()
     */
    public function clearClockingsRelatedByUserId()
    {
        $this->collClockingsRelatedByUserId = null; // important to set this to null since that means it is uninitialized
        $this->collClockingsRelatedByUserIdPartial = null;

        return $this;
    }

    /**
     * reset is the collClockingsRelatedByUserId collection loaded partially
     *
     * @return void
     */
    public function resetPartialClockingsRelatedByUserId($v = true)
    {
        $this->collClockingsRelatedByUserIdPartial = $v;
    }

    /**
     * Initializes the collClockingsRelatedByUserId collection.
     *
     * By default this just sets the collClockingsRelatedByUserId collection to an empty array (like clearcollClockingsRelatedByUserId());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initClockingsRelatedByUserId($overrideExisting = true)
    {
        if (null !== $this->collClockingsRelatedByUserId && !$overrideExisting) {
            return;
        }
        $this->collClockingsRelatedByUserId = new PropelObjectCollection();
        $this->collClockingsRelatedByUserId->setModel('Clocking');
    }

    /**
     * Gets an array of Clocking objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this User is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|Clocking[] List of Clocking objects
     * @throws PropelException
     */
    public function getClockingsRelatedByUserId($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collClockingsRelatedByUserIdPartial && !$this->isNew();
        if (null === $this->collClockingsRelatedByUserId || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collClockingsRelatedByUserId) {
                // return empty collection
                $this->initClockingsRelatedByUserId();
            } else {
                $collClockingsRelatedByUserId = ClockingQuery::create(null, $criteria)
                    ->filterByUserRelatedByUserId($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collClockingsRelatedByUserIdPartial && count($collClockingsRelatedByUserId)) {
                      $this->initClockingsRelatedByUserId(false);

                      foreach($collClockingsRelatedByUserId as $obj) {
                        if (false == $this->collClockingsRelatedByUserId->contains($obj)) {
                          $this->collClockingsRelatedByUserId->append($obj);
                        }
                      }

                      $this->collClockingsRelatedByUserIdPartial = true;
                    }

                    $collClockingsRelatedByUserId->getInternalIterator()->rewind();
                    return $collClockingsRelatedByUserId;
                }

                if($partial && $this->collClockingsRelatedByUserId) {
                    foreach($this->collClockingsRelatedByUserId as $obj) {
                        if($obj->isNew()) {
                            $collClockingsRelatedByUserId[] = $obj;
                        }
                    }
                }

                $this->collClockingsRelatedByUserId = $collClockingsRelatedByUserId;
                $this->collClockingsRelatedByUserIdPartial = false;
            }
        }

        return $this->collClockingsRelatedByUserId;
    }

    /**
     * Sets a collection of ClockingRelatedByUserId objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $clockingsRelatedByUserId A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return User The current object (for fluent API support)
     */
    public function setClockingsRelatedByUserId(PropelCollection $clockingsRelatedByUserId, PropelPDO $con = null)
    {
        $clockingsRelatedByUserIdToDelete = $this->getClockingsRelatedByUserId(new Criteria(), $con)->diff($clockingsRelatedByUserId);

        $this->clockingsRelatedByUserIdScheduledForDeletion = unserialize(serialize($clockingsRelatedByUserIdToDelete));

        foreach ($clockingsRelatedByUserIdToDelete as $clockingRelatedByUserIdRemoved) {
            $clockingRelatedByUserIdRemoved->setUserRelatedByUserId(null);
        }

        $this->collClockingsRelatedByUserId = null;
        foreach ($clockingsRelatedByUserId as $clockingRelatedByUserId) {
            $this->addClockingRelatedByUserId($clockingRelatedByUserId);
        }

        $this->collClockingsRelatedByUserId = $clockingsRelatedByUserId;
        $this->collClockingsRelatedByUserIdPartial = false;

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
    public function countClockingsRelatedByUserId(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collClockingsRelatedByUserIdPartial && !$this->isNew();
        if (null === $this->collClockingsRelatedByUserId || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collClockingsRelatedByUserId) {
                return 0;
            }

            if($partial && !$criteria) {
                return count($this->getClockingsRelatedByUserId());
            }
            $query = ClockingQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByUserRelatedByUserId($this)
                ->count($con);
        }

        return count($this->collClockingsRelatedByUserId);
    }

    /**
     * Method called to associate a Clocking object to this object
     * through the Clocking foreign key attribute.
     *
     * @param    Clocking $l Clocking
     * @return User The current object (for fluent API support)
     */
    public function addClockingRelatedByUserId(Clocking $l)
    {
        if ($this->collClockingsRelatedByUserId === null) {
            $this->initClockingsRelatedByUserId();
            $this->collClockingsRelatedByUserIdPartial = true;
        }
        if (!in_array($l, $this->collClockingsRelatedByUserId->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddClockingRelatedByUserId($l);
        }

        return $this;
    }

    /**
     * @param	ClockingRelatedByUserId $clockingRelatedByUserId The clockingRelatedByUserId object to add.
     */
    protected function doAddClockingRelatedByUserId($clockingRelatedByUserId)
    {
        $this->collClockingsRelatedByUserId[]= $clockingRelatedByUserId;
        $clockingRelatedByUserId->setUserRelatedByUserId($this);
    }

    /**
     * @param	ClockingRelatedByUserId $clockingRelatedByUserId The clockingRelatedByUserId object to remove.
     * @return User The current object (for fluent API support)
     */
    public function removeClockingRelatedByUserId($clockingRelatedByUserId)
    {
        if ($this->getClockingsRelatedByUserId()->contains($clockingRelatedByUserId)) {
            $this->collClockingsRelatedByUserId->remove($this->collClockingsRelatedByUserId->search($clockingRelatedByUserId));
            if (null === $this->clockingsRelatedByUserIdScheduledForDeletion) {
                $this->clockingsRelatedByUserIdScheduledForDeletion = clone $this->collClockingsRelatedByUserId;
                $this->clockingsRelatedByUserIdScheduledForDeletion->clear();
            }
            $this->clockingsRelatedByUserIdScheduledForDeletion[]= clone $clockingRelatedByUserId;
            $clockingRelatedByUserId->setUserRelatedByUserId(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this User is new, it will return
     * an empty collection; or if this User has previously
     * been saved, it will retrieve related ClockingsRelatedByUserId from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in User.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|Clocking[] List of Clocking objects
     */
    public function getClockingsRelatedByUserIdJoinClockingType($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = ClockingQuery::create(null, $criteria);
        $query->joinWith('ClockingType', $join_behavior);

        return $this->getClockingsRelatedByUserId($query, $con);
    }

    /**
     * Clears out the collPropertyValues collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return User The current object (for fluent API support)
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
     * If this User is new, it will return
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
                    ->filterByUser($this)
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
     * @return User The current object (for fluent API support)
     */
    public function setPropertyValues(PropelCollection $propertyValues, PropelPDO $con = null)
    {
        $propertyValuesToDelete = $this->getPropertyValues(new Criteria(), $con)->diff($propertyValues);

        $this->propertyValuesScheduledForDeletion = unserialize(serialize($propertyValuesToDelete));

        foreach ($propertyValuesToDelete as $propertyValueRemoved) {
            $propertyValueRemoved->setUser(null);
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
                ->filterByUser($this)
                ->count($con);
        }

        return count($this->collPropertyValues);
    }

    /**
     * Method called to associate a PropertyValue object to this object
     * through the PropertyValue foreign key attribute.
     *
     * @param    PropertyValue $l PropertyValue
     * @return User The current object (for fluent API support)
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
        $propertyValue->setUser($this);
    }

    /**
     * @param	PropertyValue $propertyValue The propertyValue object to remove.
     * @return User The current object (for fluent API support)
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
            $propertyValue->setUser(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this User is new, it will return
     * an empty collection; or if this User has previously
     * been saved, it will retrieve related PropertyValues from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in User.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|PropertyValue[] List of PropertyValue objects
     */
    public function getPropertyValuesJoinDomain($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = PropertyValueQuery::create(null, $criteria);
        $query->joinWith('Domain', $join_behavior);

        return $this->getPropertyValues($query, $con);
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this User is new, it will return
     * an empty collection; or if this User has previously
     * been saved, it will retrieve related PropertyValues from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in User.
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
     * Clears out the collSystemLogs collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return User The current object (for fluent API support)
     * @see        addSystemLogs()
     */
    public function clearSystemLogs()
    {
        $this->collSystemLogs = null; // important to set this to null since that means it is uninitialized
        $this->collSystemLogsPartial = null;

        return $this;
    }

    /**
     * reset is the collSystemLogs collection loaded partially
     *
     * @return void
     */
    public function resetPartialSystemLogs($v = true)
    {
        $this->collSystemLogsPartial = $v;
    }

    /**
     * Initializes the collSystemLogs collection.
     *
     * By default this just sets the collSystemLogs collection to an empty array (like clearcollSystemLogs());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initSystemLogs($overrideExisting = true)
    {
        if (null !== $this->collSystemLogs && !$overrideExisting) {
            return;
        }
        $this->collSystemLogs = new PropelObjectCollection();
        $this->collSystemLogs->setModel('SystemLog');
    }

    /**
     * Gets an array of SystemLog objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this User is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|SystemLog[] List of SystemLog objects
     * @throws PropelException
     */
    public function getSystemLogs($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collSystemLogsPartial && !$this->isNew();
        if (null === $this->collSystemLogs || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collSystemLogs) {
                // return empty collection
                $this->initSystemLogs();
            } else {
                $collSystemLogs = SystemLogQuery::create(null, $criteria)
                    ->filterByUser($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collSystemLogsPartial && count($collSystemLogs)) {
                      $this->initSystemLogs(false);

                      foreach($collSystemLogs as $obj) {
                        if (false == $this->collSystemLogs->contains($obj)) {
                          $this->collSystemLogs->append($obj);
                        }
                      }

                      $this->collSystemLogsPartial = true;
                    }

                    $collSystemLogs->getInternalIterator()->rewind();
                    return $collSystemLogs;
                }

                if($partial && $this->collSystemLogs) {
                    foreach($this->collSystemLogs as $obj) {
                        if($obj->isNew()) {
                            $collSystemLogs[] = $obj;
                        }
                    }
                }

                $this->collSystemLogs = $collSystemLogs;
                $this->collSystemLogsPartial = false;
            }
        }

        return $this->collSystemLogs;
    }

    /**
     * Sets a collection of SystemLog objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $systemLogs A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return User The current object (for fluent API support)
     */
    public function setSystemLogs(PropelCollection $systemLogs, PropelPDO $con = null)
    {
        $systemLogsToDelete = $this->getSystemLogs(new Criteria(), $con)->diff($systemLogs);

        $this->systemLogsScheduledForDeletion = unserialize(serialize($systemLogsToDelete));

        foreach ($systemLogsToDelete as $systemLogRemoved) {
            $systemLogRemoved->setUser(null);
        }

        $this->collSystemLogs = null;
        foreach ($systemLogs as $systemLog) {
            $this->addSystemLog($systemLog);
        }

        $this->collSystemLogs = $systemLogs;
        $this->collSystemLogsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related SystemLog objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related SystemLog objects.
     * @throws PropelException
     */
    public function countSystemLogs(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collSystemLogsPartial && !$this->isNew();
        if (null === $this->collSystemLogs || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collSystemLogs) {
                return 0;
            }

            if($partial && !$criteria) {
                return count($this->getSystemLogs());
            }
            $query = SystemLogQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByUser($this)
                ->count($con);
        }

        return count($this->collSystemLogs);
    }

    /**
     * Method called to associate a SystemLog object to this object
     * through the SystemLog foreign key attribute.
     *
     * @param    SystemLog $l SystemLog
     * @return User The current object (for fluent API support)
     */
    public function addSystemLog(SystemLog $l)
    {
        if ($this->collSystemLogs === null) {
            $this->initSystemLogs();
            $this->collSystemLogsPartial = true;
        }
        if (!in_array($l, $this->collSystemLogs->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddSystemLog($l);
        }

        return $this;
    }

    /**
     * @param	SystemLog $systemLog The systemLog object to add.
     */
    protected function doAddSystemLog($systemLog)
    {
        $this->collSystemLogs[]= $systemLog;
        $systemLog->setUser($this);
    }

    /**
     * @param	SystemLog $systemLog The systemLog object to remove.
     * @return User The current object (for fluent API support)
     */
    public function removeSystemLog($systemLog)
    {
        if ($this->getSystemLogs()->contains($systemLog)) {
            $this->collSystemLogs->remove($this->collSystemLogs->search($systemLog));
            if (null === $this->systemLogsScheduledForDeletion) {
                $this->systemLogsScheduledForDeletion = clone $this->collSystemLogs;
                $this->systemLogsScheduledForDeletion->clear();
            }
            $this->systemLogsScheduledForDeletion[]= $systemLog;
            $systemLog->setUser(null);
        }

        return $this;
    }

    /**
     * Clears out the collTransactionsRelatedByCreatorId collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return User The current object (for fluent API support)
     * @see        addTransactionsRelatedByCreatorId()
     */
    public function clearTransactionsRelatedByCreatorId()
    {
        $this->collTransactionsRelatedByCreatorId = null; // important to set this to null since that means it is uninitialized
        $this->collTransactionsRelatedByCreatorIdPartial = null;

        return $this;
    }

    /**
     * reset is the collTransactionsRelatedByCreatorId collection loaded partially
     *
     * @return void
     */
    public function resetPartialTransactionsRelatedByCreatorId($v = true)
    {
        $this->collTransactionsRelatedByCreatorIdPartial = $v;
    }

    /**
     * Initializes the collTransactionsRelatedByCreatorId collection.
     *
     * By default this just sets the collTransactionsRelatedByCreatorId collection to an empty array (like clearcollTransactionsRelatedByCreatorId());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initTransactionsRelatedByCreatorId($overrideExisting = true)
    {
        if (null !== $this->collTransactionsRelatedByCreatorId && !$overrideExisting) {
            return;
        }
        $this->collTransactionsRelatedByCreatorId = new PropelObjectCollection();
        $this->collTransactionsRelatedByCreatorId->setModel('Transaction');
    }

    /**
     * Gets an array of Transaction objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this User is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|Transaction[] List of Transaction objects
     * @throws PropelException
     */
    public function getTransactionsRelatedByCreatorId($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collTransactionsRelatedByCreatorIdPartial && !$this->isNew();
        if (null === $this->collTransactionsRelatedByCreatorId || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collTransactionsRelatedByCreatorId) {
                // return empty collection
                $this->initTransactionsRelatedByCreatorId();
            } else {
                $collTransactionsRelatedByCreatorId = TransactionQuery::create(null, $criteria)
                    ->filterByUserRelatedByCreatorId($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collTransactionsRelatedByCreatorIdPartial && count($collTransactionsRelatedByCreatorId)) {
                      $this->initTransactionsRelatedByCreatorId(false);

                      foreach($collTransactionsRelatedByCreatorId as $obj) {
                        if (false == $this->collTransactionsRelatedByCreatorId->contains($obj)) {
                          $this->collTransactionsRelatedByCreatorId->append($obj);
                        }
                      }

                      $this->collTransactionsRelatedByCreatorIdPartial = true;
                    }

                    $collTransactionsRelatedByCreatorId->getInternalIterator()->rewind();
                    return $collTransactionsRelatedByCreatorId;
                }

                if($partial && $this->collTransactionsRelatedByCreatorId) {
                    foreach($this->collTransactionsRelatedByCreatorId as $obj) {
                        if($obj->isNew()) {
                            $collTransactionsRelatedByCreatorId[] = $obj;
                        }
                    }
                }

                $this->collTransactionsRelatedByCreatorId = $collTransactionsRelatedByCreatorId;
                $this->collTransactionsRelatedByCreatorIdPartial = false;
            }
        }

        return $this->collTransactionsRelatedByCreatorId;
    }

    /**
     * Sets a collection of TransactionRelatedByCreatorId objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $transactionsRelatedByCreatorId A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return User The current object (for fluent API support)
     */
    public function setTransactionsRelatedByCreatorId(PropelCollection $transactionsRelatedByCreatorId, PropelPDO $con = null)
    {
        $transactionsRelatedByCreatorIdToDelete = $this->getTransactionsRelatedByCreatorId(new Criteria(), $con)->diff($transactionsRelatedByCreatorId);

        $this->transactionsRelatedByCreatorIdScheduledForDeletion = unserialize(serialize($transactionsRelatedByCreatorIdToDelete));

        foreach ($transactionsRelatedByCreatorIdToDelete as $transactionRelatedByCreatorIdRemoved) {
            $transactionRelatedByCreatorIdRemoved->setUserRelatedByCreatorId(null);
        }

        $this->collTransactionsRelatedByCreatorId = null;
        foreach ($transactionsRelatedByCreatorId as $transactionRelatedByCreatorId) {
            $this->addTransactionRelatedByCreatorId($transactionRelatedByCreatorId);
        }

        $this->collTransactionsRelatedByCreatorId = $transactionsRelatedByCreatorId;
        $this->collTransactionsRelatedByCreatorIdPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Transaction objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related Transaction objects.
     * @throws PropelException
     */
    public function countTransactionsRelatedByCreatorId(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collTransactionsRelatedByCreatorIdPartial && !$this->isNew();
        if (null === $this->collTransactionsRelatedByCreatorId || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collTransactionsRelatedByCreatorId) {
                return 0;
            }

            if($partial && !$criteria) {
                return count($this->getTransactionsRelatedByCreatorId());
            }
            $query = TransactionQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByUserRelatedByCreatorId($this)
                ->count($con);
        }

        return count($this->collTransactionsRelatedByCreatorId);
    }

    /**
     * Method called to associate a Transaction object to this object
     * through the Transaction foreign key attribute.
     *
     * @param    Transaction $l Transaction
     * @return User The current object (for fluent API support)
     */
    public function addTransactionRelatedByCreatorId(Transaction $l)
    {
        if ($this->collTransactionsRelatedByCreatorId === null) {
            $this->initTransactionsRelatedByCreatorId();
            $this->collTransactionsRelatedByCreatorIdPartial = true;
        }
        if (!in_array($l, $this->collTransactionsRelatedByCreatorId->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddTransactionRelatedByCreatorId($l);
        }

        return $this;
    }

    /**
     * @param	TransactionRelatedByCreatorId $transactionRelatedByCreatorId The transactionRelatedByCreatorId object to add.
     */
    protected function doAddTransactionRelatedByCreatorId($transactionRelatedByCreatorId)
    {
        $this->collTransactionsRelatedByCreatorId[]= $transactionRelatedByCreatorId;
        $transactionRelatedByCreatorId->setUserRelatedByCreatorId($this);
    }

    /**
     * @param	TransactionRelatedByCreatorId $transactionRelatedByCreatorId The transactionRelatedByCreatorId object to remove.
     * @return User The current object (for fluent API support)
     */
    public function removeTransactionRelatedByCreatorId($transactionRelatedByCreatorId)
    {
        if ($this->getTransactionsRelatedByCreatorId()->contains($transactionRelatedByCreatorId)) {
            $this->collTransactionsRelatedByCreatorId->remove($this->collTransactionsRelatedByCreatorId->search($transactionRelatedByCreatorId));
            if (null === $this->transactionsRelatedByCreatorIdScheduledForDeletion) {
                $this->transactionsRelatedByCreatorIdScheduledForDeletion = clone $this->collTransactionsRelatedByCreatorId;
                $this->transactionsRelatedByCreatorIdScheduledForDeletion->clear();
            }
            $this->transactionsRelatedByCreatorIdScheduledForDeletion[]= $transactionRelatedByCreatorId;
            $transactionRelatedByCreatorId->setUserRelatedByCreatorId(null);
        }

        return $this;
    }

    /**
     * Clears out the collTransactionsRelatedByUserId collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return User The current object (for fluent API support)
     * @see        addTransactionsRelatedByUserId()
     */
    public function clearTransactionsRelatedByUserId()
    {
        $this->collTransactionsRelatedByUserId = null; // important to set this to null since that means it is uninitialized
        $this->collTransactionsRelatedByUserIdPartial = null;

        return $this;
    }

    /**
     * reset is the collTransactionsRelatedByUserId collection loaded partially
     *
     * @return void
     */
    public function resetPartialTransactionsRelatedByUserId($v = true)
    {
        $this->collTransactionsRelatedByUserIdPartial = $v;
    }

    /**
     * Initializes the collTransactionsRelatedByUserId collection.
     *
     * By default this just sets the collTransactionsRelatedByUserId collection to an empty array (like clearcollTransactionsRelatedByUserId());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initTransactionsRelatedByUserId($overrideExisting = true)
    {
        if (null !== $this->collTransactionsRelatedByUserId && !$overrideExisting) {
            return;
        }
        $this->collTransactionsRelatedByUserId = new PropelObjectCollection();
        $this->collTransactionsRelatedByUserId->setModel('Transaction');
    }

    /**
     * Gets an array of Transaction objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this User is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|Transaction[] List of Transaction objects
     * @throws PropelException
     */
    public function getTransactionsRelatedByUserId($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collTransactionsRelatedByUserIdPartial && !$this->isNew();
        if (null === $this->collTransactionsRelatedByUserId || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collTransactionsRelatedByUserId) {
                // return empty collection
                $this->initTransactionsRelatedByUserId();
            } else {
                $collTransactionsRelatedByUserId = TransactionQuery::create(null, $criteria)
                    ->filterByUserRelatedByUserId($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collTransactionsRelatedByUserIdPartial && count($collTransactionsRelatedByUserId)) {
                      $this->initTransactionsRelatedByUserId(false);

                      foreach($collTransactionsRelatedByUserId as $obj) {
                        if (false == $this->collTransactionsRelatedByUserId->contains($obj)) {
                          $this->collTransactionsRelatedByUserId->append($obj);
                        }
                      }

                      $this->collTransactionsRelatedByUserIdPartial = true;
                    }

                    $collTransactionsRelatedByUserId->getInternalIterator()->rewind();
                    return $collTransactionsRelatedByUserId;
                }

                if($partial && $this->collTransactionsRelatedByUserId) {
                    foreach($this->collTransactionsRelatedByUserId as $obj) {
                        if($obj->isNew()) {
                            $collTransactionsRelatedByUserId[] = $obj;
                        }
                    }
                }

                $this->collTransactionsRelatedByUserId = $collTransactionsRelatedByUserId;
                $this->collTransactionsRelatedByUserIdPartial = false;
            }
        }

        return $this->collTransactionsRelatedByUserId;
    }

    /**
     * Sets a collection of TransactionRelatedByUserId objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $transactionsRelatedByUserId A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return User The current object (for fluent API support)
     */
    public function setTransactionsRelatedByUserId(PropelCollection $transactionsRelatedByUserId, PropelPDO $con = null)
    {
        $transactionsRelatedByUserIdToDelete = $this->getTransactionsRelatedByUserId(new Criteria(), $con)->diff($transactionsRelatedByUserId);

        $this->transactionsRelatedByUserIdScheduledForDeletion = unserialize(serialize($transactionsRelatedByUserIdToDelete));

        foreach ($transactionsRelatedByUserIdToDelete as $transactionRelatedByUserIdRemoved) {
            $transactionRelatedByUserIdRemoved->setUserRelatedByUserId(null);
        }

        $this->collTransactionsRelatedByUserId = null;
        foreach ($transactionsRelatedByUserId as $transactionRelatedByUserId) {
            $this->addTransactionRelatedByUserId($transactionRelatedByUserId);
        }

        $this->collTransactionsRelatedByUserId = $transactionsRelatedByUserId;
        $this->collTransactionsRelatedByUserIdPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Transaction objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related Transaction objects.
     * @throws PropelException
     */
    public function countTransactionsRelatedByUserId(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
    {
        $partial = $this->collTransactionsRelatedByUserIdPartial && !$this->isNew();
        if (null === $this->collTransactionsRelatedByUserId || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collTransactionsRelatedByUserId) {
                return 0;
            }

            if($partial && !$criteria) {
                return count($this->getTransactionsRelatedByUserId());
            }
            $query = TransactionQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByUserRelatedByUserId($this)
                ->count($con);
        }

        return count($this->collTransactionsRelatedByUserId);
    }

    /**
     * Method called to associate a Transaction object to this object
     * through the Transaction foreign key attribute.
     *
     * @param    Transaction $l Transaction
     * @return User The current object (for fluent API support)
     */
    public function addTransactionRelatedByUserId(Transaction $l)
    {
        if ($this->collTransactionsRelatedByUserId === null) {
            $this->initTransactionsRelatedByUserId();
            $this->collTransactionsRelatedByUserIdPartial = true;
        }
        if (!in_array($l, $this->collTransactionsRelatedByUserId->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddTransactionRelatedByUserId($l);
        }

        return $this;
    }

    /**
     * @param	TransactionRelatedByUserId $transactionRelatedByUserId The transactionRelatedByUserId object to add.
     */
    protected function doAddTransactionRelatedByUserId($transactionRelatedByUserId)
    {
        $this->collTransactionsRelatedByUserId[]= $transactionRelatedByUserId;
        $transactionRelatedByUserId->setUserRelatedByUserId($this);
    }

    /**
     * @param	TransactionRelatedByUserId $transactionRelatedByUserId The transactionRelatedByUserId object to remove.
     * @return User The current object (for fluent API support)
     */
    public function removeTransactionRelatedByUserId($transactionRelatedByUserId)
    {
        if ($this->getTransactionsRelatedByUserId()->contains($transactionRelatedByUserId)) {
            $this->collTransactionsRelatedByUserId->remove($this->collTransactionsRelatedByUserId->search($transactionRelatedByUserId));
            if (null === $this->transactionsRelatedByUserIdScheduledForDeletion) {
                $this->transactionsRelatedByUserIdScheduledForDeletion = clone $this->collTransactionsRelatedByUserId;
                $this->transactionsRelatedByUserIdScheduledForDeletion->clear();
            }
            $this->transactionsRelatedByUserIdScheduledForDeletion[]= clone $transactionRelatedByUserId;
            $transactionRelatedByUserId->setUserRelatedByUserId(null);
        }

        return $this;
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id = null;
        $this->account_id = null;
        $this->domain_id = null;
        $this->deleted = null;
        $this->name = null;
        $this->firstname = null;
        $this->lastname = null;
        $this->phone = null;
        $this->manager_of = null;
        $this->is_admin = null;
        $this->email = null;
        $this->password_hash = null;
        $this->number = null;
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
            if ($this->collClockingsRelatedByCreatorId) {
                foreach ($this->collClockingsRelatedByCreatorId as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collClockingsRelatedByUserId) {
                foreach ($this->collClockingsRelatedByUserId as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collPropertyValues) {
                foreach ($this->collPropertyValues as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collSystemLogs) {
                foreach ($this->collSystemLogs as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collTransactionsRelatedByCreatorId) {
                foreach ($this->collTransactionsRelatedByCreatorId as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collTransactionsRelatedByUserId) {
                foreach ($this->collTransactionsRelatedByUserId as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->aAccount instanceof Persistent) {
              $this->aAccount->clearAllReferences($deep);
            }
            if ($this->aDomain instanceof Persistent) {
              $this->aDomain->clearAllReferences($deep);
            }

            $this->alreadyInClearAllReferencesDeep = false;
        } // if ($deep)

        if ($this->collClockingsRelatedByCreatorId instanceof PropelCollection) {
            $this->collClockingsRelatedByCreatorId->clearIterator();
        }
        $this->collClockingsRelatedByCreatorId = null;
        if ($this->collClockingsRelatedByUserId instanceof PropelCollection) {
            $this->collClockingsRelatedByUserId->clearIterator();
        }
        $this->collClockingsRelatedByUserId = null;
        if ($this->collPropertyValues instanceof PropelCollection) {
            $this->collPropertyValues->clearIterator();
        }
        $this->collPropertyValues = null;
        if ($this->collSystemLogs instanceof PropelCollection) {
            $this->collSystemLogs->clearIterator();
        }
        $this->collSystemLogs = null;
        if ($this->collTransactionsRelatedByCreatorId instanceof PropelCollection) {
            $this->collTransactionsRelatedByCreatorId->clearIterator();
        }
        $this->collTransactionsRelatedByCreatorId = null;
        if ($this->collTransactionsRelatedByUserId instanceof PropelCollection) {
            $this->collTransactionsRelatedByUserId->clearIterator();
        }
        $this->collTransactionsRelatedByUserId = null;
        $this->aAccount = null;
        $this->aDomain = null;
    }

    /**
     * return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(UserPeer::DEFAULT_STRING_FORMAT);
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
