<?php

/**
 * Get the SQL for a query.
 *
 * @param Query $query
 * @param string $target
 * @return string
 */
function query_to_string($query, $target = 'default') {
  // Get the query as a string:
  $query_string = (string) $query;
  $statement_type = strtolower(substr($query_string, 0, strpos($query_string, ' ')));

  // Prefix tables:
  $connection = Database::getConnection($target);
  $query_string = trim($connection->prefixTables($query_string));

  // Convert query object to an array so we can access readonly properties:
  $query_array = object_to_array($query, TRUE);

  // Replace field placeholders:
  if ($statement_type == 'insert') {
    $values = $query_array['insertValues'][0];
    foreach ($values as $index => $value) {
      $replacement = is_numeric($value) ? $value : $connection->quote($value);
      $query_string = str_replace(":db_insert_placeholder_$index", $replacement, $query_string);
    }
  }
  elseif ($statement_type == 'update') {
    $fields = $query_array['fields'];
    $n = 0;
    foreach ($fields as $value) {
      $replacement = is_numeric($value) ? $value : $connection->quote($value);
      $query_string = str_replace(":db_update_placeholder_$n", $replacement, $query_string);
      $n++;
    }
  }

  // Replace condition placeholders:
  if (isset($query_array['where']['arguments']) && is_array($query_array['where']['arguments'])) {
    foreach ($query_array['where']['arguments'] as $placeholder => $value) {
      $replacement = is_numeric($value) ? $value : $connection->quote($value);
      $query_string = str_replace($placeholder, $replacement, $query_string);
    }
  }
  if (isset($query_array['condition']['arguments']) && is_array($query_array['condition']['arguments'])) {
    foreach ($query_array['condition']['arguments'] as $placeholder => $value) {
      $replacement = is_numeric($value) ? $value : $connection->quote($value);
      $query_string = str_replace($placeholder, $replacement, $query_string);
    }
  }

  return $query_string;
}
