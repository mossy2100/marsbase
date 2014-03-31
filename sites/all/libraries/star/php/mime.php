<?php
/**
 * Functions related to MIME types.
 * Path of the Star Library from Star Multimedia.
 * 
 * @author Shaun Moss
 */

/**
 * Gets a map of extensions to MIME types.
 * This is an alternative to using the fileinfo extension, which is difficult to
 * install.
 * 
 * @param string $path_to_mime_types
 *   The path to the mime.types file.
 * @return array
 */
function get_mime_types($path_to_mime_types = NULL) {
  global $mime_types;
  // if we've already loaded these, just return the map:
  if ($mime_types) {
    return $mime_types;
  }
  // default path to mime.types is the same folder as this file:
  if (!$path_to_mime_types) {
    $pi = pathinfo(__FILE__);
    $path_to_mime_types = $pi['dirname'] . '/mime.types';
  }
  // read mime types into array:
  $fp = fopen($path_to_mime_types, 'r');
  $mime_types = array();
  while (($line = fgets($fp)) !== false) {
    $line = trim(preg_replace('/#.*/', '', $line));
    if (!$line) {
      continue;
    }
    $parts = preg_split('/\s+/', $line);
    if (count($parts) == 1) {
      continue;
    }
    $type = array_shift($parts);
    foreach($parts as $extension) {
      $mime_types[$extension] = $type;
    }
  }
  fclose($fp);
  ksort($mime_types);
  return $mime_types;
}

/**
 * Get the MIME type for a given file.
 * 
 * @param string $path
 * @return string
 */
function get_mime_type($path) {
  $mime_types = get_mime_types();
  $pi = pathinfo($path);
  $ext = strtolower($pi['extension']);
  return $mime_types[$ext];
}

/**
 * Given an image filename, return the mime type.
 * If not a web image, return FALSE.
 * @param string $path
 * @return string
 */
function image_mime_type($path) {
  $pi = pathinfo($path);
  $ext = strtolower($pi['extension']);
  switch ($ext) {
    case 'jpeg':
    case 'jpg':
      return 'image/jpeg';
      
    case 'png':
      return 'image/png';
      
    case 'gif':
      return 'image/gif';
      
    default:
      return FALSE;
  }
}
