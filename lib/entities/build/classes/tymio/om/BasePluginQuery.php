<?php


/**
 * Base class that represents a query for the 'plugin' table.
 *
 *
 *
 * @method PluginQuery orderById($order = Criteria::ASC) Order by the id column
 * @method PluginQuery orderByAccountId($order = Criteria::ASC) Order by the account_id column
 * @method PluginQuery orderByEntity($order = Criteria::ASC) Order by the entity column
 * @method PluginQuery orderByEvent($order = Criteria::ASC) Order by the event column
 * @method PluginQuery orderByPriority($order = Criteria::ASC) Order by the priority column
 * @method PluginQuery orderByIdentifier($order = Criteria::ASC) Order by the identifier column
 * @method PluginQuery orderByName($order = Criteria::ASC) Order by the name column
 * @method PluginQuery orderByCode($order = Criteria::ASC) Order by the code column
 * @method PluginQuery orderByActive($order = Criteria::ASC) Order by the active column
 * @method PluginQuery orderByInterval($order = Criteria::ASC) Order by the interval column
 * @method PluginQuery orderByStart($order = Criteria::ASC) Order by the start column
 * @method PluginQuery orderByLastExecutionTime($order = Criteria::ASC) Order by the last_execution_time column
 *
 * @method PluginQuery groupById() Group by the id column
 * @method PluginQuery groupByAccountId() Group by the account_id column
 * @method PluginQuery groupByEntity() Group by the entity column
 * @method PluginQuery groupByEvent() Group by the event column
 * @method PluginQuery groupByPriority() Group by the priority column
 * @method PluginQuery groupByIdentifier() Group by the identifier column
 * @method PluginQuery groupByName() Group by the name column
 * @method PluginQuery groupByCode() Group by the code column
 * @method PluginQuery groupByActive() Group by the active column
 * @method PluginQuery groupByInterval() Group by the interval column
 * @method PluginQuery groupByStart() Group by the start column
 * @method PluginQuery groupByLastExecutionTime() Group by the last_execution_time column
 *
 * @method PluginQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method PluginQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method PluginQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method PluginQuery leftJoinAccount($relationAlias = null) Adds a LEFT JOIN clause to the query using the Account relation
 * @method PluginQuery rightJoinAccount($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Account relation
 * @method PluginQuery innerJoinAccount($relationAlias = null) Adds a INNER JOIN clause to the query using the Account relation
 *
 * @method Plugin findOne(PropelPDO $con = null) Return the first Plugin matching the query
 * @method Plugin findOneOrCreate(PropelPDO $con = null) Return the first Plugin matching the query, or a new Plugin object populated from the query conditions when no match is found
 *
 * @method Plugin findOneByAccountId(int $account_id) Return the first Plugin filtered by the account_id column
 * @method Plugin findOneByEntity(string $entity) Return the first Plugin filtered by the entity column
 * @method Plugin findOneByEvent(string $event) Return the first Plugin filtered by the event column
 * @method Plugin findOneByPriority(int $priority) Return the first Plugin filtered by the priority column
 * @method Plugin findOneByIdentifier(string $identifier) Return the first Plugin filtered by the identifier column
 * @method Plugin findOneByName(string $name) Return the first Plugin filtered by the name column
 * @method Plugin findOneByCode(string $code) Return the first Plugin filtered by the code column
 * @method Plugin findOneByActive(int $active) Return the first Plugin filtered by the active column
 * @method Plugin findOneByInterval(int $interval) Return the first Plugin filtered by the interval column
 * @method Plugin findOneByStart(int $start) Return the first Plugin filtered by the start column
 * @method Plugin findOneByLastExecutionTime(string $last_execution_time) Return the first Plugin filtered by the last_execution_time column
 *
 * @method array findById(int $id) Return Plugin objects filtered by the id column
 * @method array findByAccountId(int $account_id) Return Plugin objects filtered by the account_id column
 * @method array findByEntity(string $entity) Return Plugin objects filtered by the entity column
 * @method array findByEvent(string $event) Return Plugin objects filtered by the event column
 * @method array findByPriority(int $priority) Return Plugin objects filtered by the priority column
 * @method array findByIdentifier(string $identifier) Return Plugin objects filtered by the identifier column
 * @method array findByName(string $name) Return Plugin objects filtered by the name column
 * @method array findByCode(string $code) Return Plugin objects filtered by the code column
 * @method array findByActive(int $active) Return Plugin objects filtered by the active column
 * @method array findByInterval(int $interval) Return Plugin objects filtered by the interval column
 * @method array findByStart(int $start) Return Plugin objects filtered by the start column
 * @method array findByLastExecutionTime(string $last_execution_time) Return Plugin objects filtered by the last_execution_time column
 *
 * @package    propel.generator.tymio.om
 */
abstract class BasePluginQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BasePluginQuery object.
     *
     * @param     string $dbName The dabase name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'tymio', $modelName = 'Plugin', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new PluginQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   PluginQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return PluginQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof PluginQuery) {
            return $criteria;
        }
        $query = new PluginQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param     PropelPDO $con an optional connection object
     *
     * @return   Plugin|Plugin[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = PluginPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is alredy in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(PluginPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }
        $this->basePreSelect($con);
        if ($this->formatter || $this->modelAlias || $this->with || $this->select
         || $this->selectColumns || $this->asColumns || $this->selectModifiers
         || $this->map || $this->having || $this->joins) {
            return $this->findPkComplex($key, $con);
        } else {
            return $this->findPkSimple($key, $con);
        }
    }

    /**
     * Alias of findPk to use instance pooling
     *
     * @param     mixed $key Primary key to use for the query
     * @param     PropelPDO $con A connection object
     *
     * @return                 Plugin A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneById($key, $con = null)
     {
        return $this->findPk($key, $con);
     }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     PropelPDO $con A connection object
     *
     * @return                 Plugin A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id`, `account_id`, `entity`, `event`, `priority`, `identifier`, `name`, `code`, `active`, `interval`, `start`, `last_execution_time` FROM `plugin` WHERE `id` = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $obj = new Plugin();
            $obj->hydrate($row);
            PluginPeer::addInstanceToPool($obj, (string) $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     PropelPDO $con A connection object
     *
     * @return Plugin|Plugin[]|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $stmt = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($stmt);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     PropelPDO $con an optional connection object
     *
     * @return PropelObjectCollection|Plugin[]|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection($this->getDbName(), Propel::CONNECTION_READ);
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $stmt = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($stmt);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return PluginQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(PluginPeer::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return PluginQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(PluginPeer::ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id >= 12
     * $query->filterById(array('max' => 12)); // WHERE id <= 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PluginQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(PluginPeer::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(PluginPeer::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PluginPeer::ID, $id, $comparison);
    }

    /**
     * Filter the query on the account_id column
     *
     * Example usage:
     * <code>
     * $query->filterByAccountId(1234); // WHERE account_id = 1234
     * $query->filterByAccountId(array(12, 34)); // WHERE account_id IN (12, 34)
     * $query->filterByAccountId(array('min' => 12)); // WHERE account_id >= 12
     * $query->filterByAccountId(array('max' => 12)); // WHERE account_id <= 12
     * </code>
     *
     * @see       filterByAccount()
     *
     * @param     mixed $accountId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PluginQuery The current query, for fluid interface
     */
    public function filterByAccountId($accountId = null, $comparison = null)
    {
        if (is_array($accountId)) {
            $useMinMax = false;
            if (isset($accountId['min'])) {
                $this->addUsingAlias(PluginPeer::ACCOUNT_ID, $accountId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($accountId['max'])) {
                $this->addUsingAlias(PluginPeer::ACCOUNT_ID, $accountId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PluginPeer::ACCOUNT_ID, $accountId, $comparison);
    }

    /**
     * Filter the query on the entity column
     *
     * Example usage:
     * <code>
     * $query->filterByEntity('fooValue');   // WHERE entity = 'fooValue'
     * $query->filterByEntity('%fooValue%'); // WHERE entity LIKE '%fooValue%'
     * </code>
     *
     * @param     string $entity The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PluginQuery The current query, for fluid interface
     */
    public function filterByEntity($entity = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($entity)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $entity)) {
                $entity = str_replace('*', '%', $entity);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PluginPeer::ENTITY, $entity, $comparison);
    }

    /**
     * Filter the query on the event column
     *
     * Example usage:
     * <code>
     * $query->filterByEvent('fooValue');   // WHERE event = 'fooValue'
     * $query->filterByEvent('%fooValue%'); // WHERE event LIKE '%fooValue%'
     * </code>
     *
     * @param     string $event The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PluginQuery The current query, for fluid interface
     */
    public function filterByEvent($event = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($event)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $event)) {
                $event = str_replace('*', '%', $event);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PluginPeer::EVENT, $event, $comparison);
    }

    /**
     * Filter the query on the priority column
     *
     * Example usage:
     * <code>
     * $query->filterByPriority(1234); // WHERE priority = 1234
     * $query->filterByPriority(array(12, 34)); // WHERE priority IN (12, 34)
     * $query->filterByPriority(array('min' => 12)); // WHERE priority >= 12
     * $query->filterByPriority(array('max' => 12)); // WHERE priority <= 12
     * </code>
     *
     * @param     mixed $priority The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PluginQuery The current query, for fluid interface
     */
    public function filterByPriority($priority = null, $comparison = null)
    {
        if (is_array($priority)) {
            $useMinMax = false;
            if (isset($priority['min'])) {
                $this->addUsingAlias(PluginPeer::PRIORITY, $priority['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($priority['max'])) {
                $this->addUsingAlias(PluginPeer::PRIORITY, $priority['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PluginPeer::PRIORITY, $priority, $comparison);
    }

    /**
     * Filter the query on the identifier column
     *
     * Example usage:
     * <code>
     * $query->filterByIdentifier('fooValue');   // WHERE identifier = 'fooValue'
     * $query->filterByIdentifier('%fooValue%'); // WHERE identifier LIKE '%fooValue%'
     * </code>
     *
     * @param     string $identifier The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PluginQuery The current query, for fluid interface
     */
    public function filterByIdentifier($identifier = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($identifier)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $identifier)) {
                $identifier = str_replace('*', '%', $identifier);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PluginPeer::IDENTIFIER, $identifier, $comparison);
    }

    /**
     * Filter the query on the name column
     *
     * Example usage:
     * <code>
     * $query->filterByName('fooValue');   // WHERE name = 'fooValue'
     * $query->filterByName('%fooValue%'); // WHERE name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $name The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PluginQuery The current query, for fluid interface
     */
    public function filterByName($name = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($name)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $name)) {
                $name = str_replace('*', '%', $name);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PluginPeer::NAME, $name, $comparison);
    }

    /**
     * Filter the query on the code column
     *
     * Example usage:
     * <code>
     * $query->filterByCode('fooValue');   // WHERE code = 'fooValue'
     * $query->filterByCode('%fooValue%'); // WHERE code LIKE '%fooValue%'
     * </code>
     *
     * @param     string $code The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PluginQuery The current query, for fluid interface
     */
    public function filterByCode($code = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($code)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $code)) {
                $code = str_replace('*', '%', $code);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PluginPeer::CODE, $code, $comparison);
    }

    /**
     * Filter the query on the active column
     *
     * Example usage:
     * <code>
     * $query->filterByActive(1234); // WHERE active = 1234
     * $query->filterByActive(array(12, 34)); // WHERE active IN (12, 34)
     * $query->filterByActive(array('min' => 12)); // WHERE active >= 12
     * $query->filterByActive(array('max' => 12)); // WHERE active <= 12
     * </code>
     *
     * @param     mixed $active The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PluginQuery The current query, for fluid interface
     */
    public function filterByActive($active = null, $comparison = null)
    {
        if (is_array($active)) {
            $useMinMax = false;
            if (isset($active['min'])) {
                $this->addUsingAlias(PluginPeer::ACTIVE, $active['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($active['max'])) {
                $this->addUsingAlias(PluginPeer::ACTIVE, $active['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PluginPeer::ACTIVE, $active, $comparison);
    }

    /**
     * Filter the query on the interval column
     *
     * Example usage:
     * <code>
     * $query->filterByInterval(1234); // WHERE interval = 1234
     * $query->filterByInterval(array(12, 34)); // WHERE interval IN (12, 34)
     * $query->filterByInterval(array('min' => 12)); // WHERE interval >= 12
     * $query->filterByInterval(array('max' => 12)); // WHERE interval <= 12
     * </code>
     *
     * @param     mixed $interval The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PluginQuery The current query, for fluid interface
     */
    public function filterByInterval($interval = null, $comparison = null)
    {
        if (is_array($interval)) {
            $useMinMax = false;
            if (isset($interval['min'])) {
                $this->addUsingAlias(PluginPeer::INTERVAL, $interval['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($interval['max'])) {
                $this->addUsingAlias(PluginPeer::INTERVAL, $interval['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PluginPeer::INTERVAL, $interval, $comparison);
    }

    /**
     * Filter the query on the start column
     *
     * Example usage:
     * <code>
     * $query->filterByStart(1234); // WHERE start = 1234
     * $query->filterByStart(array(12, 34)); // WHERE start IN (12, 34)
     * $query->filterByStart(array('min' => 12)); // WHERE start >= 12
     * $query->filterByStart(array('max' => 12)); // WHERE start <= 12
     * </code>
     *
     * @param     mixed $start The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PluginQuery The current query, for fluid interface
     */
    public function filterByStart($start = null, $comparison = null)
    {
        if (is_array($start)) {
            $useMinMax = false;
            if (isset($start['min'])) {
                $this->addUsingAlias(PluginPeer::START, $start['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($start['max'])) {
                $this->addUsingAlias(PluginPeer::START, $start['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PluginPeer::START, $start, $comparison);
    }

    /**
     * Filter the query on the last_execution_time column
     *
     * Example usage:
     * <code>
     * $query->filterByLastExecutionTime(1234); // WHERE last_execution_time = 1234
     * $query->filterByLastExecutionTime(array(12, 34)); // WHERE last_execution_time IN (12, 34)
     * $query->filterByLastExecutionTime(array('min' => 12)); // WHERE last_execution_time >= 12
     * $query->filterByLastExecutionTime(array('max' => 12)); // WHERE last_execution_time <= 12
     * </code>
     *
     * @param     mixed $lastExecutionTime The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PluginQuery The current query, for fluid interface
     */
    public function filterByLastExecutionTime($lastExecutionTime = null, $comparison = null)
    {
        if (is_array($lastExecutionTime)) {
            $useMinMax = false;
            if (isset($lastExecutionTime['min'])) {
                $this->addUsingAlias(PluginPeer::LAST_EXECUTION_TIME, $lastExecutionTime['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($lastExecutionTime['max'])) {
                $this->addUsingAlias(PluginPeer::LAST_EXECUTION_TIME, $lastExecutionTime['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PluginPeer::LAST_EXECUTION_TIME, $lastExecutionTime, $comparison);
    }

    /**
     * Filter the query by a related Account object
     *
     * @param   Account|PropelObjectCollection $account The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 PluginQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAccount($account, $comparison = null)
    {
        if ($account instanceof Account) {
            return $this
                ->addUsingAlias(PluginPeer::ACCOUNT_ID, $account->getId(), $comparison);
        } elseif ($account instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(PluginPeer::ACCOUNT_ID, $account->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByAccount() only accepts arguments of type Account or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Account relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return PluginQuery The current query, for fluid interface
     */
    public function joinAccount($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Account');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'Account');
        }

        return $this;
    }

    /**
     * Use the Account relation Account object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   AccountQuery A secondary query class using the current class as primary query
     */
    public function useAccountQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinAccount($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Account', 'AccountQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   Plugin $plugin Object to remove from the list of results
     *
     * @return PluginQuery The current query, for fluid interface
     */
    public function prune($plugin = null)
    {
        if ($plugin) {
            $this->addUsingAlias(PluginPeer::ID, $plugin->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

}
