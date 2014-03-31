<?php
/**
 * A base class for entities.
 *
 * @todo This should be integrated somehow with the Entity class provided by the entity module.
 */

namespace AstroMultimedia\Drupal;

use Exception;
use stdClass;
use AstroMultimedia\Star\DateTime;

abstract class Entity {

  /**
   * The entity type.
   * To be overridden in child classes.
   *
   * @var string
   */
  const ENTITY_TYPE = NULL;

  /**
   * The bundle name.
   * - For nodes, this is the node type.
   * - For terms, this is the vocabulary machine name.
   * - For users, this is 'user'.
   * To be overridden in child classes.
   *
   * @var string
   */
  const BUNDLE = NULL;

  /**
   * Static cache of entity objects.
   *
   * @var array
   */
  protected static $cache;

  /**
   * The Drupal entity object (node, user, etc.)
   *
   * @var stdClass
   */
  protected $entity;

  /**
   * If the entity has been loaded yet.
   *
   * @var bool
   */
  protected $loaded;

  /**
   * If the id is valid, i.e. refers to an actual entity in the database.
   *
   * @var bool
   */
  protected $valid;

  /**
   * Constructor.
   */
  protected function __construct() {
    // Get the called class:
    $class = get_called_class();

    // Check the bundle is declared:
    if (!$class::BUNDLE) {
      throw new Exception("$class::BUNDLE not declared.");
    }

    // Create an entity object:
    $this->entity = new stdClass;

    // Initially the entity is not loaded:
    $this->loaded = FALSE;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Static methods

  /**
   * Get all entities of the called class as options for a select box.
   *
   * @return array
   */
  public static function options() {
    $class = get_called_class();
    $entities = $class::all();
    $options = array();
    foreach ($entities as $entity) {
      $options[$entity->id()] = $entity->name();
    }
    return $options;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Load/save/delete

  /**
   * Load an entity from the database.
   *
   * @abstract
   * @param bool $force
   * @return Entity
   */
  abstract public function load($force = FALSE);

  /**
   * Save an entity to the database.
   *
   * @abstract
   * @return Entity
   */
  abstract public function save();

  /**
   * Delete an entity.
   *
   * @abstract
   */
  abstract public function delete();

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Reload and copy methods

  /**
   * Reload the entity.
   */
  public function reload() {
    $this->loaded = FALSE;
    $this->load();
  }

//  /**
//   * Copy an entity.
//   * That means, create a new entity by cloning an existing one, with id = 0.
//   * This does not save the new entity.
//   */
//  public function copy() {
//    $new_entity = clone $this;
//    $new_entity->entity = clone $this->entity;
//    // Set id to NULL:
//    $new_entity->setId(NULL);
//    // Flag as new and valid:
//    $new_entity->setIsNew(TRUE);
//    $new_entity->setValid(TRUE);
//    return $new_entity;
//  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Get/set methods

  /**
   * Get the quick-load fields.
   * Can't make this abstract for some reason.
   *
   * @return array
   */
  protected static function quickLoadFields() {
    return array();
  }

  /**
   * Get the entity object.
   *
   * @return stdClass
   */
  public function entity() {
    $this->load();
    return $this->entity;
  }

  /**
   * Get the entity id.
   *
   * @return int
   */
  public function id() {
    $class = get_class($this);
    $primary_key = $class::PRIMARY_KEY;
    $primary_key_method = convert_case($primary_key, CASE_LOWER, CASE_CAMEL);
    $id = $this->$primary_key_method();
    return isset($id) ? (int) $id : NULL;
  }

  /**
   * Set the entity id.
   *
   * @param int $id
   * @return Entity
   */
  public function setId($id) {
    $id = isset($id) ? (int) $id : NULL;
    $class = get_class($this);
    $primary_key = $class::PRIMARY_KEY;
    $primary_key_method = 'set' . convert_case($primary_key, CASE_LOWER, CASE_TITLE);
    return $this->$primary_key_method($id);
  }

  /**
   * Get the entity type.
   *
   * @return string
   */
  public function entityType() {
    $class = get_class($this);
    return $class::ENTITY_TYPE;
  }

  /**
   * Get the bundle name.
   *
   * @return string
   */
  public function bundle() {
    $class = get_called_class();
    return $class::BUNDLE;
  }

  /**
   * Set the values of multiple fields.
   *
   * @param array $values
   * @return mixed
   */
  public function setProperties($values) {
    $this->load();

    // Set the property's value.
    foreach ($values as $property => $value) {
      $this->entity->{$property} = $value;
    }
    return $this;
  }

  /**
   * Get an entity field's value.
   * Intention is to replace field() and prop() with getField() and setField().
   *
   * @param string $field
   * @param string $lang
   * @param int $delta
   * @param string $key
   * @return mixed
   * @throws Exception
   */
  public function field($field, $lang = NULL, $delta = NULL, $key = NULL) {
    $force_load = FALSE;

    $n_args = func_num_args();

    // If not set, for quick load fields get the property value from the database; otherwise load the entity:
    if (!isset($this->entity->{$field})) {
      // Get the object's class:
      $class = get_class($this);

      // For some "quick load" fields, just get the field from the table record rather than load the whole object:
      if ($n_args == 1 && in_array($field, $class::quickLoadFields())) {
        $q = db_select($class::DB_TABLE, 't')
          ->fields('t', array($field))
          ->condition($class::PRIMARY_KEY, $this->id());
        $rec = $q->execute()->fetch();

        // If we got the record then set the property value:
        if ($rec) {
          $this->entity->{$field} = $rec->$field;
        }

        // If we got the record then the id is valid:
        $this->valid = (bool) $rec;
      }
      else {
        // It's not a quick-load field, so force load of the whole object:
        $force_load = TRUE;
      }
    }

    // Load the entity if necessary:
    if ($force_load) {
      $this->load(TRUE);
    }

    switch ($n_args) {
      case 1:
        return isset($this->entity->{$field}) ? $this->entity->{$field} : NULL;

      case 2:
        return isset($this->entity->{$field}[$lang]) ? $this->entity->{$field}[$lang] : NULL;

      case 3:
        return isset($this->entity->{$field}[$lang][$delta]) ? $this->entity->{$field}[$lang][$delta] : NULL;

      case 4:
        return isset($this->entity->{$field}[$lang][$delta][$key]) ? $this->entity->{$field}[$lang][$delta][$key] : NULL;

      default:
        throw new Exception("Invalid number of parameters.");
    }
  }

  /**
   * Set an entity field's value.
   *
   * @param string $field
   * @param mixed $arg1
   * @param mixed $arg2
   * @param mixed $arg3
   * @param mixed $arg4
   * @throws Exception
   * @return Entity
   */
  public function setField($field, $arg1, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL) {
    // Load the entity if not already:
    $this->load();

    // Update the appropriate entity property based on the number of args:
    switch (func_num_args()) {
      case 2:
        $this->entity->{$field} = $arg1;
        break;

      case 3:
        $this->entity->{$field}[$arg1] = $arg2;
        break;

      case 4:
        $this->entity->{$field}[$arg1][$arg2] = $arg3;
        break;

      case 5:
        $this->entity->{$field}[$arg1][$arg2][$arg3] = $arg4;
        break;

      default:
        throw new Exception("Invalid argument count.");
        break;
    }

    return $this;
  }

  /**
   * Check if the object is loaded.
   *
   * @return bool
   */
  public function loaded() {
    return $this->loaded;
  }

  /**
   * Check if the entity's id is valid.
   *
   * @return bool
   */
  public function valid() {
    // If the id is not set then the entity is valid. It's simply a new entity that hasn't been saved yet.
    if (!$this->id()) {
      return TRUE;
    }

    // If the valid flag hasn't been set yet via prop(), then the simplest way to check if the entity is valid
    // is to try and load it:
    if (!isset($this->valid)) {
      $this->load();
    }

    return $this->valid;
  }

  /**
   * Set if the entity is valid.
   *
   * @param bool $valid
   * @return Entity
   */
  public function setValid($valid) {
    $this->valid = (bool) $valid;
    return $this;
  }

  /**
   * Check if the entity is new.
   *
   * @return bool
   */
  public function isNew() {
    return $this->entity->is_new;
  }

  /**
   * Set if the entity is new.
   *
   * @param bool $is_new
   * @return Entity
   */
  public function setIsNew($is_new) {
    $this->entity->is_new = (bool) $is_new;
    return $this;
  }

  /**
   * Set the value for the pathauto flag.
   * This maps to the "Generate automatic URL alias" checkbox on the node edit form.
   * This code is adapted from pathauto_field_attach_form().
   */
  public function setPathauto($langcode = LANGUAGE_NONE) {
    if (!isset($this->entity->path['pathauto'])) {

      $entity_type = $this->entityType();
      list($id, $vid, $bundle) = entity_extract_ids($entity_type, $this->entity);

      if (!function_exists('pathauto_create_alias')) {
        // Pathauto is not installed, so FALSE:
        $this->entity->path['pathauto'] = FALSE;
      }
      elseif (!empty($id)) {
        module_load_include('inc', 'pathauto');
        $uri = entity_uri($entity_type, $this->entity);
        $path = drupal_get_path_alias($uri['path'], $langcode);
        $pathauto_alias = pathauto_create_alias($entity_type, 'return', $uri['path'], array($entity_type => $this->entity), $bundle, $langcode);
        $this->entity->path['pathauto'] = ($path != $uri['path'] && $path == $pathauto_alias);
      }
      else {
        // Default to TRUE:
        $this->entity->path['pathauto'] = TRUE;
      }

    }
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Caching methods.

  /**
   * Add an entity to the cache, if it has an id.
   *
   * @return bool
   */
  public function addToCache() {
    $id = $this->id();
    if ($id) {
      $class = get_class($this);
      self::$cache[$class::ENTITY_TYPE][$id] = $this;
    }
  }

  /**
   * Check if an entity is in the cache.
   *
   * @param int $entity_id
   * @return bool
   */
  public static function inCache($entity_id) {
    $class = get_called_class();
    return isset(self::$cache[$class::ENTITY_TYPE][$entity_id]);
  }

  /**
   * Get an entity from the cache.
   *
   * @param int $entity_id
   * @return Entity
   */
  public static function getFromCache($entity_id) {
    $class = get_called_class();
    return isset(self::$cache[$class::ENTITY_TYPE][$entity_id]) ? self::$cache[$class::ENTITY_TYPE][$entity_id] : NULL;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Equals.

  /**
   * Checks if two entities are equal.
   * It's possible that they could be different objects, although with the entity caching system this shouldn't happen.
   *
   * @param Entity $entity
   * @return bool
   */
  public function equals(Entity $entity) {
    return ($this->entityType() == $entity->entityType()) && ($this->id() == $entity->id());
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Path and alias-related methods.

  /**
   * Get the system or normal path to the entity's page.
   *
   * @return string
   */
  public function path() {
    return str_replace('_', '/', $this->entityType()) . '/' . $this->id();
  }

  /**
   * Get the path alias to the entity's page.
   *
   * @return string
   */
  public function alias() {
    return drupal_get_path_alias($this->path());
  }

  /**
   * Set the path alias to the entity's page.
   *
   * @param string
   * @return Entity
   */
  public function setAlias($alias) {
    $source = $this->path();

    // Delete any existing aliases for this entity.
    $q = db_delete('url_alias')
      ->condition('source', $source);
    $q->execute();

    // Insert the new alias:
    $q = db_insert('url_alias')
      ->fields(array(
        'source'   => $source,
        'alias'    => $alias,
        'language' => LANGUAGE_NONE,
      ));
    $q->execute();
    return $this;
  }

  /**
   * Delete all path aliases for the entity.
   */
  public function deleteAlias() {
    return db_delete('url_alias')
      ->condition('source', $this->path())
      ->execute();
  }

  /**
   * Get a URL for the entity.
   * This is not the same as alias(), regardless of the value of $absolute.
   * If $absolute is TRUE, it will begin with the base URL, i.e. http://example.com/the-alias
   * If $absolute is FALSE, it will begin with a '/', i.e. /the-alias
   *
   * @return string
   */
  public function url($absolute = FALSE) {
    return ($absolute ? $GLOBALS['base_url'] : '') . '/' . $this->alias();
  }

  /**
   * Get a link to the entity.
   *
   * @param string $label
   * @param bool $absolute
   * @param array $options
   * @return string
   */
  public function link($label = NULL, $absolute = FALSE, array $options = array()) {
    $url = $absolute ? $this->url($absolute) : $this->alias();
    $label = ($label === NULL) ? $url : $label;
    return l($label, $url, $options);
  }

  /**
   * Get the path to the entity's edit page.
   *
   * @return string
   */
  public function editAlias() {
    return $this->alias() . '/edit';
  }

  /**
   * Edit link.
   *
   * @param string $label
   * @param array $options
   * @return string
   */
  public function editLink($label = NULL, array $options = array()) {
    $label = ($label === NULL) ? 'edit' : $label;
    return l($label, $this->alias() . '/edit', $options);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Datetimes

  /**
   * Get the created datetime.
   *
   * @param bool $utc
   *   If TRUE the result will have UTC as the timezone.
   *   IF FALSE the timezone will be the default as returned by date_default_timezone_get().
   * @return DateTime
   */
  public function created($utc = FALSE) {
    // Create a DateTime object. Because this is a timestamp, the timezone will be UTC:
    $created = new DateTime($this->field('created'));

    // If the caller does not want UTC, change to default timezone. This is the default behaviour.
    if (!$utc) {
      $created->setTimezone(DateTime::defaultTimezone());
    }

    return $created;
  }

  /**
   * Set the created datetime.
   *
   * @param int|DateTime $created
   * @return self
   */
  public function setCreated($created) {
    if (!is_uint($created)) {
      $created = new DateTime($created);
    }
    if ($created instanceof DateTime) {
      $created = $created->timestamp();
    }
    $this->setField('created', $created);
  }

  /**
   * Get the changed datetime.
   *
   * @param bool $utc
   *   If TRUE the result will have UTC as the timezone.
   *   IF FALSE the timezone will be the default as returned by date_default_timezone_get().
   * @return DateTime
   */
  public function changed($utc = FALSE) {
    // Create a DateTime object. Because this is a timestamp, the timezone will be UTC:
    $changed = new DateTime($this->field('changed'));

    // If the caller does not want UTC, change to default timezone. This is the default behaviour.
    if (!$utc) {
      $changed->setTimezone(DateTime::defaultTimezone());
    }

    return $changed;
  }

  /**
   * Set the changed datetime.
   *
   * @param int|DateTime $changed
   * @return self
   */
  public function setChanged($changed) {
    if (!is_uint($changed)) {
      $changed = new DateTime($changed);
    }
    if ($changed instanceof DateTime) {
      $changed = $changed->timestamp();
    }
    $this->setField('changed', $changed);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Status-related methods

  /**
   * Get the entity's status.
   *
   * @return int
   */
  public function status() {
    return $this->field('status');
  }

  /**
   * Set the entity's status.
   *
   * @param int $status
   * @return Entity
   */
  public function setStatus($status) {
    return $this->setField('status', $status ? 1 : 0);
  }

  /**
   * Get if the entity is published or not.
   *
   * @return bool
   */
  public function isPublished() {
    return (bool) $this->status();
  }

  /**
   * Publish the entity, i.e. set the status flag to 1.
   *
   * @return Relation
   */
  public function publish() {
    return $this->setStatus(1);
  }

  /**
   * Unpublish the entity, i.e. set the status flag to 0.
   *
   * @return Relation
   */
  public function unpublish() {
    return $this->setStatus(0);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Conversion

  /**
   * Convert the entity to an array.
   *
   * @return array
   */
  public function toArray() {
    $array = (array) $this;
    foreach ($array as $key => $value) {
      $key = preg_replace('/^\0.+\0/i', '', $key);
      $array2[$key] = $value;
    }
    return $array2;
  }

  /**
   * Method for using dpm() on these objects.
   */
  public function dpm() {
    dpm(object_to_array($this, TRUE));
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Get/set fields

  /**
   * Get a single-value string field.
   *
   * @param string $field
   * @param string $format
   * @return string
   */
  public function stringField($field, &$format = NULL) {
    $array = $this->field($field, LANGUAGE_NONE, 0);

    // Record the format in the pass-by-reference parameter, in case they want it:
    $format = $array['format'];

    return isset($array['value']) ? trim($array['value']) : NULL;
  }

  /**
   * Set a single-value string field.
   *
   * @param string $field
   * @param string $value
   * @param string $format
   * @return self
   */
  public function setStringField($field, $value, $format = NULL) {
    $value = isset($value) ? trim($value) : NULL;

    // If the format parameter was not provided, keep the current value:
    if (func_num_args() == 2) {
      // This will set $format to the current format for this field:
      $this->stringField($field, $format);
    }

    $this->setField($field, LANGUAGE_NONE, 0, array(
      'value' => $value,
      'format' => $format,
    ));
  }

  /**
   * Get a single-value integer field.
   *
   * @param string $field
   * @return int
   */
  public function intField($field) {
    $value = $this->field($field, LANGUAGE_NONE, 0, 'value');
    return isset($value) ? ((int) $value) : NULL;
  }

  /**
   * Set a single-value integer field.
   *
   * @param string $field
   * @param int $value
   * @return self
   */
  public function setIntField($field, $value) {
    $value = isset($value) ? ((int) $value) : NULL;
    return $this->setField($field, LANGUAGE_NONE, 0, 'value', $value);
  }

  /**
   * Get a single-value float field.
   *
   * @param string $field
   * @return float
   */
  public function floatField($field) {
    $value = $this->field($field, LANGUAGE_NONE, 0, 'value');
    return isset($value) ? ((float) $value) : NULL;
  }

  /**
   * Set a single-value float field.
   *
   * @param string $field
   * @param float $value
   * @return self
   */
  public function setFloatField($field, $value) {
    $value = isset($value) ? ((float) $value) : NULL;
    return $this->setField($field, LANGUAGE_NONE, 0, 'value', $value);
  }

  /**
   * Get a single-value boolean field.
   *
   * @param string $field
   * @return bool
   */
  public function boolField($field) {
    $value = $this->field($field, LANGUAGE_NONE, 0, 'value');
    return isset($value) ? ((bool) $value) : NULL;
  }

  /**
   * Set a single-value boolean field.
   *
   * @param string $field
   * @param int $value
   * @return self
   */
  public function setBoolField($field, $value) {
    $value = isset($value) ? ($value ? 1 : 0) : NULL;
    return $this->setField($field, LANGUAGE_NONE, 0, 'value', $value);
  }

  /**
   * Get a single-value datetime field as a DateTime object.
   *
   * @param string $field
   * @param mixed $tz
   * @return DateTime
   */
  public function datetimeField($field, $tz = NULL) {
    $value = $this->field($field, LANGUAGE_NONE, 0, 'value');
    return $value ? (new DateTime($value, $tz)) : NULL;
  }

  /**
   * Set a single-value datetime field.
   *
   * @param string $field
   * @param mixed $value
   * @param mixed $tz
   * @return self
   */
  public function setDatetimeField($field, $value, $tz = NULL) {
    if (isset($value)) {
      // Convert to a DateTime object if necessary:
      if (!($value instanceof DateTime)) {
        $value = new DateTime($value, $tz);
      }

      // Get the datetime in MySQL format:
      $value = $value->mysql();
    }

    // Set the field value:
    return $this->setField($field, LANGUAGE_NONE, 0, 'value', $value);
  }

  /**
   * Get a dual-value (start and end) datetime field as an array of DateTime objects.
   *
   * @param string $field
   * @param mixed $tz
   * @return DateTime
   */
  public function datetimeField2($field, $tz = NULL) {
    $info = $this->field($field, LANGUAGE_NONE, 0);
    $start_datetime = $info['value'] ? (new DateTime($info['value'], $tz)) : NULL;
    $end_datetime = $info['value2'] ? (new DateTime($info['value2'], $tz)) : NULL;
    return array($start_datetime, $end_datetime);
  }

  /**
   * Set a dual-value (start and end) datetime field.
   *
   * @param string $field
   * @param mixed $start_datetime
   * @param mixed $end_datetime
   * @param mixed $tz
   * @return self
   */
  public function setDatetimeField2($field, $start_datetime, $end_datetime, $tz = NULL) {
    // Get start datetime value:
    if (isset($start_datetime)) {
      // Convert to a DateTime object if necessary:
      if (!($start_datetime instanceof DateTime)) {
        $start_datetime = new DateTime($start_datetime, $tz);
      }

      // Get the datetime in MySQL format:
      $start_datetime = $start_datetime->mysql();
    }

    // Get end datetime value:
    if (isset($end_datetime)) {
      // Convert to a DateTime object if necessary:
      if (!($end_datetime instanceof DateTime)) {
        $end_datetime = new DateTime($end_datetime, $tz);
      }

      // Get the datetime in MySQL format:
      $end_datetime = $end_datetime->mysql();
    }

    // Set the field value:
    return $this->setField($field, LANGUAGE_NONE, 0, array(
      'value' => $start_datetime,
      'value2' => $end_datetime,
    ));
  }

  /**
   * Get a single-value timestamp field as a DateTime object.
   *
   * @param string $field
   * @param mixed $tz
   * @return DateTime
   */
  public function timestampField($field, $tz = NULL) {
    return $this->datetimeField($field, $tz);
  }

  /**
   * Set a single-value timestamp field.
   *
   * @param string $field
   * @param mixed $value
   * @param mixed $tz
   * @return self
   */
  public function setTimestampField($field, $value, $tz = NULL) {
    if (isset($value)) {
      // Convert to a DateTime object if necessary:
      if (!($value instanceof DateTime)) {
        $value = new DateTime($value, $tz);
      }

      // Get the timestamp:
      $value = $value->timestamp();
    }

    // Set the field value:
    return $this->setField($field, LANGUAGE_NONE, 0, 'value', $value);
  }

  /**
   * Get a single-value link field.
   *
   * @param string $field
   * @return array
   */
  public function linkField($field) {
    return $this->field($field, LANGUAGE_NONE, 0);
  }

  /**
   * Set a single-value link field.
   *
   * @param string $field
   * @param string $url
   * @param string $title
   * @param array $attributes
   * @return self
   */
  public function setLinkField($field, $url, $title = NULL, $attributes = NULL) {
    if (isset($url)) {
      $url = add_http(trim($url));
      $title = isset($title) ? trim($title) : NULL;
      return $this->setField($field, LANGUAGE_NONE, 0, array(
        'url' => $url,
        'title' => $title,
        'attributes' => $attributes,
      ));
    }
    // URL is NULL, set the link to NULL:
    return $this->setField($field, LANGUAGE_NONE, 0, NULL);
  }

  /**
   * Get a single-value email field.
   *
   * @param string $field
   * @return array
   */
  public function emailField($field) {
    $email = $this->field($field, LANGUAGE_NONE, 0, 'email');
    return isset($email) ? trim($email) : NULL;
  }

  /**
   * Set a single-value email field.
   *
   * @param string $field
   * @param string $email
   * @return self
   */
  public function setEmailField($field, $email) {
    $email = isset($email) ? trim($email) : NULL;
    return $this->setField($field, LANGUAGE_NONE, 0, 'email', $email);
  }

  /**
   * Get a single-value phone field.
   *
   * @param string $field
   * @return string
   */
  public function phoneField($field) {
    $phone = $this->field($field, LANGUAGE_NONE, 0, 'value');
    return isset($phone) ? trim($phone) : NULL;
  }

  /**
   * Set a single-value phone field.
   *
   * @param string $field
   * @param string $phone
   * @return self
   */
  public function setPhoneField($field, $phone) {
    $phone = isset($phone) ? trim($phone) : NULL;
    return $this->setField($field, LANGUAGE_NONE, 0, 'value', $phone);
  }

  /**
   * Get a single-value address field.
   *
   * @param string $field
   * @return Addressfield
   */
  public function addressField($field) {
    $address = $this->field($field, LANGUAGE_NONE, 0);
    return isset($address) ? (new Addressfield($address)) : NULL;
  }

  /**
   * Set a single-value address field.
   *
   * @param string $field
   * @param mixed $address
   * @throws Exception
   * @return self
   */
  public function setAddressField($field, $address) {
    if (isset($address)) {
      if ($address instanceof Addressfield) {
        $address = $address->toArray();
      }
      if (!is_array($address)) {
        throw new Exception("Address must be an array or an Addressfield object.");
      }
    }
    return $this->setField($field, LANGUAGE_NONE, 0, $address);
  }

  /**
   * Get a single-value entity reference field.
   *
   * @param string $field
   * @param string $class
   *   Fully qualified class name.
   * @return string
   */
  public function entityField($field, $class) {
    $entity_id = $this->field($field, LANGUAGE_NONE, 0, 'target_id');
    return isset($entity_id) ? $class::create($entity_id) : NULL;
  }

  /**
   * Set a single-value entity reference field.
   *
   * @param string $field
   * @param string $class
   *   Fully qualified class name.
   * @param mixed $entity
   *   May be an Entity, id (nid, tid, uid, etc.), or NULL.
   * @return self
   * @throws Exception
   */
  public function setEntityField($field, $class, $entity) {
    // NULL:
    if (!isset($entity)) {
      return $this->setField($field, NULL);
    }

    // If an integer, assume entity id:
    if (is_pint($entity)) {
      return $this->setField($field, LANGUAGE_NONE, 0, 'target_id', $entity);
    }

    // If something else, convert to Entity object if possible:
    if (!($entity instanceof Entity) && $class) {
      $entity = $class::create($entity);
    }

    if ($entity instanceof Entity) {
      // If this entity is new, save it now so we have an id:
      if (!$entity->id()) {
        $entity->save();
      }

      return $this->setField($field, LANGUAGE_NONE, 0, 'target_id', $entity->id());
    }

    throw new Exception("Invalid entity reference.");
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Domains

  /**
   * Get the domains this entity is in.
   *
   * @return array
   */
  public function domains() {
    // Get the domain ids:
    $domain_ids = $this->domainIds();

    // Convert to Domain objects:
    $domains = array();
    foreach ($domain_ids as $domain_id) {
      $domains[$domain_id] = Domain::create($domain_id);
    }

    // Sort:
    ksort($domains);

    return $domains;
  }

  /**
   * Check if an entity is published on a domain. If domain unspecified, defaults to current.
   * If the domain access module is not enabled an exception will be thrown.
   *
   * @param Domain $domain
   * @return bool
   */
  public function isPublishedOnDomain($domain = NULL) {
    // Check if it's published on all domains:
    if ($this->isPublishedOnAllDomains()) {
      return TRUE;
    }

    // Get the specified domain - defaults to current:
    $domain = Domain::find($domain);

    // Get the entity's domain ids:
    $domain_ids = $this->domainIds();

    // Check if it's visible on the specified domain:
    foreach ($domain_ids as $domain_id) {
      if ($domain->id() == $domain_id) {
        return TRUE;
      }
    }

    // Entity is not published on the specified domain:
    return FALSE;
  }

  /**
   * Add the entity to multiple domains.
   *
   * @param array $new_domains
   * @throws Exception
   * @return self
   */
  public function publishOnDomains(array $new_domains) {
    // Get the current domains:
    $domain_ids = $this->domainIds();

    // Add the new domain ids while also checking that we only have Domain objects or domain ids:
    foreach ($new_domains as $new_domain) {
      if ($new_domain instanceof Domain) {
        $domain_id = $new_domain->id();
      }
      elseif (is_pint($new_domain)) {
        $domain_id = (int) $new_domain;
      }
      else {
        throw new Exception("Invalid parameter. Domain objects or IDs expected.");
      }
      $domain_ids[$domain_id] = $domain_id;
    }

    // Update the domains:
    return $this->setDomains($domain_ids);
  }

  /**
   * Add the entity to a domain. If domain unspecified, defaults to current.
   *
   * @param mixed $domain
   * @return self
   */
  public function publishOnDomain($domain = NULL) {
    // Default to current domain:
    if (!$domain) {
      $domain = Domain::current();
    }

    return $this->publishOnDomains(array($domain));
  }

  /**
   * Remove the entity from multiple domains.
   *
   * @param array $old_domains
   * @throws Exception
   * @return self
   */
  public function unpublishOnDomains(array $old_domains) {
    // Get the current domains:
    $domain_ids = $this->domainIds();

    // Remove the old domain ids while also checking that we only have Domain objects or domain ids:
    foreach ($old_domains as $old_domain) {
      if ($old_domain instanceof Domain) {
        $domain_id = $old_domain->id();
      }
      elseif (is_pint($old_domain)) {
        $domain_id = (int) $old_domain;
      }
      else {
        throw new Exception("Invalid parameter. Domain objects or IDs expected.");
      }
      unset($domain_ids[$domain_id]);
    }

    // Update the domains:
    return $this->setDomains($domain_ids);
  }

  /**
   * Add the entity to a domain. If domain unspecified, defaults to current.
   *
   * @param mixed $domain
   * @return self
   */
  public function unpublishOnDomain($domain = NULL) {
    // Default to current domain:
    if (!$domain) {
      $domain = Domain::current();
    }

    return $this->unpublishOnDomains(array($domain));
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Entity references

  /**
   * Check if the entity has a reference to a certain entity, or, if the parameter is an array, at least one of the
   * entities in the array.
   *
   * @param string $field
   * @param mixed $entities
   * @return bool
   * @throws Exception
   */
  public function references($field, $entities) {
    // Convert a single value into an array:
    if (!is_array($entities)) {
      $entities = array($entities);
    }

    // Check each entity:
    foreach ($entities as $entity) {
      // Check the type - provide support for ids as well as objects:
      if (is_pint($entity)) {
        $entity_id = $entity;
      }
      elseif ($entity instanceof Entity) {
        $entity_id = $entity->id();
      }
      else {
        throw new Exception("Invalid entity object or id.");
      }

      // Check each reference value:
      $fields = $this->field($field, LANGUAGE_NONE);
      if (is_array($fields)) {
        foreach ($fields as $info) {
          if ($info['target_id'] == $entity_id || $info['tid'] == $entity_id) {
            return TRUE;
          }
        }
      }
    }

    return FALSE;
  }

}
