<?php

/**
 * Format an object in a JSON-ish style.
 *
 * @param object $object
 * @param int $indent
 * @return string
 */
function object_to_string($object, $indent = 0, $objects = array(), $html = FALSE) {
  $space = $html ? '&nbsp;' : ' ';
  $spaces = str_repeat($space, $indent);
  $lines = array();
  $lines[] = get_class($object) . $space . '{';

  // Get the object's property values:
  if (method_exists($object, 'toArray')) {
    $properties = $object->toArray();
  }
  else {
    $properties = object_to_array($object);
  }

  // Loop through properties:
  if (is_array($properties)) {
    foreach ($properties as $key => $value) {
      $lines[] = $spaces . $space . $space . $key . ':' . $space . var_to_string($value, $indent + 2, $objects, $html) . ',';
    }
  }

  $lines[] = "$spaces}";
  return implode($html ? '<br>' : "\n", $lines);
}

/**
 * Converts an object to an array.
 * Similar to casting, but gets rid of the weird \0...\0 prefix for private/protected properties.
 *
 * @param object $object
 * @param bool $deep
 *   If TRUE, then objects inside the provided object will also be converted to arrays.
 * @param array $objects
 * @return array
 */
function object_to_array($object, $deep = FALSE, $objects = array()) {
  $array = (array) $object;
  $result = array();
  foreach ($array as $key => $value) {

    // If the value is an object:
    if (is_object($value)) {
      $object_hash = spl_object_hash($value);
      if (in_array($object_hash, $objects)) {
        $value = "((Circular reference))";
      }
      else {
        // Remember we saw this object:
        $objects[] = $object_hash;

        // If this is a deep conversion, convert it to an array also:
        if ($deep) {
          $value = object_to_array($value, $deep, $objects);
        }
      }
    }

    // Remove the initial null characters prepended to protected and private properties.
    $key = preg_replace('/^\0.+\0/i', '', $key);

    // Add the value to the $result array:
    $result[$key] = $value;
  }
  return $result;
}

/**
 * Get an object's unique instance ID.
 *
 * @param object $obj
 * @return int
 */
function get_object_id($obj) {
  if (!is_object($obj)) {
    return FALSE;
  }

  ob_start();
  var_dump($obj);
  $dump = ob_get_clean();
  preg_match("/\d+/", $dump, $oid);
  return $oid[0];
}
