<?php
/**
 * Encapsulates a field collection.
 *
 * @author Shaun Moss (shaun@astromultimedia.com, skype:mossy2100)
 * @since 2012-11-02 10:05
 */

namespace AstroMultimedia\Drupal;

use stdClass;
use Exception;
use FieldCollectionItemEntity;

class FieldCollectionItem extends Entity {

  /**
   * The entity type.
   *
   * @var string
   */
  const ENTITY_TYPE = 'field_collection_item';

  /**
   * The database table name.
   *
   * @var string
   */
  const DB_TABLE = 'field_collection_item';

  /**
   * The primary key.
   *
   * @var string
   */
  const PRIMARY_KEY = 'item_id';

  /**
   * An array of all field collection items, organised by type. Static cache for all() method.
   *
   * @var array
   */
  public static $all;

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Magic methods

  /**
   * Constructor.
   */
  protected function __construct() {
    return parent::__construct();
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Static methods

  /**
   * Create a new FieldCollectionItem object.
   *
   * @param mixed $param
   * @throws \Exception
   * @return FieldCollectionItem
   */
  public static function create($param = NULL) {
    // Get the class of the object we want to create:
    $class = get_called_class();

    if (is_null($param)) {
      // Create new field collection item:
      $fci_obj = new $class;

      // Create the entity:
      $values = array(
        'field_name' => $class::BUNDLE,
        'is_new' => TRUE,
      );
      $fci_obj->entity = new FieldCollectionItemEntity($values);

      // The field_collection_item is valid without a item_id:
      $fci_obj->valid = TRUE;
    }
    elseif (is_pint($param)) {
      // item_id provided:
      $item_id = (int) $param;

      // Only create the new field_collection_item if not already in the cache:
      if (self::inCache($item_id)) {
        return self::getFromCache($item_id);
      }
      else {
        // Create new field_collection_item:
        $fci_obj = new $class;

        // Create the entity:
        $values = array(
          'field_name' => $class::BUNDLE,
          'item_id' => $item_id,
        );
        $fci_obj->entity = new FieldCollectionItemEntity($values);
      }
    }
    elseif ($param instanceof FieldCollectionItemEntity) {
      // Drupal field_collection_item object provided:
      $field_collection_item = $param;

      // Get the object from the cache if possible:
      if (isset($field_collection_item->item_id) && $field_collection_item->item_id && self::inCache($field_collection_item->item_id)) {
        $fci_obj = self::getFromCache($field_collection_item->item_id);
      }
      else {
        $fci_obj = new $class;
      }

      // Reference the provided entity object:
      $fci_obj->entity = $field_collection_item;

      // Make sure we mark the field_collection_item as loaded. It may not have been saved yet, and if we load it, any
      // changes to the field_collection_item entity would be overwritten.
      $fci_obj->loaded = TRUE;
    }

    // If we have a field_collection_item object, add to cache and return:
    if (isset($fci_obj)) {
      $fci_obj->addToCache();
      return $fci_obj;
    }

    throw new Exception("Invalid parameter.");
  }

  /**
   * Get all field collection items of a given type, determined by the derived class from where the method is called.
   *
   * @param bool $published
   *   If TRUE, gets all published nodes of the specified type.
   *   If FALSE, gets all unpublished nodes of the specified type.
   *   If NULL, gets all nodes of the specified type.
   * @param bool $force_update
   * @throws Exception
   * @return array
   */
  public static function all($published = TRUE, $force_update = FALSE) {
    // Get the node type:
    $class = get_called_class();
    $bundle = $class::BUNDLE;
    if (!$bundle) {
      throw new Exception("Could not determine field collection. This method must be called from a class derived from FieldCollectionItem.");
    }

    // Check if we already did this:
    if (!isset(self::$all[$bundle]) || $force_update) {
      // Get all the field collection items of the specified type:
      $q = db_select('field_collection_item', 'fci')
        ->fields('fci', array('item_id'))
        ->condition('field_name', $bundle)
        ->orderBy('item_id')
        ->range(0, 10);

      // Restrict to published or unpublished unless specified otherwise:
      if (isset($published)) {
        $q->condition('archived', $published ? 0 : 1);
      }

      // Get the nodes:
      $rs = $q->execute();
      self::$all[$bundle] = array();
      foreach ($rs as $rec) {
        self::$all[$bundle][] = $class::create($rec->item_id);
      }
    }

    return self::$all[$bundle];
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Load/save/delete

  /**
   * Load the field_collection object.
   *
   * @param bool $force
   * @return FieldCollectionItem
   */
  public function load($force = FALSE) {
    // Avoid reloading:
    if ($this->loaded && !$force) {
      return $this;
    }

    // Default result:
    $field_collection_item = FALSE;

    // If we have a item_id, try to load the field_collection:
    if ($this->entity->item_id) {
      // If we want to force a reload, remove the FCI from the entity cache:
      if ($force) {
        entity_get_controller(self::ENTITY_TYPE)->resetCache([$this->entity->item_id]);
      }
      // Load by item_id. Drupal caching will prevent reloading of the same field_collection.
      $field_collection_item = field_collection_item_load($this->entity->item_id);
    }

    // Set the valid flag:
    $this->valid = (bool) $field_collection_item;

    // If the field_collection was successfully loaded, update fields:
    if ($field_collection_item) {
      $this->entity = $field_collection_item;
      $this->loaded = TRUE;
    }

    return $this;
  }

  /**
   * Save the field_collection object.
   *
   * @param $skip_host_save
   *   (internal) If TRUE is passed, the host entity is not saved automatically
   *   and therefore no link is created between the host and the item or
   *   revision updates might be skipped. Use with care.
   *   This is a passthru of the same parameter as in FieldCollectionItemEntity::save().
   * @return FieldCollectionItem
   */
  public function save($skip_host_save = FALSE) {
    // Ensure the field_collection_item is loaded:
    $this->load();

    // Save the field_collection:
    $this->entity->save($skip_host_save);

    // It's not new any more:
    $this->entity->is_new = FALSE;

    // In case the field_collection is new, add it to the cache:
    $this->addToCache();

    return $this;
  }

  /**
   * Delete a field collection item.
   */
  public function delete() {
    $this->entity->delete();
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Get/set methods

  /**
   * Get the item_id.
   *
   * @return int
   */
  public function itemId() {
    return isset($this->entity->item_id) ? (int) $this->entity->item_id : NULL;
  }

  /**
   * Set the item_id.
   *
   * @param int $item_id
   * @return FieldCollectionItem
   */
  public function setItemId($item_id) {
    $this->entity->item_id = isset($item_id) ? (int) $item_id : NULL;
    // Add the object to the cache if not already:
    $this->addToCache();
    return $this;
  }

  /**
   * Get the revision_id.
   *
   * @return int
   */
  public function revisionId() {
    return isset($this->entity->revision_id) ? (int) $this->entity->revision_id : NULL;
  }

  /**
   * Set the revision_id.
   *
   * @param int $revision_id
   * @return FieldCollectionItem
   */
  public function setRevisionId($revision_id) {
    $this->entity->revision_id = isset($revision_id) ? (int) $revision_id : NULL;
    return $this;
  }

  /**
   * Get the host entity.
   *
   * @return stdClass
   */
  public function hostEntity() {
    $this->load();
    return $this->entity->hostEntity();
  }

  /**
   * Set the host entity. This must be called before save().
   *
   * @param Entity $entity
   * @param FieldCollectionItem
   */
  public function setHostEntity(Entity $entity) {
    return $this->entity->setHostEntity($entity->entityType(), $entity->entity());
  }

}
