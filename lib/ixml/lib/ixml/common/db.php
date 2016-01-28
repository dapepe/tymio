<?php
namespace Zeyon\Db;

// -------------------- Implementation --------------------

function connect($type, $server, $dbname, $username, $password) {
  $class = __NAMESPACE__.'\\'.($type === 'mysql' ? 'ConnMysql' : 'ConnPgsql');
  return new $class($server, $dbname, $username, $password);
}

abstract class Conn {
  protected $db;

  public $trans_count = 0;

  public static $default;

  abstract public function close();
  abstract public function executeQuery($statement);
  abstract public function resultQueryAll($statement, $assoc = false);
  abstract public function resultQueryList($statement);
  abstract public function resultQueryAV($statement);
  abstract public function getLastId();
  abstract public function getConnInfo();
  abstract public function getSize();
  abstract public function listTables($query = '');
  abstract public function listTablesInfo($query = '');
  abstract public function listFields($table);
  abstract public function listFieldsBin();
  abstract public function createOrder($sort, $desc);

  public function __construct() {
    self::$default OR self::$default = $this;
  }

  public function resultQuery($statement, $assoc = false) {
  	return ( $result = $this -> resultQueryAll($statement, $assoc) ) ? $result[0] : [];
  }
}

class ConnMysql extends Conn {
  public function __construct($server, $dbname, $username, $password) {
    if (!class_exists('mysqli', false))
      throw new \Exception('MySQLi extension not available');

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    list($host, $port) = \Zeyon\extractServer($server, 3306);

    $this -> db = new \mysqli($host, $username, $password, $dbname, $port);
    $this -> db -> set_charset('utf8');
    $this -> db -> real_query("SET sql_mode = 'ANSI_QUOTES,ERROR_FOR_DIVISION_BY_ZERO,IGNORE_SPACE,NO_BACKSLASH_ESCAPES,NO_ENGINE_SUBSTITUTION,ONLY_FULL_GROUP_BY,PIPES_AS_CONCAT,STRICT_TRANS_TABLES'");

    parent::__construct();
  }

  public function close() {
    $this -> db -> close();
  }

  public function executeQuery($statement) {
    if (preg_match('/;\s*$/D', $statement)) {
      $this -> db -> multi_query($statement);

      while ($this -> db -> more_results())
        $this -> db -> next_result();
    } else
      $this -> db -> real_query($statement);
  }

  public function resultQueryAll($statement, $assoc = false) {
    $type = $assoc ? MYSQLI_ASSOC : MYSQLI_NUM;

    if (method_exists( $res = $this -> db -> query($statement) , 'fetch_all'))
      $data = $res -> fetch_all($type);
    else {
      $data = [];

    	while ( $item = $res -> fetch_array($type) )
        $data[] = $item;
    }

    $res -> free();
    return $data;
  }

  public function resultQueryList($statement) {
    $res = $this -> db -> query($statement, MYSQLI_USE_RESULT);

    $list = [];

    while ( $item = $res -> fetch_row() )
      $list[] = $item[0];

    $res -> free();
    return $list;
  }

  public function resultQueryAV($statement) {
    $res = $this -> db -> query($statement, MYSQLI_USE_RESULT);

    $data = [];

    while ( $item = $res -> fetch_row() )
      $data[$item[0]] = $item[1];

    $res -> free();
    return $data;
  }

  public function getLastId() {
  	return $this -> db -> insert_id;
  }

  public function getConnInfo() {
    return 'MySQL '.$this -> db -> host_info.' ('.$this -> db -> server_info.')';
  }

  public function getSize() {
    return $this -> resultQuery('SELECT COALESCE(SUM("data_length" + "index_length"), 0) FROM "information_schema"."tables" WHERE "table_schema" = DATABASE()')[0];
  }

  public function listTables($query = '') {
    return $this -> resultQueryList('SELECT "table_name" FROM "information_schema"."tables" WHERE "table_schema" = DATABASE()'.($query == '' ? '' : ' AND '.whereSearch('table_name', $query)).' ORDER BY "table_name"');
  }

  public function listTablesInfo($query = '') {
  	$data = $this -> resultQueryAll('SELECT "table_name", "table_rows" FROM "information_schema"."tables" WHERE "table_schema" = DATABASE()'.($query == '' ? '' : ' AND '.whereSearch('table_name', $query)).' ORDER BY "table_name"');

  	foreach ($data as &$item)
  	  $item[] = $this -> resultQueryAll('SELECT "column_name", "column_type", TRIM("is_nullable" || \' \' || "column_key" || \' \' || "extra") FROM "information_schema"."columns" WHERE "table_schema" = DATABASE() AND "table_name" = '.quote($item[0]).' ORDER BY "ordinal_position"');

  	return $data;
  }

  public function listFields($table) {
    return $this -> resultQueryList('SELECT "column_name" FROM "information_schema"."columns" WHERE "table_schema" = DATABASE() AND "table_name" = '.quote($table).' ORDER BY "ordinal_position"');
  }

  public function listFieldsBin() {
    return $this -> resultQueryAll('SELECT "table_name", "column_name" FROM "information_schema"."columns" WHERE "table_schema" = DATABASE() AND "column_name" LIKE \'%binfile\'');
  }

  public function createOrder($sort, $desc) {
    $field = mask($sort);
    $postfix = $desc ? 'DESC' : 'ASC';

    return "$field IS NULL $postfix, ".($sort === 'ID' || strrchr($sort, '.') === '.ID'
         ? "$field $postfix"
         : "IF(COLLATION($field) = 'utf8_bin', NULL, $field) $postfix, IF(COLLATION($field) = 'utf8_bin', CONVERT($field USING utf8) COLLATE utf8_general_ci, NULL) $postfix");
  }
}

class ConnPgsql extends Conn {
  public function __construct($server, $dbname, $username, $password) {
    if (!function_exists('pg_connect'))
      throw new \Exception('PostgreSQL extension not available');

  	if ($server != '') {
  	  list($host, $port) = \Zeyon\extractServer($server, 5432);
  	  $server = 'host='.quote($host)." port=$port ";
  	}

    $this -> db = pg_connect($server.'user='.quote($username).' password='.quote($password).' dbname='.quote($dbname));
    pg_set_client_encoding($this -> db, 'UTF8');
    pg_query($this -> db, 'SET standard_conforming_strings = on');

    parent::__construct();
  }

  public function close() {
    pg_close($this -> db);
  }

  public function executeQuery($statement) {
    pg_query($this -> db, $statement);
  }

  public function resultQueryAll($statement, $assoc = false) {
    $res = pg_query($this -> db, $statement);

    if ($assoc)
      $data = pg_fetch_all($res) ?: [];
    else {
      $data = [];

      while ( $item = pg_fetch_row($res) )
        $data[] = $item;
    }

    pg_free_result($res);
    return $data;
  }

  public function resultQueryList($statement) {
    $list = pg_fetch_all_columns( $res = pg_query($this -> db, $statement) );
    pg_free_result($res);
    return $list;
  }

  public function resultQueryAV($statement) {
    $data = ( $keys = pg_fetch_all_columns( $res = pg_query($this -> db, $statement) ) )
          ? array_combine($keys, pg_fetch_all_columns($res, 1)) : [];

    pg_free_result($res);
    return $data;
  }

  public function getLastId() {
    return (int) $this -> resultQuery('SELECT LASTVAL()')[0];
  }

  public function getConnInfo() {
    $item = $this -> resultQuery('SHOW SERVER_VERSION');

    return 'PostgreSQL '
         . (( $host = pg_host($this -> db) ) === '' ? 'local' : "$host:".pg_port($this -> db))
         . " ($item[0])";
  }

  public function getSize() {
    return $this -> resultQuery('SELECT PG_DATABASE_SIZE(CURRENT_DATABASE())')[0];
  }

  public function listTables($query = '') {
    return $this -> resultQueryList('SELECT "relname" FROM "pg_class" WHERE "relname" NOT LIKE \'pg\\_%\' AND "relname" NOT LIKE \'sql\\_%\' AND "relpersistence" = \'p\' AND "relkind" = \'r\''.($query == '' ? '' : ' AND '.whereSearch('relname', $query)).' ORDER BY "relname"');
  }

  public function listTablesInfo($query = '') {
  	$data = $this -> resultQueryAll('SELECT "relname", "reltuples" FROM "pg_class" WHERE "relname" NOT LIKE \'pg\\_%\' AND "relname" NOT LIKE \'sql\\_%\' AND "relpersistence" = \'p\' AND "relkind" = \'r\''.($query == '' ? '' : ' AND '.whereSearch('relname', $query)).' ORDER BY "relname"');

  	foreach ($data as &$item)
  	  $item[] = $this -> resultQueryAll('SELECT "a"."attname", "t"."typname" || CASE WHEN "t"."typlen" > 0 THEN \'(\' || "t"."typlen" || \')\' ELSE \'\' END, CASE WHEN "a"."attnotnull" THEN \'NO\' ELSE \'YES\' END FROM "pg_attribute" "a", "pg_class" "c", "pg_type" "t" WHERE "a"."attrelid" = "c"."oid" AND "a"."atttypid" = "t"."oid" AND "a"."attnum" > 0 AND NOT "a"."attisdropped" AND "c"."relname" = '.quote($item[0]).' ORDER BY "a"."attnum"');

  	return $data;
  }

  public function listFields($table) {
    return $this -> resultQueryList('SELECT "a"."attname" FROM "pg_attribute" "a", "pg_class" "c" WHERE "a"."attrelid" = "c"."oid" AND "a"."attnum" > 0 AND NOT "a"."attisdropped" AND "c"."relname" = '.quote($table).' ORDER BY "a"."attnum"');
  }

  public function listFieldsBin() {
    return $this -> resultQueryAll('SELECT "c"."relname", "a"."attname" FROM "pg_attribute" "a", "pg_class" "c" WHERE "a"."attrelid" = "c"."oid" AND "a"."attname" LIKE \'%binfile\' AND "a"."attnum" > 0 AND NOT "a"."attisdropped" AND "c"."relname" NOT LIKE \'pg\\_%\' AND "c"."relname" NOT LIKE \'sql\\_%\' AND "c"."relpersistence" = \'p\' AND "c"."relkind" = \'r\'');
  }

  public function createOrder($sort, $desc) {
    return mask($sort).($desc ? ' DESC' : ' ASC');
  }
}

abstract class Child {
  protected $conn;

  public function __construct($conn = null) {
    $this -> conn = $conn ?: Conn::$default;
  }
}

class Transaction extends Child {
  public function __construct($conn = null) {
    parent::__construct($conn);

    $this -> conn -> trans_count++ || $this -> conn -> executeQuery('BEGIN');
  }

  public function commit() {
    --$this -> conn -> trans_count || $this -> conn -> executeQuery('COMMIT');
  }

  public function rollback() {
    --$this -> conn -> trans_count || $this -> conn -> executeQuery('ROLLBACK');
  }
}

abstract class Statement extends Child {
  abstract public function __toString();
}

abstract class Result extends Statement {
  public $orderby = [];
  public $limit = 0;
  public $offset = 0;

  public function createSubQuery($alias = '') {
  	return $alias == '' ? "($this)" : ["($this)", $alias];
  }

  public function createSubSelect($alias) {
    $db = new Select;
    $db -> table = $this -> createSubQuery($alias);
  	return $db;
  }

  public function result($assoc = false) {
    return $this -> conn -> resultQuery("$this", $assoc);
  }

  public function resultAll($assoc = false) {
    return $this -> conn -> resultQueryAll("$this", $assoc);
  }

  public function resultList() {
    return $this -> conn -> resultQueryList("$this");
  }

  public function resultAV() {
    return $this -> conn -> resultQueryAV("$this");
  }
}

class Select extends Result {
  public $distinct = false;
  public $fields = [];
  public $table = '';
  public $join = [];
  public $where = '';
  public $groupby = [];
  public $having = '';

  public function __toString() {
    $statement = 'SELECT ';
    $this -> distinct AND $statement .= 'DISTINCT ';
    $statement .= maskFields($this -> fields).' FROM '.$this -> createBase();

    if ( $orderby = (array) $this -> orderby ) {
      $list = [];

      foreach ($orderby as $sort => $desc)
        $list[] = $this -> conn -> createOrder($sort, $desc);

      $statement .= ' ORDER BY '.\join(', ', $list);
    } else if ((array) $this -> groupby && $this -> conn instanceof ConnMysql) // MySQL Performance Optimization
      $statement .= ' ORDER BY NULL';

    $this -> limit > 0 AND $statement .= " LIMIT $this->limit";
    $this -> offset > 0 AND $statement .= " OFFSET $this->offset";
    return $statement;
  }

  protected function createBase() {
  	$statement = maskTable($this -> table);
    $join = (array) $this -> join AND $statement .= ' '.\join(' ', $join);
    $this -> where == '' OR $statement .= " WHERE $this->where";
    $groupby = (array) $this -> groupby AND $statement .= ' GROUP BY '.\join(', ', array_map(__NAMESPACE__.'\mask', $groupby));
    $this -> having == '' OR $statement .= " HAVING $this->having";
    return $statement;
  }

  public function count() {
    $statement = 'SELECT ';

    if ( $wrap = $this -> distinct )
      $statement .= 'DISTINCT '.maskFields($this -> fields);
    else if ( $wrap = (array) $this -> groupby )
      $statement .= 'NULL';
    else
      $statement .= 'COUNT(*)';

    $statement .= ' FROM '.$this -> createBase();

    return $this -> conn -> resultQuery($wrap ? "SELECT COUNT(*) FROM ($statement) AS \"count\"" : $statement)[0];
  }

  public function resultView($offset = null, $limit = 100, $assoc = false) {
    $this -> limit = $limit;
    $this -> offset = +$offset;

    return $offset === null ? [$this -> count(), $this -> resultAll($assoc)] : $this -> resultAll($assoc);
  }

  public static function fetch($table, $fields, $where = '', $assoc = false, $conn = null) {
    $conn OR $conn = Conn::$default;
    return $conn -> resultQuery('SELECT '.maskFields($fields).' FROM '.maskTable($table).($where == '' ? '' : " WHERE $where").' LIMIT 1', $assoc);
  }

  public static function fetchRowById($table, $fields, $id, $assoc = false, $conn = null) {
    $conn OR $conn = Conn::$default;
  	return $conn -> resultQuery('SELECT '.maskFields($fields).' FROM '.escapeEntity($table).' WHERE "ID" = '.quote($id).' LIMIT 1', $assoc);
  }

  public static function lookup($table, $where = '', $conn = null) {
    $conn OR $conn = Conn::$default;

    if ( $item = $conn -> resultQuery('SELECT "ID" FROM '.maskTable($table).($where == '' ? '' : " WHERE $where").' LIMIT 1') )
      return $item[0];
  }
}

abstract class Set extends Result {
  public $all = true;
  public $subqueries = [];

  public function __toString() {
    $subqueries = $this -> subqueries;

    foreach ($subqueries as &$subquery)
      $subquery = "($subquery)";

    $statement = \join(' '.static::OPERATION.($this -> all ? ' ALL ' : ' '), $subqueries);

    if ( $orderby = (array) $this -> orderby ) {
      $list = [];

      foreach ($orderby as $sort => $desc)
        $list[] = $this -> conn -> createOrder($sort, $desc);

      $statement .= ' ORDER BY '.\join(', ', $list);
    }

    $this -> limit > 0 AND $statement .= " LIMIT $this->limit";
    $this -> offset > 0 AND $statement .= " OFFSET $this->offset";
    return $statement;
  }
}

class Union extends Set {
  const OPERATION = 'UNION';
}

class Intersect extends Set {
  const OPERATION = 'INTERSECT';
}

abstract class Exec extends Statement {
  public $table = '';

  public function execute() {
    $this -> conn -> executeQuery("$this");
  }
}

class Insert extends Exec {
  public $data = [];

  public function __toString() {
    return 'INSERT INTO '.escapeEntity($this -> table).' ('.\join(', ', array_map(__NAMESPACE__.'\escapeEntity', array_keys($this -> data))).') VALUES ('.\join(', ', array_map(__NAMESPACE__.'\quote', $this -> data)).')';
  }

  public function execute() {
    parent::execute();

    return $this -> conn -> getLastId();
  }

  public static function exec($table, $data, $conn = null) {
    $db = new self($conn);
    $db -> data = $data;
    $db -> table = $table;
    return $db -> execute();
  }
}

class Update extends Exec {
  public $data = [];
  public $where = '';

  public function __toString() {
    $data = [];

    foreach ($this -> data as $field => $value)
      $data[] = escapeEntity($field).' = '.quote($value);

    return 'UPDATE '.escapeEntity($this -> table).' SET '.\join(', ', $data).($this -> where == '' ? '' : " WHERE $this->where");
  }

  public static function exec($table, $data, $where = '', $conn = null) {
    $db = new self($conn);
    $db -> data = $data;
    $db -> table = $table;
    $db -> where = $where;
    $db -> execute();
  }
}

class Delete extends Exec {
  public $where = '';

  public function __toString() {
    return 'DELETE FROM '.escapeEntity($this -> table).($this -> where == '' ? '' : " WHERE $this->where");
  }

  public static function exec($table, $where = '', $conn = null) {
    $db = new self($conn);
    $db -> table = $table;
    $db -> where = $where;
    $db -> execute();
  }
}

function condition($operand1, $func, $operand2, $conn = null) {
  switch ($func) {
    case '=':
    case '!=':
    case '<>':
    case '<':
    case '<=':
    case '>':
    case '>=':
      break;

    case '!=*':
    case '<>*':
      $func = '<>';

    case '=*':
    case '_*':
    case '!_*':
    case '^*':
    case '!^*':
    case '$*':
    case '!$*':
    case '~*':
    case '!~*':
    case 'L*':
    case '!L*':
      $operand1 = "LOWER($operand1)";
      $operand2 = "LOWER($operand2)";

    case '_':
    case '!_':
    case '^':
    case '!^':
    case '$':
    case '!$':
    case '~':
    case '!~':
    case 'L':
    case '!L':
      switch ($func) {
        case '=*':
          $func = '=';

        case '<>':
          break 2;

        case '~':
        case '~*':
          $func = ($conn ?: Conn::$default) instanceof ConnMysql ? 'REGEXP' : '~';
          break 2;

        case '!~':
        case '!~*':
          $func = ($conn ?: Conn::$default) instanceof ConnMysql ? 'NOT REGEXP' : '!~';
          break 2;

        case 'L':
        case 'L*':
        case '!L':
        case '!L*':
          break;

        default:
          $operand2 = "REPLACE(REPLACE(REPLACE($operand2, '\\', '\\\\'), '_', '\\_'), '%', '\\%')";

          switch ($func) {
            case '_':
            case '_*':
            case '!_':
            case '!_*':
              $operand2 = "'%' || $operand2 || '%'";
              break;

            case '^':
            case '^*':
            case '!^':
            case '!^*':
              $operand2 = "$operand2 || '%'";
              break;

            case '$':
            case '$*':
            case '!$':
            case '!$*':
              $operand2 = "'%' || $operand2";
              break;
          }
      }

      $func = $func[0] === '!' ? 'NOT LIKE' : 'LIKE';
      break;

    default:
      $func = '=';
  }

  return "$operand1 $func $operand2";
}

function prepare($statement) {
  if (!preg_match_all('/(?:[$#]+\D|[^$#]+)+|([$#])(\\d+)/S', $statement, $matches, PREG_SET_ORDER))
    throw new \Exception('Invalid prepare statement');

  $statement = '';
  $args = func_get_args();

  foreach ($matches as $match)
    if (!isset($match[1]))
      $statement .= $match[0];
    else if (isset($args[ $pos = $match[2] + 1 ]))
      $statement .= $match[1] === '$' ? mask($args[$pos]) : quote($args[$pos]);

  return "($statement)";
}

function joinCross($table) {
  return 'CROSS JOIN '.maskTable($table);
}

function joinNatural($table) {
  return 'NATURAL JOIN '.maskTable($table);
}

function joinInner($table, $where) {
  $args = func_get_args();
  return 'INNER JOIN '.maskTable($table).' ON '.(isset($args[2]) ? whereFieldIs($where, $args[2]) : $where);
}

function joinLeft($table, $where) {
  $args = func_get_args();
  return 'LEFT OUTER JOIN '.maskTable($table).' ON '.(isset($args[2]) ? whereFieldIs($where, $args[2]) : $where);
}

function joinRight($table, $where) {
  $args = func_get_args();
  return 'RIGHT OUTER JOIN '.maskTable($table).' ON '.(isset($args[2]) ? whereFieldIs($where, $args[2]) : $where);
}

function whereAnd($where) {
  $args = func_get_args();

	$statement = '';

	foreach (isset($args[1]) ? $args : $where as $where)
	  $where == '' OR $statement .= " AND $where";

	return $statement === '' ? '' : "(TRUE$statement)";
}

function whereOr($where) {
  $args = func_get_args();

	$statement = '';

	foreach (isset($args[1]) ? $args : $where as $where)
	  $where == '' OR $statement .= " OR $where";

	return $statement === '' ? '' : "(FALSE$statement)";
}

function whereNot($where) {
  return $where == '' ? '' : "NOT ($where)";
}

function whereIs($field, $value, $func = '=', $conn = null) {
  return $value === null
       ? mask($field).($func === '=' ? ' IS NULL' : ' IS NOT NULL')
       : condition(mask($field), $func, quote($value), $conn);
}

function whereFieldIs($field1, $field2, $func = '=', $conn = null) {
  return condition(mask($field1), $func, mask($field2), $conn);
}

function whereAll($field, $subquery, $func = '=') {
  switch ($func) {
    case '=':
    case '!=':
    case '<>':
    case '<':
    case '>':
    case '<=':
    case '>=':
      break;

    default:
      $func = '=';
  }

  return mask($field)." $func ALL ($subquery)";
}

function whereAny($field, $subquery, $func = '=') {
  switch ($func) {
    case '=':
    case '!=':
    case '<>':
    case '<':
    case '>':
    case '<=':
    case '>=':
      break;

    default:
      $func = '=';
  }

  return mask($field)." $func ANY ($subquery)";
}

function whereExists($subquery) {
  return "EXISTS ($subquery)";
}

function whereNotExists($subquery) {
  return "NOT EXISTS ($subquery)";
}

function whereIn($field, $subquery) {
  if (is_array($subquery)) {
    if (!$subquery)
      return 'FALSE';

    $subquery = \join(', ', array_map(__NAMESPACE__.'\quote', $subquery));
  }

  return mask($field)." IN ($subquery)";
}

function whereNotIn($field, $subquery) {
  if (is_array($subquery)) {
    if (!$subquery)
      return 'TRUE';

    $subquery = \join(', ', array_map(__NAMESPACE__.'\quote', $subquery));
  }

  return mask($field)." NOT IN ($subquery)";
}

function whereSearch($fields, $query) {
  if ($query == '')
    return '';

  $fields = array_map(__NAMESPACE__.'\mask', (array) $fields);

  $statement = '';

  foreach (\Zeyon\tokenizeQuery(str_replace("'", "''", escapeLike($query))) as $token) {
  	$statement .= ' AND (FALSE';

  	foreach ($fields as $field)
  	  $statement .= " OR LOWER($field) LIKE '%$token%'";

  	$statement .= ')';
  }

  return "(TRUE$statement)";
}

function quote($value) {
  return $value === null ? 'NULL' : "'".str_replace("'", "''", $value)."'";
}

function mask($entity) {
  if ($entity == '')
    return 'NULL';

  static $used;

  if (isset($used[$entity]))
    return $used[$entity];

  if (strrpos($entity, ')') !== false)
    return $entity;

  $entity1 = strtok(str_replace('"', '""', $entity), '.');
  $entity2 = strtok('');

  return $used[$entity] = $entity2 == '' ? "\"$entity1\"" : "\"$entity1\".\"$entity2\"";
}

function maskFields($fields) {
  if (! $fields = (array) $fields )
    return '*';

  foreach ($fields as &$field) {
    $def = (array) $field;
    $field = mask($def[0]);
    isset($def[1]) AND ( $alias = $def[1] ) != '' AND $field .= ' AS '.escapeEntity($alias);
  }

  return \join(', ', $fields);
}

function maskTable($table) {
  $table = (array) $table;
  $statement = mask( $entity = $table[0] );

  if (!isset($table[1]))
    $statement .= ' '.escapeEntity(( $pos = strpos($entity, '2') ) === false ? substr($entity, 0, 3) : "$entity[0]2".$entity[$pos + 1]);
  else if (( $alias = $table[1] ) != '')
    $statement .= ' '.escapeEntity($alias);

  return $statement;
}

function escapeEntity($entity) {
  return '"'.str_replace('"', '""', $entity).'"';
}

function escapeLike($value) {
  return addcslashes($value, '\\_%');
}