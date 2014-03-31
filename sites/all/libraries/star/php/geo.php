<?php

/**
 * Converts a number of address fields into a single string.
 * The parts of the address are separated by commas.
 * @param strings
 * @return string
 */
function address_fields_to_string() {
  $fields = func_get_args();
  foreach ($fields as $key => $field) {
    $fields[$key] = trim($field, " \t\n\r\0\x0B,");
  }
  return implode(', ', array_filter($fields));
}
