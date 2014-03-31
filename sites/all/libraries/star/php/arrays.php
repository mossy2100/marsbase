<?php

/**
 * Format an array in the Drupal style.
 *
 * @param array $array
 * @param int $indent
 * @param array $objects
 * @param bool $html
 * @return string
 */
function array_to_string(array $array, $indent = 0, $objects = array(), $html = FALSE) {
  // Use updated array syntax for PHP 5.4:
  $is54 = substr(phpversion(), 0, 3) == '5.4';
  if ($is54) {
    $array_begin = '[';
    $array_end = ']';
  }
  else {
    $array_begin = 'array(';
    $array_end = ')';
  }

  if (empty($array)) {
    return "$array_begin$array_end";
  }
  $space = $html ? '&nbsp;' : ' ';
  $spaces = str_repeat($space, $indent);
  $lines = array();
  $lines[] = $array_begin;
  foreach ($array as $key => $value) {
    $lines[] = $spaces . $space . $space . var_to_string($key, 0, $objects, $html) . $space . '=>' . $space . var_to_string($value, $indent + 2, $objects, $html) . ',';
  }
  $lines[] = "$spaces$array_end";
  return implode($html ? '<br>' : "\n", $lines);
}
