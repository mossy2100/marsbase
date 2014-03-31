<?php
/**
 * Set class. Emulates sets, i.e. unordered collections with no duplicates.
 */

namespace AstroMultimedia\Star;

class Set {

  /**
   * Items in the set.
   *
   * @var array
   */
  protected $items;

  /**
   * Constructor.
   * Creates a new set containing the unique values of the provided array. Keys are discarded.
   */
  public function __construct(array $arr = array()) {
    $this->items = array_values(array_unique($arr));
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Standard set operations. Instance methods.

  /**
   * Return the number of items in the set.
   *
   * @return int
   */
  public function count() {
    return count($this->items);
  }

  /**
   * Check if the set contains a given item.
   *
   * @param mixed $item
   * @return bool
   */
  public function contains($item) {
    return in_array($item, $this->items);
  }

  /**
   * Add an item to the set.
   *
   * @param mixed $item
   * @return Set
   */
  public function add($item) {
    if (!in_array($item, $this->items, TRUE)) {
      $this->items[] = $item;
    }
    return $this;
  }

  /**
   * Remove an item from the set.
   *
   * @param mixed $item
   * @return Set
   */
  public function remove($item) {
    $this->items = array_values(array_diff($this->items, array($item)));
    return $this;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Standard set operations. Class methods.

  /**
   * Union of two sets.
   *
   * @param Set $set1
   * @param Set $set2
   * @return Set
   */
  public static function union(self $set1, self $set2) {
    return new self(array_merge($set1->items, $set2->items));
  }

  /**
   * Difference between two sets.
   *
   * @param Set $set1
   * @param Set $set2
   * @return Set
   */
  public static function diff(self $set1, self $set2) {
    return new self(array_diff($set1->items, $set2->items));
  }

  /**
   * Intersection between two sets.
   *
   * @param Set $set1
   * @param Set $set2
   * @return Set
   */
  public static function intersect(self $set1, self $set2) {
    return new self(array_intersect($set1->items, $set2->items));
  }

  /**
   * Checks if 2 sets are equal.
   *
   * @param Set $set2
   * @return Set
   */
  public function equal(self $set2) {
    return ($this->count() == $set2->count()) && $this->subset($set2);
  }


  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Standard set comparisons. Instance methods.

  /**
   * Checks if a set is a subset of another set.
   *
   * @param Set $set2
   * @return Set
   */
  public function subset(self $set2) {
    foreach ($this->items as $item) {
      if (!$set2->in($item)) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Checks if a set is a proper subset of another set.
   *
   * @param Set $set2
   * @return Set
   */
  public function properSubset(self $set2) {
    return ($this->count() < $set2->count()) && $this->subset($set2);
  }

  /**
   * Checks if a set is a superset of another set.
   *
   * @param Set $set2
   * @return Set
   */
  public function superset(self $set2) {
    return $set2->subset($this);
  }

  /**
   * Checks if a set is a proper superset of another set.
   *
   * @param Set $set2
   * @return Set
   */
  public function properSuperset(self $set2) {
    return $set2->properSubset($this);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Conversion methods.

  /**
   * Convert set to a string.
   *
   * @todo needs improvement such as quotes around string values.
   *  Could perhaps use code from object_to_string() or array_to_string()
   *
   * @return string
   */
  public function toString($glue = ', ', $left_bracket = '{', $right_bracket = '}') {
    return $left_bracket . implode($glue, $this->items) . $right_bracket;
  }

  /**
   * Magic method providing default behaviour for converting a set to a string.
   *
   * @return string
   */
  public function __toString() {
    return $this->toString();
  }

  /**
   * Get the items.
   *
   * @return array
   */
  public function items() {
    return $this->items;
  }

  /**
   * Convert set to an array.
   * (Alias for items())
   *
   * @return array
   */
  public function toArray() {
    return $this->items;
  }

  /**
   * Sorts the set items.
   * Technically not a set operation, but useful.
   */
  public function sort() {
    sort($this->items);
  }

}
