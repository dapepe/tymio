<?php
namespace Zeyon\Mongo;

// -------------------- Implementation --------------------

class Conn {
  protected $db;

  public function __construct($server, $dbname, $username = '', $password = '') {
    if (!class_exists('MongoClient', false))
      throw new \Exception('MongoDB extension not available');

    $options = ['db' => $dbname];

    if ($username != '') {
      $options['username'] = $username;
      $options['password'] = $password;
    }

    $this -> db = (new \MongoClient('mongodb://'.($server == '' ? 'localhost:27017' : $server), $options)) -> selectDB($dbname);
  }

  public function insert($collection, $data) {
    isset($data['_id']) AND $id = new \MongoId( $id =& $data['_id'] );

    $this -> db -> selectCollection($collection) -> insert($data);
    return $data['_id'] -> id;
  }

  public function update($collection, $data, $query = [], $multiple = false) {
    isset($data['_id']) AND $id = new \MongoId( $id =& $data['_id'] );

    $this -> db -> selectCollection($collection) -> update($query, $data, ['multiple' => $multiple]);
  }

  public function save($collection, $data, $id = '') {
    if ($id != '')
      $data['_id'] = new \MongoId($id);
    else if (isset($data['_id']))
      $id = new \MongoId( $id =& $data['_id'] );

    $this -> db -> selectCollection($collection) -> save($data);
    return $data['_id'] -> id;
  }

  public function remove($collection, $query = [], $justone = false) {
    $this -> db -> selectCollection($collection) -> remove($query, ['justOne' => $justone]);
  }

  public function removeById($collection, $id) {
    $this -> remove($collection, ['_id' => new \MongoId($id)], true);
  }

  public function distinct($collection, $field, $query = []) {
    return $this -> db -> selectCollection($collection) -> distinct($field, $query);
  }

  public function count($collection, $query = []) {
    return $this -> db -> selectCollection($collection) -> count($query);
  }

  public function find($collection, $query = [], $fields = [], $sort = [], $limit = 0, $offset = 0) {
    $data = [];

    $cursor = $this -> db -> selectCollection($collection) -> find($query, $fields);

    if ($sort) {
      foreach ($sort as &$desc)
        $desc = $desc ? -1 : 1;

      $cursor = $cursor -> sort($sort);
    }

    $limit > 0 AND $cursor = $cursor -> limit($limit);
    $offset > 0 AND $cursor = $cursor -> skip($offset);

    foreach ($cursor as $id => $item) {
      $item['_id'] = $id;
      $data[$id] = $item;
    }

    return $data;
  }

  public function findOne($query = [], $fields = []) {
    if ( $result = $this -> db -> selectCollection($collection) -> findOne($query, $fields) ) {
      $id =& $item['_id'];
      $id = $id -> id;
      return $result;
    }

    return [];
  }

  public function findOneById($id, $fields = []) {
    return $this -> findOne(['_id' => new \MongoId($id)], $fields);
  }

  public function lookup($query = []) {
    if ( $item = $this -> db -> selectCollection($collection) -> findOne($query, ['_id' => true]) )
      return $item['_id'] -> id;
  }
}

function queryAnd($query) {
  $args = func_get_args();
  return ( $query = isset($args[1]) ? $args : $query ) ? ['$and' => $query] : [];
}

function queryOr($query) {
  $args = func_get_args();
  return ( $query = isset($args[1]) ? $args : $query ) ? ['$or' => $query] : [];
}

function queryIs($field, $value, $func = '=') {
  switch ($func) {
    case '~':
    case '!~':
      break;

    default:
      $field === '_id' AND $value = new \MongoId($value);
  }

  switch ($func) {
    case '=':
      break;

    case '!=':
    case '<>':
      return [$field => ['$ne' => $value]];

    case '<':
      return [$field => ['$lt' => $value]];

    case '<=':
      return [$field => ['$lte' => $value]];

    case '>':
      return [$field => ['$gt' => $value]];

    case '>=':
      return [$field => ['$gte' => $value]];

    case '~':
      return [$field => ['$regex' => new \MongoRegex($value)]];

    case '!~':
      return [$field => ['$not' => ['$regex' => new \MongoRegex($value)]]];
  }

  return [$field => $value];
}

function queryValues($type, $field, $values) {
  if ($field === '_id')
    foreach ($values as &$value)
      $value = new \MongoId($value);

  return [$field => [$type => $values]];
}

function queryAll($field, $values) {
  return queryValues('$all', $field, $values);
}

function queryExists($field) {
  return [$field => ['$exists' => true]];
}

function queryNotExists($field) {
  return [$field => ['$exists' => false]];
}

function queryIn($field, $values) {
  return queryValues('$in', $field, $values);
}

function queryNotIn($field, $values) {
  return queryValues('$nin', $field, $values);
}