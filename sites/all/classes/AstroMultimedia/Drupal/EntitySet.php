<?php
namespace AstroMultimedia\Drupal;

/**
 * User: shaun
 * Date: 2012-09-14
 * Time: 12:08 PM
 */
class EntitySet {

  /**
   * Array of entities.
   *
   * @var array
   */
  protected $entities;

  /**
   * Constructor.
   */
  public function __construct() {
    // Initialise the array of entities:
    $this->entities = array();
  }

  /**
   * Add an entity to the set.
   *
   * @param Entity $entity
   */
  public function addSingle(Entity $entity) {
    // By using the entity path as the array key, we prevent duplicates.
    $this->entities[$entity->path()] = $entity;
  }

  /**
   * Add entities to the set.
   *
   * @param array $entities
   */
  public function addMultiple(array $entities) {
    foreach ($entities as $entity) {
      $this->addSingle($entity);
    }
  }

  /**
   * Add one or more entities to the set.
   *
   * @param array|Entity $entity
   */
  public function add($entities) {
    if (is_array($entities)) {
      $this->addMultiple($entities);
    }
    else {
      $this->addSingle($entities);
    }
  }

  /**
   * Remove an entity from the set.
   *
   * @param Entity $entity
   */
  public function removeSingle(Entity $entity) {
    // By using the entity path as the array key, it's easy to find the entity to remove.
    unset($this->entities[$entity->path()]);
  }

  /**
   * Remove entities from the set.
   *
   * @param array $entities
   */
  public function removeMultiple(array $entities) {
    foreach ($entities as $entity) {
      $this->removeSingle($entity);
    }
  }

  /**
   * Remove one or more entities from the set.
   *
   * @param array|Entity $entities
   */
  public function remove($entities) {
    if (is_array($entities)) {
      $this->removeMultiple($entities);
    }
    else {
      $this->removeSingle($entities);
    }
  }

  /**
   * Get the array of entities.
   *
   * @return array
   */
  public function entities() {
    return $this->entities;
  }

  /**
   * Get the array of entity paths.
   *
   * @return array
   */
  public function entityPaths() {
    return array_keys($this->entities);
  }

  /**
   * Get the number entities in the set.
   *
   * @return int
   */
  public function count() {
    return count($this->entities);
  }

}
