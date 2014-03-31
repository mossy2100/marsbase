<?php
namespace AstroMultimedia\Drupal;

use stdClass;
use Exception;

/**
 * Relation class.
 */
class Relation extends Entity {

  /**
   * The entity type.
   *
   * @var string
   */
  const ENTITY_TYPE = 'relation';

  /**
   * The database table name.
   *
   * @var string
   */
  const DB_TABLE = 'relation';

  /**
   * The primary key
   *
   * @var string
   */
  const PRIMARY_KEY = 'rid';

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Magic methods

  /**
   * Constructor.
   */
  protected function __construct() {
    return parent::__construct();
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Create/delete

  /**
   * Create a new Relation object.
   *
   * @param mixed $param
   * @return Relation
   */
  public static function create($param = NULL) {
    // Get the class of the object we want to create:
    $class = get_called_class();

    if (is_null($param)) {
      // Create new relation:
      $relation_obj = new $class;

      // It's new:
      $relation_obj->entity->is_new = TRUE;

      // The relation is valid without a rid:
      $relation_obj->valid = TRUE;
    }
    elseif (is_pint($param)) {
      // rid provided:
      $rid = (int) $param;

      // Only create the new relation if not already in the cache:
      if (self::inCache($rid)) {
        return self::getFromCache($rid);
      }
      else {
        // Create new relation:
        $relation_obj = new $class;

        // Set the rid:
        $relation_obj->entity->rid = $rid;
      }
    }
    elseif ($param instanceof stdClass) {
      // Drupal relation object provided:
      $relation = $param;

      // Get the object from the cache if possible:
      if (isset($relation->rid) && $relation->rid && self::inCache($relation->rid)) {
        $relation_obj = self::getFromCache($relation->rid);
      }
      else {
        $relation_obj = new $class;
      }

      // Reference the provided entity object:
      $relation_obj->entity = $relation;

      // Make sure we mark the relation as loaded. It may not have been saved yet, and if we load it, any changes to the
      // relation entity would be overwritten.
      $relation_obj->loaded = TRUE;
    }

    // If we have a relation object, add to cache and return:
    if (isset($relation_obj)) {
      $relation_obj->addToCache();
      return $relation_obj;
    }

    throw new Exception("Invalid parameter.");
  }

  /**
   * Delete a relation.
   */
  public function delete() {
    relation_delete($this->rid());
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Load/save

  /**
   * Load the relation object.
   *
   * @param bool $force
   * @return Relation
   */
  public function load($force = FALSE) {
    // Avoid reloading:
    if ($this->loaded && !$force) {
      return $this;
    }

    // Default result:
    $relation = FALSE;

    // If we have a rid, try to load the relation:
    if ($this->entity->rid) {
      // If we want to force a reload, remove the relation from the entity cache:
      if ($force) {
        entity_get_controller(self::ENTITY_TYPE)->resetCache([$this->entity->rid]);
      }
      // Load by rid. Drupal caching will prevent reloading of the same relation.
      $relation = relation_load($this->entity->rid);
    }

    // Set the valid flag:
    $this->valid = (bool) $relation;

    // If the relation was successfully loaded, update fields:
    if ($relation) {
      $this->entity = $relation;
      $this->loaded = TRUE;
    }

    return $this;
  }

  /**
   * Save the relation object.
   *
   * @return Relation
   */
  public function save() {
    // Ensure the relation has been loaded:
    $this->load();

    // Save the relation:
    relation_save($this->entity);

    // It's not new any more:
    $this->entity->is_new = FALSE;

    // In case the relation is new, add it to the cache:
    $this->addToCache();

    return $this;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Get/set methods

  /**
   * Get the rid.
   *
   * @return int
   */
  public function rid() {
    return isset($this->entity->rid) ? (int) $this->entity->rid : NULL;
  }

  /**
   * Set the rid.
   *
   * @param int $rid
   * @return Relation
   */
  public function setRid($rid) {
    $this->entity->rid = isset($rid) ? (int) $rid : NULL;
    // Add the relation object to the cache if not already:
    $this->addToCache();
    return $this;
  }

  /**
   * Get the relation object.
   *
   * @return stdClass
   */
  public function relation() {
    return $this->entity();
  }

  /**
   * Get the uid of the user who created the relation.
   *
   * @return int
   */
  public function uid() {
    return (int) $this->field('uid');
  }

  /**
   * Set the uid of the user who created the relation.
   *
   * @param int
   * @return Relation
   */
  public function setUid($uid) {
    return $this->setField('uid', $uid);
  }

  /**
   * Get the relation's creator.
   *
   * @return User
   */
  public function creator() {
    return User::create($this->uid());
  }

  /**
   * Get the relation type.
   *
   * @return string
   */
  public function relationType() {
    return $this->field('relation_type');
  }

  /**
   * Get an endpoint.
   *
   * @param string $lang
   * @param int $delta
   * @return object|null
   */
  public function endpoint($delta, $lang = LANGUAGE_NONE) {
    $this->load();
    return isset($this->entity->endpoints[$lang][$delta]) ? ((object) $this->entity->endpoints[$lang][$delta]) : NULL;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Static methods for working with binary relationships.

  /**
   * Create a new binary relation.
   *
   * @static
   * @param string $relationship_type
   * @param Entity $entity0
   * @param Entity $entity1
   * @param bool $save
   *   Whether or not to save the relationship. Defaults to TRUE.
   * @return Relation
   */
  public static function createNewBinary($relationship_type, $entity0, $entity1, $save = TRUE) {
    $entity_type0 = $entity0->entityType();
    $entity_id0 = $entity0->id();
    $entity_type1 = $entity1->entityType();
    $entity_id1 = $entity1->id();

    // Get the called class:
    $class = get_called_class();

    $endpoints = array(
      array(
        'entity_type' => $entity_type0,
        'entity_id'   => $entity_id0,
      ),
      array(
        'entity_type' => $entity_type1,
        'entity_id'   => $entity_id1,
      ),
    );

    // Create the relation entity:
    $rel_entity = relation_create($relationship_type, $endpoints);

    // Create the Relation object:
    $relation = $class::create($rel_entity);

    // Save if requested:
    if ($save) {
      $relation->save();
    }

    return $relation;
  }

  /**
   * Search for relationships matching the provided parameters.
   *
   * @param string $relationship_type
   * @param Entity $entity0
   *   Use NULL to match all.
   * @param Entity $entity1
   *   Use NULL to match all.
   * @param null|int $offset
   * @param null|int $limit
   * @return array
   */
  public static function searchBinary($relationship_type, $entity0 = NULL, $entity1 = NULL, $offset = NULL, $limit = NULL, $orderByField = NULL, $orderByDirection = NULL) {
    // Look for a relationship record of the specified type:
    $q = db_select('relation', 'r')
      ->fields('r', array('rid'))
      ->condition('relation_type', $relationship_type);

    // Add conditions:
    if ($entity0 !== NULL) {
      $q->join('field_data_endpoints', 'fde0', "r.rid = fde0.entity_id AND fde0.endpoints_r_index = 0");
      $q->condition('fde0.endpoints_entity_type', $entity0->entityType())
        ->condition('fde0.endpoints_entity_id', $entity0->id());
    }
    if ($entity1 !== NULL) {
      $q->join('field_data_endpoints', 'fde1', "r.rid = fde1.entity_id AND fde1.endpoints_r_index = 1");
      $q->condition('fde1.endpoints_entity_type', $entity1->entityType())
        ->condition('fde1.endpoints_entity_id', $entity1->id());
    }

    // Add LIMIT clause:
    if ($offset !== NULL && $limit !== NULL) {
      $q->range($offset, $limit);
    }

    // Add ORDER BY clause:
    if ($orderByField === NULL) {
      $orderByField = 'r.changed';
    }
    if ($orderByDirection === NULL) {
      $orderByDirection = 'DESC';
    }
    $q->orderBy($orderByField, $orderByDirection);

    // Get the called class:
    $class = get_called_class();

    // Get the relationships:
    $rs = $q->execute();
    $results = array();
    foreach ($rs as $rec) {
      $results[] = $class::create($rec->rid);
    }
    return $results;
  }

  /**
   * Update or create a relationship.
   *
   * @param string $relationship_type
   * @param Entity $entity0
   * @param Entity $entity1
   * @param bool $save
   *   Whether or not to save the relationship. Defaults to TRUE.
   * @return Relation
   */
  public static function updateBinary($relationship_type, $entity0, $entity1, $save = TRUE) {
    // Get the called class:
    $class = get_called_class();

    // See if the relationship already exists:
    $rels = $class::searchBinary($relationship_type, $entity0, $entity1);

    if ($rels) {
      // Update the relationship. We really just want to update the changed timestamp, so let's just load and save it.
      $rel = $rels[0];
      $rel->load();

      if ($save) {
        $rel->save();
      }
    }
    else {
      // Create a new relationship:
      $rel = $class::createNewBinary($relationship_type, $entity0, $entity1, $save);
    }

    return $rel;
  }

  /**
   * Delete relationships.
   *
   * @param string $relationship_type
   * @param Entity $entity0
   * @param Entity $entity1
   * @return bool
   *   TRUE on success, FALSE on failure
   */
  public static function deleteBinary($relationship_type, $entity0 = NULL, $entity1 = NULL) {
    // Get the called class:
    $class = get_called_class();

    // Get the relationships:
    $rels = $class::searchBinary($relationship_type, $entity0, $entity1);

    // If none were found, return FALSE:
    if (empty($rels)) {
      return FALSE;
    }

    // Delete the relationships:
    foreach ($rels as $rel) {
      relation_delete($rel->rid());
    }

    return TRUE;
  }

}
